<template lang="pug">
.uk-card.uk-card-default.uk-card-body
  loading-alert.uk-text-center(
    v-if="loading",
    :message="strings.game_match_loading"
  )
  template(v-else)
    match-nav(
      :own-user-id="ownUserId",
      :round="round",
      :match-groups="matchGroups"
    )
    failure-alert(
      v-if="match === null",
      :message="strings.game_match_show_error"
    )
    template(v-else)
      .uk-margin-large-bottom(
        v-for="match in matchGroups[round.id]",
        :key="`match-show-${round.id}-${match.id}`"
      )
        h4(v-if="matchGroups[round.id].length > 1") {{ strings.game_match_title | stringParams(getMatchTitleStringParams(match)) }}
        match-show(
          :round="round",
          :match="match",
          :questions="questions",
          :attempts="attempts",
          :own-user-id="ownUserId"
        )
</template>

<script>
import langMixins from "../../../mixins/lang-mixins";
import timeMixins from "../../../mixins/time-mixins";
import { mapActions, mapGetters, mapState } from "vuex";
import isNil from "lodash/isNil";
import last from "lodash/last";
import first from "lodash/first";
import FailureAlert from "../../helper/failure-alert";
import LoadingAlert from "../../helper/loading-alert";
import MatchShow from "./match-show";
import MatchNav from "./match-nav";

export default {
  mixins: [langMixins, timeMixins],
  props: {
    rounds: {
      type: Array,
      required: true,
    },
    matches: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      questions: [],
      attempts: [],
    };
  },
  computed: {
    ...mapState(["strings", "game"]),
    ...mapGetters(["getRoundById"]),
    ...mapGetters("player", ["getMatchById"]),
    matchId() {
      if (!isNil(this.$route.params.forcedMatchId)) {
        return parseInt(this.$route.params.forcedMatchId);
      }
      // serve first unfinished match
      const unfinishedMatches = this.matches.filter(
        (m) => !m.completed && !m.mdl_user_1_completed
      );
      if (unfinishedMatches.length > 0) {
        return first(unfinishedMatches).id;
      }
      // fall back to last match, if all are finished
      if (this.matches.length > 0) {
        return last(this.matches).id;
      }
      return undefined;
    },
    match() {
      if (this.matchId) {
        return this.getMatchById(this.matchId);
      }
      return undefined;
    },
    round() {
      if (!isNil(this.match)) {
        return this.getRoundById(this.match.round);
      }
      return last(this.rounds);
    },
    matchGroups() {
      const groups = this.matches.reduce((groups, match) => {
        if (!groups[match.round]) {
          groups[match.round] = [];
        }
        groups[match.round].push(match);
        return groups;
      }, {});
      for (let roundId of Object.keys(groups)) {
        groups[roundId] = groups[roundId].sort((m1, m2) => {
          return m1.number < m2.number ? 1 : -1;
        });
      }
      return groups;
    },
    ownUserId() {
      return this.game.mdl_user;
    },
  },
  methods: {
    ...mapActions({
      fetchMatches: "player/fetchMatches",
      fetchMatchQuestions: "player/fetchMatchQuestions",
      fetchMatchAttempts: "player/fetchMatchAttempts",
    }),
    async loadData() {
      await this.fetchMatches();
      await this.loadQuestions();
    },
    async loadQuestions() {
      if (this.matchId) {
        this.questions = await this.fetchMatchQuestions({
          matchid: this.matchId,
        });
        this.attempts = await this.fetchMatchAttempts({
          matchid: this.matchId,
        });
      } else {
        this.questions = [];
        this.attempts = [];
      }
    },
    getMatchTitleStringParams(match) {
      return {
        number: match.number,
        date: this.formDate(match.timecreated),
        time: this.formTime(match.timecreated),
      };
    },
  },
  async mounted() {
    await this.loadData();
    this.loading = false;
  },
  watch: {
    matchId() {
      this.loadData();
    },
  },
  components: {
    MatchNav,
    MatchShow,
    LoadingAlert,
    FailureAlert,
  },
};
</script>
