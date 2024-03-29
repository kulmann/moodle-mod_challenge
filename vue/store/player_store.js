import sortBy from "lodash/sortBy";
import first from "lodash/first";
import { ajax } from "./index";

export default {
  namespaced: true,
  state: {
    initialized: false,
    matches: [],
  },
  mutations: {
    setPlayerInitialized(state, initialized) {
      state.initialized = initialized;
    },
    setMatches(state, matches) {
      state.matches = matches;
    },
  },
  getters: {
    getMatchById: (state) => (id) => {
      return first(state.matches.filter((m) => m.id === id));
    },
    getCurrentMatch: (state, getters, rootState, rootGetters) => {
      const currentRound = rootGetters.getCurrentRound;
      if (currentRound) {
        return first(state.matches.filter((m) => m.round === currentRound.id));
      }
      return null;
    },
  },
  actions: {
    /**
     * Initializes everything (current round, current match, etc).
     *
     * @param context
     */
    async initPlayer(context) {
      await context.dispatch("fetchMatches", {});
      context.commit("setPlayerInitialized", true);
    },
    /**
     * Fetches the matches of a player
     *
     * @param context
     * @param payload
     * @returns {Promise<void>}
     */
    async fetchMatches(context, payload) {
      const result = await ajax("mod_challenge_player_get_matches", payload);
      context.commit("setMatches", result);
    },
    /**
     * Fetches the questions of a match.
     *
     * @param context
     * @param payload
     * @returns {Promise<*>}
     */
    async fetchMatchQuestions(context, payload) {
      return await ajax("mod_challenge_player_get_match_questions", payload);
    },
    /**
     * Fetches the attempts of a match.
     *
     * @param context
     * @param payload
     * @returns {Promise<*>}
     */
    async fetchMatchAttempts(context, payload) {
      return await ajax("mod_challenge_player_get_match_attempts", payload);
    },
    /**
     * Requests a question by topic id and fetches the chosen question.
     *
     * @param context
     * @param payload
     * @returns {Promise<*>}
     */
    async requestQuestionForMatch(context, payload) {
      return await ajax("mod_challenge_player_request_match_question", payload);
    },
    /**
     * Submit an answer to the currently loaded question.
     *
     * @param context
     * @param payload
     *
     * @returns {Promise<void>}
     */
    async submitAnswer(context, payload) {
      return await ajax("mod_challenge_player_save_answer", payload);
    },
  },
};
