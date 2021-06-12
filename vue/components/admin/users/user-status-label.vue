<template lang="pug">
span.uk-flex.uk-flex-middle
  v-icon.uk-margin-small-right.uk-preserve-width(
    :name="iconName",
    :class="iconClasses"
  )
  span {{ label }}
</template>

<script>
import { mapState } from "vuex";

export default {
  name: "UserStatusLabel",
  props: {
    status: {
      type: String,
      required: true,
    },
  },
  computed: {
    ...mapState(["strings"]),
    isEnabled() {
      return this.status === "enabled";
    },
    iconName() {
      return this.isEnabled ? "user-check" : "user-times";
    },
    iconClasses() {
      return this.isEnabled ? ["status-enabled"] : ["status-disabled"];
    },
    label() {
      return this.strings["admin_users_participants_status_" + this.status];
    },
  },
};
</script>

<style lang="scss" scoped>
.status-enabled {
  color: green;
}
.status-disabled {
  color: maroon;
}
</style>
