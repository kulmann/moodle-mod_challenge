<template lang="pug">
div
  span
    user-avatar(:size="80", :user="user")
  b {{ user.firstname }} {{ user.lastname }}
</template>

<script>
import UserAvatar from "../../../helper/user-avatar";

export default {
  components: { UserAvatar },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
};
</script>
