<template lang="pug">
vk-grid.uk-flex-middle.uk-margin-bottom(matched)
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
</template>

<script>
import MatchUser from "./match-user";
import MatchScore from "./match-score";
import { mapGetters } from "vuex";

export default {
  components: {
    MatchScore,
    MatchUser,
  },
  props: {
    round: {
      type: Object,
      required: true,
    },
    match: {
      type: Object,
      required: true,
    },
    questions: {
      type: Array,
      required: true,
    },
    attempts: {
      type: Array,
      required: true,
    },
  },
  computed: {
    ...mapGetters(["getMdlUser"]),
  },
  methods: {
    getMatchUser1(match) {
      return this.getMdlUser(match.mdl_user_1);
    },
    getMatchUser2(match) {
      return this.getMdlUser(match.mdl_user_2);
    },
  },
};
</script>
