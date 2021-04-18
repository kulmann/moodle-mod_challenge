<template lang="pug">
  div
    loadingAlert(v-if="loading", :message="strings.game_loading_question")
    template(v-else)
      div(
        :is="componentByType",
        :game="game",
        :question="question",
        :attempt="attempt",
        :mdl_question="mdl_question",
        :mdl_answers="mdl_answers",
        @reloadQuestion="reloadData"
      )
      question-actions.uk-margin-small-top(
        v-if="areActionsAllowed",
        :question="question",
        :attempt="attempt"
      )
</template>

<script>
import { mapState, mapActions } from "vuex";
import first from "lodash/first";
import langMixins from "../../../mixins/lang-mixins";
import questionActions from "./question-actions";
import questionError from "./question-error";
import questionSingleChoice from "./question-singlechoice";
import loadingAlert from "../../helper/loading-alert";

export default {
  mixins: [langMixins],
  data() {
    return {
      question: null,
      attempt: null,
      mdl_question: null,
      mdl_answers: null,
    };
  },
  computed: {
    ...mapState(["strings", "game"]),
    loading() {
      return (
        this.question === null ||
        this.attempt == null ||
        this.mdl_question === null ||
        this.mdl_answers === null
      );
    },
    componentByType() {
      switch (this.question.mdl_question_type) {
        case "qtype_multichoice_single_question":
          return "question-single-choice";
        default:
          return "question-error";
      }
    },
    areActionsAllowed() {
      return this.attempt.finished;
    },
    matchId() {
      return parseInt(this.$route.params.matchId);
    },
    questionNumber() {
      return parseInt(this.$route.params.questionNumber);
    },
    ownUserId() {
      return this.game.mdl_user;
    },
  },
  methods: {
    ...mapActions({
      requestQuestionForMatch: "player/requestQuestionForMatch",
      fetchMatchAttempts: "player/fetchMatchAttempts",
      fetchMdlQuestion: "fetchMdlQuestion",
      fetchMdlAnswers: "fetchMdlAnswers",
    }),
    async loadData() {
      this.question = null;
      this.attempt = null;
      this.mdl_question = null;
      this.mdl_answers = null;
      await this.reloadData();
    },
    async reloadData() {
      await this.loadQuestion();
      await Promise.all([
        this.loadAttempt(),
        this.loadMdlQuestion(),
        this.loadMdlAnswers(),
      ]);
    },
    async loadQuestion() {
      this.question = await this.requestQuestionForMatch({
        matchid: this.matchId,
        number: this.questionNumber,
      });
    },
    async loadAttempt() {
      const attempts = await this.fetchMatchAttempts({
        matchid: this.matchId,
      });
      this.attempt = first(
        attempts.filter((attempt) => {
          return (
            attempt.question === this.question.id &&
            attempt.mdl_user === this.ownUserId
          );
        })
      );
    },
    async loadMdlQuestion() {
      this.mdl_question = await this.fetchMdlQuestion({
        questionid: this.question.id,
      });
    },
    async loadMdlAnswers() {
      this.mdl_answers = await this.fetchMdlAnswers({
        questionid: this.question.id,
      });
    },
  },
  mounted() {
    this.loadData();
  },
  watch: {
    matchId() {
      this.loadData();
    },
    questionNumber() {
      this.loadData();
    },
  },
  components: {
    loadingAlert,
    questionActions,
    questionError,
    questionSingleChoice,
  },
};
</script>
