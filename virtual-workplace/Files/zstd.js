// Minimal zstd (RFC 8878) decompressor, enough for Figma .fig canvas payloads.
(function () {
  function RevReader(data, start, size) {
    const last = start + size - 1;
    const lastByte = data[last];
    if (!lastByte) throw new Error('zstd: last byte of bitstream is 0');
    const h = 31 - Math.clz32(lastByte);
    this.pos = size * 8 - (8 - h);
    this.read = function (n) {
      if (n <= 0) return 0;
      this.pos -= n;
      let p = this.pos, len = n;
      if (p < 0) { len = n + p; p = 0; }
      let v = 0;
      if (len > 0) {
        const byteStart = p >> 3, bitOff = p & 7;
        const nbytes = (bitOff + len + 7) >> 3;
        let acc = 0;
        for (let i = nbytes - 1; i >= 0; i--) acc = acc * 256 + data[start + byteStart + i];
        v = Math.floor(acc / Math.pow(2, bitOff)) % Math.pow(2, len);
      }
      return v * Math.pow(2, n - len);
    };
  }

  function FwdReader(data, start) {
    let bit = 0;
    return {
      read(n) {
        let v = 0;
        for (let i = 0; i < n; i++) {
          v |= ((data[start + (bit >> 3)] >> (bit & 7)) & 1) << i;
          bit++;
        }
        return v;
      },
      get bytesUsed() { return (bit + 7) >> 3; }
    };
  }

  function readNCount(data, start, maxSymbol, maxLog) {
    const br = FwdReader(data, start);
    const accuracyLog = br.read(4) + 5;
    if (accuracyLog > maxLog) throw new Error('zstd: accuracy log too large');
    let remaining = (1 << accuracyLog) + 1;
    let threshold = 1 << accuracyLog;
    let nbBits = accuracyLog + 1;
    const counts = [];
    let charnum = 0, previous0 = false;
    while (remaining > 1 && charnum <= maxSymbol) {
      if (previous0) {
        while (true) {
          const rep = br.read(2);
          for (let i = 0; i < rep; i++) counts[charnum++] = 0;
          if (rep < 3) break;
        }
        previous0 = false;
      }
      const max = (2 * threshold - 1) - remaining;
      let val = br.read(nbBits - 1);
      let count;
      if (val < max) count = val;
      else {
        val |= br.read(1) << (nbBits - 1);
        if (val >= threshold) val -= max;
        count = val;
      }
      count--;
      remaining -= count < 0 ? -count : count;
      counts[charnum++] = count;
      previous0 = count === 0;
      while (remaining < threshold) { nbBits--; threshold >>= 1; }
    }
    for (let i = charnum; i <= maxSymbol; i++) counts[i] = 0;
    return { counts, accuracyLog, maxSymbol: charnum - 1, bytesUsed: br.bytesUsed };
  }

  function buildFSE(counts, maxSymbol, accuracyLog) {
    const size = 1 << accuracyLog, mask = size - 1;
    const stateSym = new Int32Array(size);
    const symbolNext = new Int32Array(maxSymbol + 2);
    let highThreshold = size - 1;
    for (let s = 0; s <= maxSymbol; s++) {
      if (counts[s] === -1) { stateSym[highThreshold--] = s; symbolNext[s] = 1; }
      else symbolNext[s] = counts[s];
    }
    const step = (size >> 1) + (size >> 3) + 3;
    let position = 0;
    for (let s = 0; s <= maxSymbol; s++) {
      for (let i = 0; i < counts[s]; i++) {
        stateSym[position] = s;
        position = (position + step) & mask;
        while (position > highThreshold) position = (position + step) & mask;
      }
    }
    const sym = new Int32Array(size), nb = new Int32Array(size), ns = new Int32Array(size);
    for (let u = 0; u < size; u++) {
      const s = stateSym[u];
      const next = symbolNext[s]++;
      const bits = accuracyLog - (31 - Math.clz32(next));
      sym[u] = s; nb[u] = bits; ns[u] = (next << bits) - size;
    }
    return { sym, nb, ns, accuracyLog };
  }

  function fseDecompress(data, start, size, table) {
    const br = new RevReader(data, start, size);
    const log = table.accuracyLog;
    let s1 = br.read(log), s2 = br.read(log);
    const out = [];
    while (true) {
      out.push(table.sym[s1]); s1 = table.ns[s1] + br.read(table.nb[s1]);
      if (br.pos < 0) { out.push(table.sym[s2]); break; }
      out.push(table.sym[s2]); s2 = table.ns[s2] + br.read(table.nb[s2]);
      if (br.pos < 0) { out.push(table.sym[s1]); break; }
    }
    return out;
  }

  function buildHuff(weights) {
    let sum = 0;
    for (const w of weights) if (w > 0) sum += 1 << (w - 1);
    if (sum === 0) throw new Error('zstd: empty huffman');
    const tableLog = (31 - Math.clz32(sum)) + 1;
    const rest = (1 << tableLog) - sum;
    const lastWeight = (31 - Math.clz32(rest)) + 1;
    const all = weights.concat([lastWeight]);
    const rankCount = new Int32Array(tableLog + 2);
    for (const w of all) if (w > 0) rankCount[w]++;
    const rankVal = new Int32Array(tableLog + 2);
    let nextRankStart = 0;
    for (let n = 1; n <= tableLog; n++) {
      rankVal[n] = nextRankStart;
      nextRankStart += rankCount[n] << (n - 1);
    }
    const size = 1 << tableLog;
    const symbol = new Uint8Array(size), nbBits = new Uint8Array(size);
    for (let n = 0; n < all.length; n++) {
      const w = all[n];
      if (!w) continue;
      const length = (1 << w) >> 1;
      const startI = rankVal[w];
      for (let u = startI; u < startI + length; u++) { symbol[u] = n; nbBits[u] = tableLog + 1 - w; }
      rankVal[w] += length;
    }
    return { symbol, nbBits, tableLog };
  }

  function readHuffTable(data, p) {
    const headerByte = data[p]; p++;
    let weights;
    if (headerByte < 128) {
      const nc = readNCount(data, p, 255, 6);
      const table = buildFSE(nc.counts, nc.maxSymbol, nc.accuracyLog);
      weights = fseDecompress(data, p + nc.bytesUsed, headerByte - nc.bytesUsed, table);
      p += headerByte;
    } else {
      const n = headerByte - 127;
      weights = [];
      for (let i = 0; i < n; i++) {
        const b = data[p + (i >> 1)];
        weights.push(i % 2 === 0 ? (b >> 4) : (b & 15));
      }
      p += (n + 1) >> 1;
    }
    return { huff: buildHuff(weights), next: p };
  }

  function huffDecodeStream(data, start, size, huff, count, out, outPos) {
    const br = new RevReader(data, start, size);
    const log = huff.tableLog;
    for (let i = 0; i < count; i++) {
      const idx = br.read(log);
      out[outPos++] = huff.symbol[idx];
      br.pos += log - huff.nbBits[idx];
    }
    return outPos;
  }

  const LL_BASE = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,18,20,22,24,28,32,40,48,64,128,256,512,1024,2048,4096,8192,16384,32768,65536];
  const LL_BITS = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,2,2,3,3,4,6,7,8,9,10,11,12,13,14,15,16];
  const ML_BASE = [3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,37,39,41,43,47,51,59,67,83,99,131,259,515,1027,2051,4099,8195,16387,32771,65539];
  const ML_BITS = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,2,2,3,3,4,4,5,7,8,9,10,11,12,13,14,15,16];
  const LL_DEF = [4,3,2,2,2,2,2,2,2,2,2,2,2,1,1,1,2,2,2,2,2,2,2,2,2,3,2,1,1,1,1,1,-1,-1,-1,-1];
  const ML_DEF = [1,4,3,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,-1,-1,-1,-1,-1,-1,-1];
  const OF_DEF = [1,1,1,1,1,1,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,-1,-1,-1,-1,-1];

  function zstdDecompress(src) {
    let p = 0;
    const chunks = [];
    while (p < src.length) {
      const magic = src[p] + src[p+1]*256 + src[p+2]*65536 + src[p+3]*16777216;
      if (magic === 0xFD2FB528) { p = decodeFrame(src, p + 4, chunks); }
      else if (magic >= 0x184D2A50 && magic <= 0x184D2A5F) {
        const len = src[p+4] + src[p+5]*256 + src[p+6]*65536 + src[p+7]*16777216;
        p += 8 + len;
      } else break;
    }
    let total = 0; for (const c of chunks) total += c.length;
    const out = new Uint8Array(total);
    let o = 0; for (const c of chunks) { out.set(c, o); o += c.length; }
    return out;
  }

  function decodeFrame(src, p, chunks) {
    const fhd = src[p]; p++;
    const fcsFlag = fhd >> 6, singleSegment = (fhd >> 5) & 1, checksum = (fhd >> 2) & 1, dictFlag = fhd & 3;
    if (!singleSegment) p++; // window descriptor
    p += [0,1,2,4][dictFlag];
    let contentSize = 0;
    const fcsSize = fcsFlag === 0 ? (singleSegment ? 1 : 0) : (fcsFlag === 1 ? 2 : fcsFlag === 2 ? 4 : 8);
    for (let i = fcsSize - 1; i >= 0; i--) contentSize = contentSize * 256 + src[p + i];
    if (fcsSize === 2) contentSize += 256;
    p += fcsSize;

    let out = new Uint8Array(contentSize > 0 ? contentSize + 1024 : 1 << 20);
    let o = 0;
    const grow = (need) => {
      if (o + need <= out.length) return;
      let n = out.length;
      while (n < o + need) n *= 2;
      const t = new Uint8Array(n); t.set(out.subarray(0, o)); out = t;
    };

    let huff = null, llT = null, ofT = null, mlT = null;
    const rep = [1, 4, 8];
    const litBuf = new Uint8Array(1 << 18);

    while (true) {
      const bh = src[p] | (src[p+1]<<8) | (src[p+2]<<16); p += 3;
      const last = bh & 1, type = (bh >> 1) & 3, blockSize = bh >> 3;
      if (type === 0) { grow(blockSize); out.set(src.subarray(p, p + blockSize), o); o += blockSize; p += blockSize; }
      else if (type === 1) { grow(blockSize); out.fill(src[p], o, o + blockSize); o += blockSize; p += 1; }
      else {
        const blockEnd = p + blockSize;
        // --- literals section ---
        let lp = p;
        const lh = src[lp];
        const litType = lh & 3, sizeFormat = (lh >> 2) & 3;
        let regen = 0, comp = 0, streams = 1;
        if (litType <= 1) {
          if (sizeFormat === 0 || sizeFormat === 2) { regen = lh >> 3; lp += 1; }
          else if (sizeFormat === 1) { regen = (src[lp] >> 4) | (src[lp+1] << 4); lp += 2; }
          else { regen = (src[lp] >> 4) | (src[lp+1] << 4) | (src[lp+2] << 12); lp += 3; }
        } else {
          if (sizeFormat === 0 || sizeFormat === 1) {
            const h = src[lp] | (src[lp+1] << 8) | (src[lp+2] << 16);
            regen = (h >> 4) & 1023; comp = (h >> 14) & 1023; lp += 3;
            streams = sizeFormat === 0 ? 1 : 4;
          } else if (sizeFormat === 2) {
            const h = src[lp] + src[lp+1]*256 + src[lp+2]*65536 + src[lp+3]*16777216;
            regen = Math.floor(h / 16) % 16384; comp = Math.floor(h / 262144) % 16384; lp += 4; streams = 4;
          } else {
            const h = src[lp] + src[lp+1]*256 + src[lp+2]*65536 + src[lp+3]*16777216 + src[lp+4]*4294967296;
            regen = Math.floor(h / 16) % 262144; comp = Math.floor(h / 4194304) % 262144; lp += 5; streams = 4;
          }
        }
        let lits = litBuf, litLen = regen;
        if (regen > litBuf.length) lits = new Uint8Array(regen);
        if (litType === 0) { lits.set(src.subarray(lp, lp + regen)); lp += regen; }
        else if (litType === 1) { lits.fill(src[lp], 0, regen); lp += 1; }
        else {
          let dp = lp, compEnd = lp + comp;
          if (litType === 2) { const r = readHuffTable(src, dp); huff = r.huff; dp = r.next; }
          if (!huff) throw new Error('zstd: treeless without table');
          if (streams === 1) {
            huffDecodeStream(src, dp, compEnd - dp, huff, regen, lits, 0);
          } else {
            const s1 = src[dp] | (src[dp+1] << 8), s2 = src[dp+2] | (src[dp+3] << 8), s3 = src[dp+4] | (src[dp+5] << 8);
            dp += 6;
            const s4 = compEnd - dp - s1 - s2 - s3;
            const seg = Math.ceil(regen / 4);
            const sizes = [s1, s2, s3, s4], counts = [seg, seg, seg, regen - 3 * seg];
            let op = 0, sp = dp;
            for (let i = 0; i < 4; i++) {
              op = huffDecodeStream(src, sp, sizes[i], huff, counts[i], lits, op);
              sp += sizes[i];
            }
          }
          lp = compEnd;
        }
        // --- sequences section ---
        let nbSeq = src[lp];
        if (nbSeq === 0) { lp++; }
        else if (nbSeq < 128) { lp++; }
        else if (nbSeq < 255) { nbSeq = ((nbSeq - 128) << 8) + src[lp+1]; lp += 2; }
        else { nbSeq = (src[lp+1] | (src[lp+2] << 8)) + 0x7F00; lp += 3; }

        if (nbSeq === 0) {
          grow(litLen); out.set(lits.subarray(0, litLen), o); o += litLen;
        } else {
          const modes = src[lp]; lp++;
          const llMode = modes >> 6, ofMode = (modes >> 4) & 3, mlMode = (modes >> 2) & 3;
          const readTable = (mode, def, defLog, maxSym, maxLog, cur) => {
            if (mode === 0) return buildFSE(def, def.length - 1, defLog);
            if (mode === 1) { const s = src[lp]; lp++; const c = new Array(maxSym + 1).fill(0); c[s] = 1; return buildFSE(c, maxSym, 0); }
            if (mode === 2) {
              const nc = readNCount(src, lp, maxSym, maxLog);
              lp += nc.bytesUsed;
              return buildFSE(nc.counts, nc.maxSymbol, nc.accuracyLog);
            }
            if (!cur) throw new Error('zstd: repeat table missing');
            return cur;
          };
          llT = readTable(llMode, LL_DEF, 6, 35, 9, llT);
          ofT = readTable(ofMode, OF_DEF, 5, 31, 8, ofT);
          mlT = readTable(mlMode, ML_DEF, 6, 52, 9, mlT);

          const br = new RevReader(src, lp, blockEnd - lp);
          let llS = br.read(llT.accuracyLog), ofS = br.read(ofT.accuracyLog), mlS = br.read(mlT.accuracyLog);
          let litPos = 0;
          for (let i = 0; i < nbSeq; i++) {
            const llCode = llT.sym[llS], ofCode = ofT.sym[ofS], mlCode = mlT.sym[mlS];
            const offValue = Math.pow(2, ofCode) + br.read(ofCode);
            const mlVal = ML_BASE[mlCode] + br.read(ML_BITS[mlCode]);
            const llVal = LL_BASE[llCode] + br.read(LL_BITS[llCode]);
            let offset;
            if (offValue > 3) {
              offset = offValue - 3;
              rep[2] = rep[1]; rep[1] = rep[0]; rep[0] = offset;
            } else {
              let idx = offValue + (llVal === 0 ? 1 : 0);
              if (idx === 1) offset = rep[0];
              else if (idx === 2) { offset = rep[1]; rep[1] = rep[0]; rep[0] = offset; }
              else if (idx === 3) { offset = rep[2]; rep[2] = rep[1]; rep[1] = rep[0]; rep[0] = offset; }
              else { offset = rep[0] - 1; rep[2] = rep[1]; rep[1] = rep[0]; rep[0] = offset; }
            }
            grow(llVal + mlVal);
            for (let k = 0; k < llVal; k++) out[o++] = lits[litPos++];
            let from = o - offset;
            for (let k = 0; k < mlVal; k++) out[o++] = out[from++];
            if (i < nbSeq - 1) {
              llS = llT.ns[llS] + br.read(llT.nb[llS]);
              mlS = mlT.ns[mlS] + br.read(mlT.nb[mlS]);
              ofS = ofT.ns[ofS] + br.read(ofT.nb[ofS]);
            }
          }
          const restLits = litLen - litPos;
          grow(restLits);
          for (let k = 0; k < restLits; k++) out[o++] = lits[litPos++];
        }
        p = blockEnd;
      }
      if (last) break;
    }
    if (checksum) p += 4;
    chunks.push(out.subarray(0, o));
    return p;
  }

  globalThis.zstdDecompress = zstdDecompress;
})();
