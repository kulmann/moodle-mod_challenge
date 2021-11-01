<template lang="pug">
.uk-card.uk-card-default
  template(v-if="loading")
    .uk-card-body
      loading-alert.uk-text-center(:message="strings.admin_results_loading")
  template(v-else)
    .uk-card-header
      h3 {{ strings.admin_results_title | stringParams(round.number) }}
    .uk-card-body
      info-alert(
        v-if="isRoundPending",
        :message="strings.admin_results_pending"
      )
      info-alert(
        v-else-if="matches.length === 0",
        :message="strings.admin_results_no_matches"
      )
      template(v-else)
        template(v-for="matchNumber in matchNumbers")
          .uk-margin-bottom(:key="`match-group-${matchNumber}`")
            h4(v-if="round.matches > 1") {{ strings.admin_results_match_group | stringParams(getMatchTitleStringParams(matchNumber)) }}
            div(
              v-for="match in matchesByNumbers[matchNumber]",
              :key="`match-${matchNumber}-${match.id}`"
            )
              match-pair(
                :round="round",
                :match="match",
                :questions="questions",
                :attempts="attempts"
              )
    .uk-card-footer.uk-text-right
      button.btn.btn-default.uk-margin-small-left(@click="goToRoundList()")
        v-icon.uk-margin-small-right(name="list-ol")
        span {{ strings.admin_results_btn_rounds }}
</template>

<script>
import LangMixins from "../../../mixins/lang-mixins";
import TimeMixins from "../../../mixins/time-mixins";
import { mapState, mapActions } from "vuex";
import LoadingAlert from "../../helper/loading-alert";
import constants from "../../../constants";
import InfoAlert from "../../helper/info-alert";
import MatchPair from "./results/match-pair";

export default {
  components: { MatchPair, InfoAlert, LoadingAlert },
  mixins: [LangMixins, TimeMixins],
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
    isRoundPending() {
      return this.round.state === "pending";
    },
    /**
     * Returns all matches, but grouped by their match numbers
     */
    matchesByNumbers() {
      return this.matches.reduce((groups, match) => {
        if (!groups[match.number]) {
          groups[match.number] = [];
        }
        groups[match.number].push(match);
        return groups;
      }, {});
    },
    matchNumbers() {
      return Object.keys(this.matchesByNumbers).sort();
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
    getMatchTitleStringParams(matchNumber) {
      return {
        number: matchNumber,
        date: this.formDate(this.matchesByNumbers[matchNumber][0].timecreated),
        time: this.formTime(this.matchesByNumbers[matchNumber][0].timecreated),
      };
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
