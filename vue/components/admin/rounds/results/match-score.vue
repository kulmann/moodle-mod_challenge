<template lang="pug">
div
  table.uk-table.uk-table-small.score-table
    tbody
      tr(v-for="row in rowData", :key="`${match.id}-score-row-${row.index}`")
        td.uk-table-shrink.uk-text-right(
          :class="{ 'match-winner': leftWinner, 'match-loser': leftLoser }"
        )
          span(v-if="row.left.correct")
            span.uk-text-bold.uk-margin-small-right {{ row.left.score }}
            v-icon.uk-text-success(name="check-circle")
          span(v-else-if="row.left.finished")
            v-icon.uk-text-danger(name="times-circle")
          span(v-else)
            v-icon.uk-text-meta(name="minus-circle")
        td.uk-table-shrink {{ strings.admin_results_match_score_table_question | stringParams(row.index + 1) }}
        td.uk-table-shrink.uk-text-left(
          :class="{ 'match-winner': rightWinner, 'match-loser': rightLoser }"
        )
          span(v-if="row.right.correct")
            v-icon.uk-text-success(name="check-circle")
            span.uk-text-bold.uk-margin-small-left {{ row.right.score }}
          span(v-else-if="row.right.finished")
            v-icon.uk-text-danger(name="times-circle")
          span(v-else)
            v-icon.uk-text-meta(name="minus-circle")
    tfoot
      tr
        td.uk-text-bold.uk-table-shrink.uk-text-right(
          :class="{ 'match-winner': leftWinner, 'match-loser': leftLoser }"
        )
          span.uk-text-large(v-if="leftScore > 0") {{ leftScore }}
          span(v-else) -
        td.uk-text-bold.uk-table-shrink
          span(v-if="match.completed") {{ strings.admin_results_match_score_state_finished }}
          span(v-else) {{ strings.admin_results_match_score_state_ongoing }}
        td.uk-text-bold.uk-table-shrink.uk-text-left(
          :class="{ 'match-winner': rightWinner, 'match-loser': rightLoser }"
        )
          span.uk-text-large(v-if="rightScore > 0") {{ rightScore }}
          span(v-else) -
</template>

<script>
import { mapState } from "vuex";
import LangMixins from "../../../../mixins/lang-mixins";
import first from "lodash/first";

export default {
  mixins: [LangMixins],
  props: {
    round: {
      type: Object,
      required: true,
    },
    match: {
      type: Object,
      required: true,
    },
    userLeft: {
      type: Object,
      required: true,
    },
    userRight: {
      type: Object,
      required: true,
    },
    questions: {
      type: Array,
      default: () => [],
    },
    attempts: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    ...mapState(["strings"]),
    rowData() {
      const result = [];
      for (let i = 0; i < this.round.questions; i++) {
        const row = {
          index: i,
          left: {},
          right: {},
        };
        const question = this.questions.filter(
          (q) => q.matchid === this.match.id && q.number === i + 1
        );
        if (question.length === 1) {
          const attemptLeft = this.getAttempt(first(question), this.userLeft);
          if (attemptLeft) {
            row.left = {
              correct: attemptLeft.correct,
              finished: attemptLeft.finished,
              score: attemptLeft.score,
            };
          }
          const attemptRight = this.getAttempt(first(question), this.userRight);
          if (attemptRight) {
            row.right = {
              correct: attemptRight.correct,
              finished: attemptRight.finished,
              score: attemptRight.score,
            };
          }
        }
        result.push(row);
      }
      return result;
    },
    leftScore() {
      return this.getScoreSum(this.userLeft);
    },
    rightScore() {
      return this.getScoreSum(this.userRight);
    },
    leftWinner() {
      return this.match.winner_mdl_user === this.userLeft.id;
    },
    rightWinner() {
      return this.match.winner_mdl_user === this.userRight.id;
    },
    leftLoser() {
      return this.match.completed && !this.leftWinner;
    },
    rightLoser() {
      return this.match.completed && !this.rightWinner;
    },
  },
  methods: {
    getAttempt(question, user) {
      const attempts = this.attempts.filter(
        (a) => a.question === question.id && a.mdl_user === user.id
      );
      if (attempts.length === 1) {
        return first(attempts);
      } else {
        return null;
      }
    },
    getScoreSum(user) {
      const questionIds = this.questions
        .filter((q) => q.matchid === this.match.id)
        .map((q) => q.id);
      const attemptScores = this.attempts
        .filter(
          (a) => questionIds.includes(a.question) && a.mdl_user === user.id
        )
        .map((a) => a.score);
      return attemptScores.reduce(function (a, b) {
        return a + b;
      }, 0);
    },
  },
};
</script>

<style lang="scss" scoped>
table.score-table {
  td {
    vertical-align: middle;
  }
}

.match-winner {
  background-color: rgba(0, 255, 0, 0.1);
}

.match-loser {
  background-color: rgba(255, 0, 0, 0.1);
}
</style>
