<template lang="pug">
    #challenge-highscore
        .uk-card.uk-card-default
            .uk-card-header
                h3 {{ strings.shared_highscore_title }}
            .uk-card-body
                loadingAlert(v-if="loading", :message="strings.shared_highscore_loading")
                failureAlert(v-else-if="failed", :message="strings.shared_highscore_loading_failed")
                highscore(v-else, :maxRows="5", :scores="scores", :mdl-user="game.mdl_user")
</template>

<script>
import { mapActions, mapState } from "vuex";
import loadingAlert from "../../helper/loading-alert";
import failureAlert from "../../helper/failure-alert";
import highscore from "./highscore";

export default {
  data() {
    return {
      loading: true,
      failed: false,
    };
  },
  computed: {
    ...mapState(["strings", "game", "scores"]),
  },
  methods: {
    ...mapActions(["fetchScores"]),
  },
  mounted() {
    this.fetchScores()
      .then(() => {
        this.loading = false;
      })
      .catch(() => {
        this.loading = false;
        this.failed = true;
      });
  },
  components: {
    loadingAlert,
    failureAlert,
    highscore,
  },
};
</script>
