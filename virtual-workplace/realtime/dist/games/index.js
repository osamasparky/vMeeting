"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.TICK_HZ = exports.GameHub = exports.GAMES = void 0;
const snipers_js_1 = require("./snipers.js");
const racing_js_1 = require("./racing.js");
const karts_js_1 = require("./karts.js");
const pool_js_1 = require("./pool.js");
/** Every game a machine can run, by the key stored on the furniture item. */
exports.GAMES = { snipers: snipers_js_1.snipers, racing: racing_js_1.racing, karts: karts_js_1.karts, pool: pool_js_1.pool };
var game_hub_js_1 = require("./game-hub.js");
Object.defineProperty(exports, "GameHub", { enumerable: true, get: function () { return game_hub_js_1.GameHub; } });
Object.defineProperty(exports, "TICK_HZ", { enumerable: true, get: function () { return game_hub_js_1.TICK_HZ; } });
