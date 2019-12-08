<template lang="pug">
    #challenge-loading_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Game").uk-text-center
</template>

<script>
    import mixins from "../mixins";
    import {mapGetters} from "vuex";
    import loadingAlert from "./helper/loading-alert";

    export default {
        mixins: [mixins],
        computed: mapGetters([
            'isAdminUser',
            'isInitialized',
        ]),
        methods: {
            goToUserScreen() {
                if (this.isAdminUser) {
                    this.$router.push({name: 'admin-screen'});
                } else {
                    this.$router.push({name: 'game-screen'});
                }
            }
        },
        mounted () {
            if (this.isInitialized) {
                this.goToUserScreen();
            }
        },
        watch: {
            isInitialized (initialized) {
                if (initialized) {
                    this.goToUserScreen();
                }
            }
        },
        components: {loadingAlert}
    }
</script>
