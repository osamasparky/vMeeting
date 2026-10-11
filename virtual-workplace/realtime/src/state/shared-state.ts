/**
 * Office state that everyone on a map shares and late joiners must see:
 * - which room doors are locked (the browser used to be the only place this lived);
 * - each room's whiteboard: strokes and sticky notes.
 *
 * Kept in memory per organization + map + room. A server restart clears it, which
 * matches the presence data held alongside it.
 */

export const MAX_STROKES = 3000;
export const MAX_NOTES = 200;
const MAX_POINTS = 2000;
const TOOLS = new Set(['pen', 'highlighter', 'eraser', 'line', 'rect', 'circle', 'arrow', 'text']);
const COLOR = /^#[0-9a-fA-F]{6}$/;

export interface Stroke {
  tool: string;
  color: string;
  startX?: number;
  startY?: number;
  endX?: number;
  endY?: number;
  rad?: number;
  text?: string;
  points?: [number, number][];
}

export interface Note {
  id: string;
  content: string;
  bg: string;
  border: string;
  text: string;
  author: string;
  createdAt: string;
}

const num = (v: unknown): number | undefined => (typeof v === 'number' && Number.isFinite(v) ? Math.round(v * 10) / 10 : undefined);

/** A stroke as drawn by the client, reduced to known fields; null when unusable. */
export function cleanStroke(raw: any): Stroke | null {
  if (!raw || typeof raw !== 'object' || !TOOLS.has(raw.tool)) return null;
  const stroke: Stroke = { tool: raw.tool, color: COLOR.test(raw.color) ? raw.color : '#0F172A' };
  for (const k of ['startX', 'startY', 'endX', 'endY', 'rad'] as const) {
    const v = num(raw[k]);
    if (v !== undefined) stroke[k] = v;
  }
  if (raw.tool === 'text') stroke.text = String(raw.text ?? '').slice(0, 300);
  if (Array.isArray(raw.points)) {
    stroke.points = raw.points
      .slice(0, MAX_POINTS)
      .map((p: unknown) => (Array.isArray(p) ? [num(p[0]), num(p[1])] : [undefined, undefined]))
      .filter((p: (number | undefined)[]) => p[0] !== undefined && p[1] !== undefined) as [number, number][];
  }
  return stroke;
}

export function cleanNote(raw: any, author: string): Note | null {
  const content = String(raw?.content ?? '').trim().slice(0, 500);
  if (!content) return null;
  const color = (v: unknown, fallback: string) => (typeof v === 'string' && COLOR.test(v) ? v : fallback);
  return {
    id: typeof raw?.id === 'string' && /^[\w-]{1,64}$/.test(raw.id) ? raw.id : `n_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
    content,
    bg: color(raw?.bg, '#FEF08A'),
    border: color(raw?.border, '#FACC15'),
    text: color(raw?.text, '#713F12'),
    author: author.slice(0, 120),
    createdAt: new Date().toISOString(),
  };
}

export class SharedState {
  private doors = new Map<string, boolean>();
  private boards = new Map<string, { strokes: Stroke[]; notes: Note[] }>();

  private key(orgId: string, mapId: string, roomId: string): string {
    return `${orgId}:${mapId}:${roomId}`;
  }

  // ── Doors ──
  setDoor(orgId: string, mapId: string, roomId: string, closed: boolean): void {
    const k = this.key(orgId, mapId, roomId);
    if (closed) this.doors.set(k, true);
    else this.doors.delete(k);
  }

  isDoorClosed(orgId: string, mapId: string, roomId: string): boolean {
    return this.doors.get(this.key(orgId, mapId, roomId)) === true;
  }

  closedDoors(orgId: string, mapId: string): string[] {
    const prefix = `${orgId}:${mapId}:`;
    return [...this.doors.keys()].filter((k) => k.startsWith(prefix)).map((k) => k.slice(prefix.length));
  }

  // ── Whiteboards ──
  private board(orgId: string, mapId: string, roomId: string) {
    const k = this.key(orgId, mapId, roomId);
    let b = this.boards.get(k);
    if (!b) {
      b = { strokes: [], notes: [] };
      this.boards.set(k, b);
    }
    return b;
  }

  addStroke(orgId: string, mapId: string, roomId: string, stroke: Stroke): void {
    const b = this.board(orgId, mapId, roomId);
    b.strokes.push(stroke);
    if (b.strokes.length > MAX_STROKES) b.strokes.splice(0, b.strokes.length - MAX_STROKES);
  }

  clearStrokes(orgId: string, mapId: string, roomId: string): void {
    this.board(orgId, mapId, roomId).strokes = [];
  }

  addNote(orgId: string, mapId: string, roomId: string, note: Note): void {
    const b = this.board(orgId, mapId, roomId);
    b.notes = [note, ...b.notes.filter((n) => n.id !== note.id)].slice(0, MAX_NOTES);
  }

  removeNote(orgId: string, mapId: string, roomId: string, id: string): boolean {
    const b = this.board(orgId, mapId, roomId);
    const before = b.notes.length;
    b.notes = b.notes.filter((n) => n.id !== id);
    return b.notes.length !== before;
  }

  history(orgId: string, mapId: string, roomId: string) {
    const b = this.boards.get(this.key(orgId, mapId, roomId));
    return { strokes: b ? b.strokes : [], notes: b ? b.notes : [] };
  }
}
