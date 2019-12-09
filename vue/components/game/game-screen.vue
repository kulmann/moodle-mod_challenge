<template lang="pug">
    #challenge-game_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Game").uk-text-center
        failureAlert(v-else-if="isAdminUser", :message="strings.game_not_allowed").uk-text-center
        template(v-else)
            .uk-margin-small-top.uk-margin-small-bottom
                span there is no game to play, yet.
</template>

<script>
    import mixins from '../../mixins';
    import {mapGetters, mapState} from 'vuex';
    import loadingAlert from '../helper/loading-alert';
    import VkGrid from "vuikit/src/library/grid/components/grid";

    export default {
        mixins: [mixins],
        computed: {
            ...mapState([
                'strings',
            ]),
            ...mapGetters([
                'isAdminUser',
                'isInitialized'
            ]),
        },
        components: {
            loadingAlert,
            VkGrid
        }
    }
</script>
