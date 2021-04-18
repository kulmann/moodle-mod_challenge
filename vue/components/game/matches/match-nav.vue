<template lang="pug">
  div
    .uk-flex.uk-flex-middle.uk-flex-center.uk-text-center.uk-margin-small-bottom
      button.btn.btn-default(:disabled="isFirstMatch", @click="goToPrevMatch")
        v-icon(name="chevron-left")
      div
        b.uk-margin-small-left.uk-margin-small-right {{ strings.game_match_step | stringParams({step: matchIndex + 1, total: matches.length}) }}
        template(v-if="match")
          br
          i(v-if="isMatchOpen") {{ strings.game_match_lbl_open }}
          b.uk-text-success(v-else-if="isMatchWon") {{ strings.game_match_lbl_won }}
          b.uk-text-danger(v-else) {{ strings.game_match_lbl_lost }}
      button.btn.btn-default(:disabled="isLastMatch", @click="goToNextMatch")
        v-icon(name="chevron-right")
    .uk-flex.uk-flex-middle.uk-flex-center.uk-text-center.uk-margin-small-bottom(v-if="round")
      span(v-html="stringParams(strings.game_round_dates, {start: roundStartDate, end: roundEndDate})")
</template>

<script>
import { mapState } from "vuex";
import LangMixins from "../../../mixins/lang-mixins";
import TimeMixins from "../../../mixins/time-mixins";
import { findIndex } from "lodash";

export default {
  mixins: [LangMixins, TimeMixins],
  props: {
    ownUserId: {
      type: Number,
      required: true,
    },
    round: {
      type: Object,
      required: false,
    },
    match: {
      type: Object,
      required: false,
    },
    matches: {
      type: Array,
      required: true,
    },
  },
  computed: {
    ...mapState(["strings"]),
    matchIndex() {
      return findIndex(this.matches, (m) => m.id === this.match.id);
    },
    isFirstMatch() {
      return this.matchIndex === 0;
    },
    isLastMatch() {
      return this.matchIndex === this.matches.length - 1;
    },
    roundStartDate() {
      return this.formDateTime(this.round.timestart);
    },
    roundEndDate() {
      return this.formDateTime(this.round.timeend);
    },
    isMatchOpen() {
      return this.match.open;
    },
    isMatchWon() {
      return this.match.winner_mdl_user === this.ownUserId;
    },
  },
  methods: {
    goToPrevMatch() {
      if (!this.isFirstMatch) {
        const match = this.matches[this.matchIndex - 1];
        this.goToMatch(match.id);
      }
    },
    goToNextMatch() {
      if (!this.isLastMatch) {
        const match = this.matches[this.matchIndex + 1];
        this.goToMatch(match.id);
      }
    },
    goToMatch(matchId) {
      this.$router.push({
        name: "player-match-show",
        params: { forcedMatchId: matchId },
      });
    },
  },
};
</script>
