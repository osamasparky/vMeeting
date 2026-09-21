// Kiwi schema + message decoder (enough for Figma .fig canvas payloads).
(function () {
  function Reader(data) {
    let p = 0;
    const api = {
      get pos() { return p; },
      set pos(v) { p = v; },
      get eof() { return p >= data.length; },
      byte() { return data[p++]; },
      bool() { return !!data[p++]; },
      varuint() {
        let v = 0, shift = 0, b;
        do { b = data[p++]; v += (b & 127) * Math.pow(2, shift); shift += 7; } while (b & 128);
        return v;
      },
      varint() { const v = api.varuint(); return (v % 2) ? -((v + 1) / 2) : v / 2; },
      varuint64() { return api.varuint(); },
      varint64() { return api.varint(); },
      float() {
        const first = data[p];
        if (first === 0) { p++; return 0; }
        let bits = (data[p] | (data[p+1] << 8) | (data[p+2] << 16) | (data[p+3] << 24)) >>> 0;
        p += 4;
        bits = ((bits << 23) | (bits >>> 9)) >>> 0;
        const buf = new DataView(new ArrayBuffer(4));
        buf.setUint32(0, bits, true);
        return buf.getFloat32(0, true);
      },
      string() {
        const start = p;
        while (data[p] !== 0) p++;
        const s = new TextDecoder('utf-8').decode(data.subarray(start, p));
        p++;
        return s;
      }
    };
    return api;
  }

  const KINDS = ['ENUM', 'STRUCT', 'MESSAGE'];
  const BUILTIN = { '-1': 'bool', '-2': 'byte', '-3': 'int', '-4': 'uint', '-5': 'float', '-6': 'string', '-7': 'int64', '-8': 'uint64' };

  function parseSchema(bytes) {
    const r = Reader(bytes);
    const n = r.varuint();
    const defs = [];
    for (let i = 0; i < n; i++) {
      const name = r.string();
      const kind = KINDS[r.byte()];
      const fc = r.varuint();
      const fields = [];
      for (let j = 0; j < fc; j++) {
        const fname = r.string();
        const type = r.varint();
        const isArray = !!r.byte();
        const value = r.varuint();
        fields.push({ name: fname, type, isArray, value });
      }
      defs.push({ name, kind, fields });
    }
    return defs;
  }

  function makeDecoder(defs) {
    const byName = {};
    defs.forEach((d, i) => { d.index = i; byName[d.name] = d; });

    function readValue(r, type) {
      if (type < 0) {
        switch (type) {
          case -1: return r.bool();
          case -2: return r.byte();
          case -3: return r.varint();
          case -4: return r.varuint();
          case -5: return r.float();
          case -6: return r.string();
          case -7: return r.varint64();
          case -8: return r.varuint64();
        }
      }
      return readDef(r, defs[type]);
    }

    function readDef(r, def) {
      if (def.kind === 'ENUM') {
        const v = r.varuint();
        const f = def.fields.find(f => f.value === v);
        return f ? f.name : v;
      }
      const obj = {};
      if (def.kind === 'STRUCT') {
        for (const f of def.fields) obj[f.name] = readField(r, f);
        return obj;
      }
      while (true) {
        const id = r.varuint();
        if (id === 0) return obj;
        const f = def.fields.find(f => f.value === id);
        if (!f) throw new Error('unknown field id ' + id + ' in ' + def.name);
        obj[f.name] = readField(r, f);
      }
    }

    function readField(r, f) {
      if (f.isArray) {
        const n = r.varuint();
        if (f.type === -2) { const a = new Uint8Array(n); for (let i = 0; i < n; i++) a[i] = r.byte(); return a; }
        const arr = new Array(n);
        for (let i = 0; i < n; i++) arr[i] = readValue(r, f.type);
        return arr;
      }
      return readValue(r, f.type);
    }

    return {
      defs, byName,
      decodeRoot(bytes, rootName) {
        const r = Reader(bytes);
        return readDef(r, byName[rootName]);
      }
    };
  }

  globalThis.kiwi = { parseSchema, makeDecoder };
})();
