<template>
  <div class="not-found">
    <h3>{{ strings.route_not_found }}</h3>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  computed: mapState(["strings"]),
  created: function () {},
};
</script>
