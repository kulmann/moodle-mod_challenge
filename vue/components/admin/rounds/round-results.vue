<template lang="pug">
  .uk-card.uk-card-default
    template(v-if="loading")
      .uk-card-body
        loading-alert(:message="strings.admin_results_loading").uk-text-center
    template(v-else)
      .uk-card-header
        h3 {{ strings.admin_results_title | stringParams(round.number) }}
      .uk-card-body
        info-alert(v-if="isRoundPending", :message="strings.admin_results_pending")
        template(v-else)
          div(v-for="match in matches", :key="`match-${match.id}`")
            vk-grid(matched).uk-flex-middle.uk-margin-bottom
              match-user.uk-width-1-5.uk-text-center(:user="getMatchUser1(match)")
              match-score.uk-width-3-5.uk-text-center(
                :round="round",
                :match="match",
                :user-left="getMatchUser1(match)",
                :user-right="getMatchUser2(match)",
                :questions="questions",
                :attempts="attempts"
              )
              match-user.uk-width-1-5.uk-text-center(:user="getMatchUser2(match)")
      .uk-card-footer.uk-text-right
        button.btn.btn-default(@click="goToRoundList()").uk-margin-small-left
          v-icon(name="list-ol").uk-margin-small-right
          span {{ strings.admin_results_btn_rounds }}

</template>

<script>
import LangMixins from "../../../mixins/lang-mixins";
import { mapState, mapActions, mapGetters } from "vuex";
import LoadingAlert from "../../helper/loading-alert";
import constants from "../../../constants";
import UserAvatar from "../../helper/user-avatar";
import MatchUser from "./results/match-user";
import MatchScore from "./results/match-score";
import InfoAlert from "../../helper/info-alert";

export default {
  components: { InfoAlert, MatchScore, MatchUser, UserAvatar, LoadingAlert },
  mixins: [LangMixins],
  props: {
    round: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      matches: [],
      questions: [],
      attempts: [],
    };
  },
  computed: {
    ...mapState(["strings"]),
    ...mapGetters(["getMdlUser"]),
    isRoundPending() {
      return this.round.state === "pending";
    },
  },
  methods: {
    ...mapActions({
      fetchRoundMatches: "admin/fetchRoundMatches",
      fetchRoundQuestions: "admin/fetchRoundQuestions",
      fetchRoundAttempts: "admin/fetchRoundAttempts",
    }),
    async loadData() {
      this.loading = true;
      await Promise.all([
        this.loadDataMatches(),
        this.loadDataQuestions(),
        this.loadDataAttempts(),
      ]);
      this.loading = false;
    },
    async loadDataMatches() {
      this.matches = await this.fetchRoundMatches({ roundid: this.round.id });
    },
    async loadDataQuestions() {
      this.questions = await this.fetchRoundQuestions({
        roundid: this.round.id,
      });
    },
    async loadDataAttempts() {
      this.attempts = await this.fetchRoundAttempts({ roundid: this.round.id });
    },
    goToRoundList() {
      this.$router.push({ name: constants.ROUTE_ADMIN_ROUNDS });
    },
    getMatchUser1(match) {
      return this.getMdlUser(match.mdl_user_1);
    },
    getMatchUser2(match) {
      return this.getMdlUser(match.mdl_user_2);
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style lang="scss" scoped>
.match-versus {
  font-size: 3em;
  font-weight: bold;
  font-style: italic;
  text-transform: uppercase;
}
</style>
