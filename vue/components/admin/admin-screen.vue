<template lang="pug">
#challenge-admin_screen
  .uk-clearfix
  loadingAlert.uk-text-center(
    v-if="!isInitialized",
    message="Loading Settings"
  )
  failureAlert.uk-text-center(
    v-else-if="isRegularUser",
    :message="strings.admin_not_allowed"
  )
  template(v-else)
    template(v-if="viewModeList")
      ul.uk-tab.uk-margin-small-bottom
        li(:class="{ 'uk-active': viewMode === VIEW_MODE_ROUNDS }")
          a(@click="setViewMode(VIEW_MODE_ROUNDS)")
            v-icon.uk-margin-small-right(name="cog")
            span {{ strings.admin_nav_rounds }}
        li(:class="{ 'uk-active': viewMode === VIEW_MODE_USERS }")
          a(@click="setViewMode(VIEW_MODE_USERS)")
            v-icon.uk-margin-small-right(name="users")
            span {{ strings.admin_nav_users }}
        li(:class="{ 'uk-active': viewMode === VIEW_MODE_HIGHSCORE }")
          a(@click="setViewMode(VIEW_MODE_HIGHSCORE)")
            v-icon.uk-margin-small-right(name="chart-line")
            span {{ strings.admin_nav_highscore }}
      rounds-list(
        v-if="viewMode === VIEW_MODE_ROUNDS",
        :rounds="rounds",
        :categories="categories",
        :mdlCategories="mdlCategories",
        key="rounds"
      )
      users-list(v-if="viewMode === VIEW_MODE_USERS", key="users")
      highscores(v-else-if="viewMode === VIEW_MODE_HIGHSCORE", key="highscore")
    template(v-else)
      round-edit(
        v-if="viewMode === VIEW_MODE_ROUND_EDIT",
        :round="roundForEditing",
        :categories="categories",
        :mdlCategories="mdlCategories",
        :rounds="rounds"
      )
      round-results(
        v-else-if="viewMode === VIEW_MODE_ROUND_RESULTS",
        :round="roundForEditing",
        :rounds="rounds"
      )
</template>

<script>
import langMixins from "../../mixins/lang-mixins";
import { mapState, mapGetters } from "vuex";
import endsWith from "lodash/endsWith";
import isNil from "lodash/isNil";
import find from "lodash/find";
import VkGrid from "vuikit/src/library/grid/components/grid";
import loadingAlert from "../helper/loading-alert";
import failureAlert from "../helper/failure-alert";
import RoundsList from "./rounds/rounds-list";
import RoundEdit from "./rounds/round-edit";
import UsersList from "./users/users-list";
import RoundResults from "./rounds/round-results";
import Highscores from "../shared/highscore/highscores";
import constants from "../../constants";

export default {
  mixins: [langMixins],
  data() {
    return {
      VIEW_MODE_NONE: constants.ROUTE_UNKNOWN,
      VIEW_MODE_ROUNDS: constants.ROUTE_ADMIN_ROUNDS,
      VIEW_MODE_ROUND_EDIT: constants.ROUTE_ADMIN_ROUND_EDIT,
      VIEW_MODE_ROUND_RESULTS: constants.ROUTE_ADMIN_ROUND_RESULTS,
      VIEW_MODE_USERS: constants.ROUTE_ADMIN_USERS,
      VIEW_MODE_HIGHSCORE: constants.ROUTE_ADMIN_HIGHSCORE,
    };
  },
  computed: {
    ...mapState(["strings", "rounds"]),
    ...mapState("admin", ["categories", "mdlCategories"]),
    ...mapGetters(["isRegularUser", "isInitialized"]),
    viewModeList() {
      return endsWith(this.$route.name, "-list");
    },
    viewMode() {
      const routes = [
        this.VIEW_MODE_ROUNDS,
        this.VIEW_MODE_USERS,
        this.VIEW_MODE_HIGHSCORE,
        this.VIEW_MODE_ROUND_EDIT,
        this.VIEW_MODE_ROUND_RESULTS,
      ];
      if (routes.includes(this.$route.name)) {
        return this.$route.name;
      } else {
        return this.VIEW_MODE_NONE;
      }
    },
    roundForEditing() {
      // try to find the given roundId in our loaded rounds.
      if (!isNil(this.$route.params.roundId)) {
        let roundId = parseInt(this.$route.params.roundId);
        return find(this.rounds, (round) => round.id === roundId);
      }
      // None found. Returning null will (correctly) result in creating a new round.
      return null;
    },
  },
  methods: {
    setViewMode(viewMode) {
      if (viewMode !== this.viewMode) {
        switch (viewMode) {
          case this.VIEW_MODE_ROUNDS:
            this.$router.push({ name: constants.ROUTE_ADMIN_ROUNDS });
            break;
          case this.VIEW_MODE_USERS:
            this.$router.push({ name: constants.ROUTE_ADMIN_USERS });
            break;
          case this.VIEW_MODE_HIGHSCORE:
            this.$router.push({ name: constants.ROUTE_ADMIN_HIGHSCORE });
            break;
          default: // do nothing
        }
      }
    },
  },
  mounted() {
    if (this.viewMode === this.VIEW_MODE_NONE) {
      this.setViewMode(this.VIEW_MODE_ROUNDS);
    }
  },
  components: {
    UsersList,
    Highscores,
    RoundResults,
    RoundsList,
    RoundEdit,
    VkGrid,
    failureAlert,
    loadingAlert,
  },
};
</script>
