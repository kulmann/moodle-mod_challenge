<template lang="pug">
    div
        table.uk-table.uk-table-small.uk-table-striped(v-if="filteredScores.length > 0")
            thead
                tr
                    th.uk-table-shrink.uk-text-nowrap {{ strings.shared_highscore_rank }}
                    th.uk-table-auto {{ strings.shared_highscore_user }}
                    th.uk-table-shrink.uk-text-nowrap {{ strings.shared_highscore_score_total }}
                    th.uk-table-shrink.uk-text-nowrap {{ strings.shared_highscore_score_best }}
            tbody
                tr(v-for="score in mainScores", :key="score.mdl_user", :class="getScoreClasses(score)")
                    td {{ score.rank }}
                    td
                        a(:href="getProfileUrl(score.mdl_user)", target="_blank") {{ score.mdl_user_name }}
                    td.uk-text-right(v-html="formatScore(score.score_total)")
                    td.uk-text-right(v-html="formatScore(score.score_best)")
                tr(v-if="loserScores.length > 0")
                    td.uk-text-center(colspan="5")
                        v-icon(name="ellipsis-v")
                tr(v-for="score in loserScores", :key="score.mdl_user", :class="getScoreClasses(score)")
                    td {{ score.rank }}
                    td
                        a(:href="getProfileUrl(score.mdl_user)", target="_blank") {{ score.mdl_user_name }}
                    td.uk-text-right(v-html="formatScore(score.score_total)")
                    td.uk-text-right(v-html="formatScore(score.score_best)")
        info-alert(v-else, :message="strings.shared_highscore_empty")
</template>

<script>
import { mapState } from "vuex";
import inRange from "lodash/inRange";
import InfoAlert from "../../helper/info-alert";

export default {
  props: {
    maxRows: Number,
    scores: Array,
    mdlUser: {
      type: Number,
      required: false,
      default: null,
    },
  },
  computed: {
    ...mapState(["strings", "game"]),
    filteredScores() {
      const ownScore = this.scores.find((score) => {
        return parseInt(score.mdl_user) === this.mdlUser;
      });
      return this.scores.filter((score) => {
        // show first x rows in any case
        if (score.rank <= this.maxRows) {
          return true;
        }
        // if own score not within first x: show one before and one after own score as well
        if (ownScore) {
          if (inRange(score.rank, ownScore.rank - 1, ownScore.rank + 2)) {
            return true;
          }
        }
        return false;
      });
    },
    firstLoser() {
      return this.filteredScores.find((score, index) => {
        const prev = index === 0 ? null : this.filteredScores[index - 1];
        return prev !== null && prev.rank < score.rank - 1;
      });
    },
    mainScores() {
      if (this.firstLoser) {
        return this.filteredScores.filter((score) => {
          return score.rank < this.firstLoser.rank;
        });
      } else {
        return this.filteredScores;
      }
    },
    loserScores() {
      if (this.firstLoser) {
        return this.filteredScores.filter((score) => {
          return score.rank >= this.firstLoser.rank;
        });
      } else {
        return [];
      }
    },
  },
  methods: {
    formatScore(score) {
      return score.toFixed(0);
    },
    getScoreClasses(score) {
      let result = [];
      if (score.mdl_user === this.mdlUser) {
        result.push("uk-text-bold");
      }
      return result.join(" ");
    },
    getProfileUrl(userId) {
      const baseUrl = window.location.origin;
      return `${baseUrl}/user/profile.php?id=${userId}`;
    },
  },
  components: { InfoAlert },
};
</script>
