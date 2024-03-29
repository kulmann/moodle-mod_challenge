<template lang="pug">
  #challenge-question_singlechoice
    .uk-card.uk-card-default
      .uk-card-header(style="padding-top: 5px; padding-bottom: 5px;")
        vk-grid(matched)
          .uk-width-expand
            i.uk-h5 {{ strings.game_match_question_title | stringParams(question.number) }}
          .uk-width-auto
            questionTimer(:question="question", :attempt="attempt")
      .uk-card-body
        p._question(v-html="mdl_question.questiontext")
    vk-grid.uk-margin-top(matched)
      div(v-for="answer in mdl_answers", :key="answer.id", class="uk-width-1-1@s uk-width-1-2@m")
        .uk-alert.uk-alert-default._answer(uk-alert, @click="selectAnswer(answer)", :class="getAnswerClasses(answer)")
          vk-grid.uk-grid-small
            span.uk-width-auto.uk-text-bold {{ answer.label }}
            span.uk-width-expand.uk-text-center(v-html="answer.answer")
</template>

<script>
import find from "lodash/find";
import { mapActions, mapState } from "vuex";
import langMixins from "../../../mixins/lang-mixins";
import VkGrid from "vuikit/src/library/grid/components/grid";
import questionTimer from "./question-timer";

export default {
  mixins: [langMixins],
  props: {
    game: Object,
    question: Object,
    attempt: Object,
    mdl_question: Object,
    mdl_answers: Array,
  },
  data() {
    return {
      clickedAnswerId: null,
    };
  },
  computed: {
    ...mapState(["strings"]),
    correctAnswerId() {
      let correct = find(this.mdl_answers, function (mdl_answer) {
        return mdl_answer.fraction === 1;
      });
      return correct ? correct.id : null;
    },
    isFinished() {
      return this.clickedAnswerId !== null || this.attempt.finished;
    },
    isAnyAnswerGiven() {
      return this.clickedAnswerId !== null || this.attempt.finished;
    },
  },
  methods: {
    ...mapActions({
      submitAnswer: "player/submitAnswer",
    }),
    async selectAnswer(answer) {
      if (this.isFinished) {
        // don't allow another submission if already answered
        return;
      }
      this.clickedAnswerId = answer.id;
      await this.submitAnswer({
        questionid: this.question.id,
        mdlanswerid: this.clickedAnswerId,
      });
      this.$emit("reloadQuestion");
    },
    getAnswerClasses(answer) {
      let result = [];
      if (this.isFinished) {
        if (this.isCorrectAnswer(answer)) {
          result.push("uk-alert-success");
        }
        if (this.isClickedAnswer(answer)) {
          result.push("_thick-border");
          if (this.isWrongAnswer(answer)) {
            result.push("uk-alert-danger");
          }
        }
      } else {
        result.push("_pointer");
      }
      return result.join(" ");
    },
    isClickedAnswer(answer) {
      return this.isFinished && this.clickedAnswerId === answer.id;
    },
    isWrongAnswer(answer) {
      return this.correctAnswerId !== answer.id;
    },
    isCorrectAnswer(answer) {
      return this.correctAnswerId === answer.id;
    },
    initQuestion() {
      this.clickedAnswerId =
        this.attempt.mdl_answer > 0 ? this.attempt.mdl_answer : null;
    },
  },
  mounted() {
    if (this.attempt) {
      this.initQuestion();
    }
  },
  watch: {
    attempt() {
      this.initQuestion();
    },
  },
  components: {
    VkGrid,
    questionTimer,
  },
};
</script>

<style lang="scss" scoped="scoped">
._thick-border {
  border-width: 1px;
  border-style: solid;
}

._answer {
  border-radius: 5px;
  font-size: 1.2em;
}

._question {
  font-size: 1.4em;
}
</style>
