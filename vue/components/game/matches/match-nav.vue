<template lang="pug">
div
  .uk-flex.uk-flex-middle.uk-flex-center.uk-text-center.uk-margin-small-bottom
    button.btn.btn-default(:disabled="isFirstRound", @click="goToPrevRound")
      v-icon(name="chevron-left")
    div
      b.uk-margin-small-left.uk-margin-small-right {{ strings.game_match_step | stringParams({ step: roundIndex + 1, total: roundIds.length }) }}
      template(v-if="round")
        br
        i(v-if="isRoundOpen") {{ strings.game_match_lbl_round_open }}
        b(v-else) {{ strings.game_match_lbl_round_ended }}
    button.btn.btn-default(:disabled="isLastRound", @click="goToNextRound")
      v-icon(name="chevron-right")
  .uk-flex.uk-flex-middle.uk-flex-center.uk-text-center.uk-margin-small-bottom(
    v-if="round"
  )
    span(
      v-html="stringParams(strings.game_round_dates, { start: roundStartDate, end: roundEndDate })"
    )
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
    matchGroups: {
      type: Object,
      required: true,
    },
  },
  computed: {
    ...mapState(["strings"]),
    roundIds() {
      return Object.values(this.matchGroups).map((group) => group[0].round);
    },
    roundIndex() {
      return findIndex(this.roundIds, (id) => id === this.round.id);
    },
    isFirstRound() {
      return this.roundIndex === 0;
    },
    isLastRound() {
      return this.roundIndex === this.roundIds.length - 1;
    },
    roundStartDate() {
      return this.formDateTime(this.round.timestart);
    },
    roundEndDate() {
      return this.formDateTime(this.round.timeend);
    },
    isRoundOpen() {
      return this.round.started && !this.round.ended;
    },
  },
  methods: {
    goToPrevRound() {
      if (!this.isFirstRound) {
        const prevRoundId = this.roundIds[this.roundIndex - 1];
        this.goToRound(prevRoundId);
      }
    },
    goToNextRound() {
      if (!this.isLastRound) {
        const nextRoundId = this.roundIds[this.roundIndex + 1];
        this.goToRound(nextRoundId);
      }
    },
    goToRound(roundId) {
      const matchId = this.matchGroups[roundId][0].id;
      this.$router.push({
        name: "player-match-show",
        params: { forcedMatchId: matchId },
      });
    },
  },
};
</script>
