<template lang="pug">
  #challenge-question_error
    .uk-alert.uk-alert-danger(uk-alert)
      p {{ strings.game_qtype_not_supported | stringParams(this.question.mdl_question_type) }}
</template>

<script>
import { mapState } from "vuex";
import langMixins from "../../../mixins/lang-mixins";

export default {
  mixins: [langMixins],
  props: {
    question: Object,
  },
  computed: {
    ...mapState(["strings"]),
  },
};
</script>
