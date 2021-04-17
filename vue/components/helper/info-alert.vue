<template lang="pug">
  .uk-alert.uk-alert-primary(uk-alert)
    p
      v-icon(v-if="icon", :name="icon").uk-margin-small-right
      span {{ message }}
</template>

<script>
export default {
  props: {
    icon: {
      type: String,
      default: "info-circle",
    },
    message: {
      type: String,
      required: true,
    },
  },
};
</script>
