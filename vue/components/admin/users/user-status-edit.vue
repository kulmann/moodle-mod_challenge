<template lang="pug">
span
  span._pointer.uk-flex.uk-flex-middle(v-if="!editMode", @click="onClickLabel")
    span {{ selectedLabel }}
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
          span {{ getStatusLabel(status) }}
</template>

<script>
import { mapState } from "vuex";

const STATUS_ENABLED = "enabled";
const STATUS_DISABLED = "disabled";

export default {
  name: "UserStatusEdit",
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
    selectedLabel() {
      return this.getStatusLabel(this.value);
    },
    statusItems() {
      return [STATUS_ENABLED, STATUS_DISABLED];
    },
  },
  methods: {
    getStatusLabel(status) {
      return this.strings["admin_users_participants_status_" + status];
    },
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
