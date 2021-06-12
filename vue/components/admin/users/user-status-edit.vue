<template lang="pug">
div
  span._pointer.uk-flex.uk-flex-middle(v-if="!editMode", @click="onClickLabel")
    user-status-label(:status="value")
    v-icon.uk-margin-small-left.uk-preserve-width(name="edit")
  span(v-else)
    ul.uk-list
      li(v-for="status in statusItems")
        label._pointer.uk-flex.uk-flex-middle
          input.uk-radio.uk-margin-small-right(
            type="radio",
            :value="status",
            :checked="status === value",
            @change="onSelectStatus(status)"
          )
          user-status-label(:status="status")
</template>

<script>
import { mapState } from "vuex";
import UserStatusLabel from "./user-status-label";

const STATUS_ENABLED = "enabled";
const STATUS_DISABLED = "disabled";

export default {
  name: "UserStatusEdit",
  components: { UserStatusLabel },
  props: {
    id: {
      type: Number,
      required: true,
    },
    value: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      editMode: false,
    };
  },
  computed: {
    ...mapState(["strings"]),
    statusItems() {
      return [STATUS_ENABLED, STATUS_DISABLED];
    },
  },
  methods: {
    onClickLabel() {
      this.editMode = true;
    },
    onSelectStatus(status) {
      this.editMode = false;
      this.$emit("input", { id: this.id, status });
    },
  },
};
</script>
