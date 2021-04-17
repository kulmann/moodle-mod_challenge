<template lang="pug">
  b.time(:class="timeLabelClass") {{ remainingSeconds }}
</template>

<script>
import { mapActions, mapState } from "vuex";

export default {
  props: {
    question: Object,
    attempt: Object,
  },
  data() {
    return {
      cancelledQuestionId: 0,
    };
  },
  computed: {
    ...mapState(["strings", "now"]),
    timeLabelClass() {
      if (this.remainingSeconds > this.question.time_max) {
        return "time-reading";
      }
      if (this.remainingSeconds > (this.question.time_max / 3) * 2) {
        return "time-plenty";
      }
      if (this.remainingSeconds > this.question.time_max / 3) {
        return "time-okish";
      }
      return "time-tight";
    },
    remainingSeconds() {
      if (this.attempt.timeremaining >= 0) {
        return this.attempt.timeremaining;
      } else {
        let start = this.attempt.timecreated * 1000;
        let end = start + this.question.time_max * 1000;
        return Math.max(0, Math.round((end - this.now) / 1000));
      }
    },
  },
  methods: {
    ...mapActions({
      submitAnswer: "player/submitAnswer",
    }),
    goToMatch() {
      const matchId = this.question.matchid;
      this.$router.push({
        name: "player-match-show",
        params: { forcedMatchId: matchId },
      });
    },
  },
  watch: {
    remainingSeconds(seconds) {
      if (
        this.question &&
        this.cancelledQuestionId !== this.question.id &&
        seconds <= 0
      ) {
        this.cancelledQuestionId = this.question.id;
        this.submitAnswer({
          questionid: this.question.id,
          mdlanswerid: 0,
        }).then(() => this.goToMatch());
      }
    },
  },
};
</script>

<style scoped>
.time {
  font-weight: bold;
}

.time-reading {
  color: black;
}

.time-plenty {
  color: green;
}

.time-okish {
  color: orange;
}

.time-tight {
  color: red;
}
</style>
