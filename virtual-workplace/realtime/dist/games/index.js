"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.TICK_HZ = exports.GameHub = exports.GAMES = void 0;
exports.buildGames = buildGames;
const snipers_js_1 = require("./snipers.js");
const racing_js_1 = require("./racing.js");
const karts_js_1 = require("./karts.js");
const pool_js_1 = require("./pool.js");
const quiz_js_1 = require("./quiz.js");
const run_js_1 = require("./run.js");
const chess_js_1 = require("./chess.js");
const tictactoe_js_1 = require("./tictactoe.js");
/** Every game a machine can run, by the key stored on the furniture item. */
function buildGames(secret) {
    return { snipers: snipers_js_1.snipers, racing: racing_js_1.racing, karts: karts_js_1.karts, pool: pool_js_1.pool, run: run_js_1.run, chess: chess_js_1.chess, tictactoe: tictactoe_js_1.tictactoe, quiz: (0, quiz_js_1.quizDef)(secret) };
}
/** Without the secret: everything but the quiz (which can then never start). */
exports.GAMES = buildGames(undefined);
var game_hub_js_1 = require("./game-hub.js");
Object.defineProperty(exports, "GameHub", { enumerable: true, get: function () { return game_hub_js_1.GameHub; } });
Object.defineProperty(exports, "TICK_HZ", { enumerable: true, get: function () { return game_hub_js_1.TICK_HZ; } });
