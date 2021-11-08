<template lang="pug">
#challenge-game_screen
  .uk-clearfix
  loadingAlert.uk-text-center(v-if="!isInitialized", message="Loading Game")
  failureAlert.uk-text-center(
    v-else-if="isAdminUser",
    :message="strings.game_not_allowed"
  )
  infoAlert.uk-text-center(
    v-else-if="isNotStarted",
    :message="strings.game_not_started"
  )
  template(v-else-if="viewMode === VIEW_MODE_MATCH_SHOW")
    ul.uk-tab.uk-margin-small-bottom
      li(:class="{ 'uk-active': !showHighscores }")
        a(@click="showHighscores = false")
          v-icon.uk-margin-small-right(name="gamepad")
          span {{ strings.game_nav_play }}
      li(:class="{ 'uk-active': showHighscores }")
        a(@click="showHighscores = true")
          v-icon.uk-margin-small-right(name="chart-line")
          span {{ strings.game_nav_highscore }}
    highscores(v-if="showHighscores")
    matches(v-else, :rounds="rounds", :matches="matches")
  template(v-else)
    question(v-if="viewMode === VIEW_MODE_QUESTION_PLAY")
</template>

<script>
import langMixins from "../../mixins/lang-mixins";
import { mapGetters, mapState } from "vuex";
import VkGrid from "vuikit/src/library/grid/components/grid";
import loadingAlert from "../helper/loading-alert";
import infoAlert from "../helper/info-alert";
import matches from "./matches/matches";
import question from "./question/question";
import Highscores from "../shared/highscore/highscores";

export default {
  mixins: [langMixins],
  data() {
    return {
      VIEW_MODE_NONE: "none",
      VIEW_MODE_MATCH_SHOW: "player-match-show",
      VIEW_MODE_QUESTION_PLAY: "player-question-play",
      showHighscores: false,
    };
  },
  computed: {
    ...mapState(["strings", "rounds"]),
    ...mapGetters(["isAdminUser", "isInitialized", "getCurrentRound"]),
    ...mapState("player", ["matches"]),
    viewMode() {
      const viewModes = [
        this.VIEW_MODE_MATCH_SHOW,
        this.VIEW_MODE_QUESTION_PLAY,
      ];
      const viewMode = this.$route.name;
      if (viewModes.includes(viewMode)) {
        return viewMode;
      } else {
        return this.VIEW_MODE_NONE;
      }
    },
    isNotStarted() {
      return this.matches.length === 0;
    },
  },
  mounted() {
    if (this.viewMode === this.VIEW_MODE_NONE) {
      this.$router.push({ name: this.VIEW_MODE_MATCH_SHOW });
    }
    this.showHighscores = false;
  },
  components: {
    Highscores,
    question,
    matches,
    infoAlert,
    loadingAlert,
    VkGrid,
  },
};
</script>
