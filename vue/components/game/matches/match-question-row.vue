<template lang="pug">
  router-link(:to="{name: 'player-question-play', params: {matchId: match.id, questionNumber: question.number}}")
    vk-grid(matched, :class="{'_pointer': !isQuestionAnswered}").uk-flex-middle
      .uk-width-1-5
        .uk-flex.uk-flex-middle.uk-flex-center
          v-icon(:name="leftIcon", :scale="2", :style="leftStyle")
          .score.score-left {{ leftScore }}
      .uk-width-3-5
        .question-tile.uk-text-center.uk-text-middle
          template(v-if="isQuestionAnswered")
            span(v-if="mdlQuestion", v-html="mdlQuestion.questiontext")
            loadingIcon(v-else)
          span(v-else) {{ strings.game_match_lbl_question | stringParams(question.number) }}
      .uk-width-1-5
        .uk-flex.uk-flex-middle.uk-flex-center
          .score.score-right {{ rightScore }}
          v-icon(:name="rightIcon", :scale="2", :style="rightStyle")
</template>

<script>
import first from "lodash/first";
import isNil from "lodash/isNil";
import langMixins from "../../../mixins/lang-mixins";
import { mapState, mapActions } from "vuex";
import LoadingIcon from "../../helper/loading-icon";

export default {
  mixins: [langMixins],
  props: {
    question: {
      type: Object,
      required: true,
    },
    match: {
      type: Object,
      required: true,
    },
    mdlUserLeft: {
      type: Number,
      required: true,
    },
    mdlUserRight: {
      type: Number,
      required: true,
    },
    attempts: {
      type: Array,
      required: true,
    },
    ownUserId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      mdlQuestion: null,
    };
  },
  computed: {
    ...mapState(["strings", "game"]),
    leftAttempt() {
      return this.getAttemptByUser(this.mdlUserLeft);
    },
    leftScore() {
      if (this.leftAttempt) {
        return this.leftAttempt.score;
      }
      return "";
    },
    leftIcon() {
      return this.getIconByAttempt(this.leftAttempt);
    },
    leftStyle() {
      return this.getStyleByAttempt(this.leftAttempt);
    },
    rightAttempt() {
      if (this.match.open === false) {
        return this.getAttemptByUser(this.mdlUserRight);
      }
      return null;
    },
    rightScore() {
      if (this.rightAttempt) {
        return this.rightAttempt.score;
      }
      return "";
    },
    rightIcon() {
      return this.getIconByAttempt(this.rightAttempt);
    },
    rightStyle() {
      return this.getStyleByAttempt(this.rightAttempt);
    },
    ownAttempt() {
      return this.getAttemptByUser(this.ownUserId);
    },
    isQuestionAnswered() {
      return !isNil(this.ownAttempt) && this.ownAttempt.finished;
    },
  },
  methods: {
    ...mapActions(["fetchMdlQuestion"]),
    getAttemptByUser(mdlUserId) {
      return first(
        this.attempts.filter(
          (a) => a.mdl_user === mdlUserId && a.question === this.question.id
        )
      );
    },
    getIconByAttempt(attempt) {
      if (isNil(attempt) || !attempt.finished) {
        return "play-circle";
      } else {
        if (attempt.correct) {
          return "check-circle";
        } else {
          return "times-circle";
        }
      }
    },
    getStyleByAttempt(attempt) {
      let styles = [];
      if (isNil(attempt) || !attempt.finished) {
        styles.push("color: #cccccc;");
      } else {
        if (attempt.correct) {
          styles.push("color: #00bb00;");
        } else {
          styles.push("color: #9d261d;");
        }
      }
      return styles.join(" ");
    },
    async loadMdlQuestion() {
      if (!isNil(this.question.id)) {
        this.mdlQuestion = await this.fetchMdlQuestion({
          questionid: this.question.id,
        });
      }
    },
  },
  mounted() {
    this.loadMdlQuestion();
  },
  watch: {
    question() {
      this.loadMdlQuestion();
    },
  },
  components: { LoadingIcon },
};
</script>

<style lang="scss" scoped>
.question-tile {
  min-height: 40px;
  padding: 10px;
  background-color: #ccc;
  border: 1px solid #999;
  border-radius: 10px;
  color: #333;
}

.score {
  width: 42px;
  text-align: center;
  font-size: 1.5em;
  font-weight: bold;
  text-decoration: none;
}

.score-left {
  margin-left: 10px;
}

.score-right {
  margin-right: 10px;
}
</style>
