<template lang="pug">
  img.userpicture.defaultuserpic(:width="size", :height="size",
    :title="strings.admin_avatar_alt | stringParams(user.firstname + ' ' + user.lastname)",
    :alt="strings.admin_avatar_alt | stringParams(user.firstname + ' ' + user.lastname)",
    :src="user.image", :style="'width: ' + size + 'px; height: ' + size + 'px;'")
</template>

<script>
import langMixins from "../../mixins/lang-mixins";
import { mapState } from "vuex";

export default {
  mixins: [langMixins],
  props: {
    size: {
      type: Number,
      default: 50,
    },
    user: Object,
  },
  computed: mapState(["strings"]),
};
</script>
