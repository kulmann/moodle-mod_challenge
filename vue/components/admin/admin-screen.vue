<template lang="pug">
    #challenge-admin_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Settings").uk-text-center
        failureAlert(v-else-if="!isAdminUser", :message="strings.admin_not_allowed", icon="exclamation-circle").uk-text-center
        template(v-else)
            levelEdit(v-if="viewMode === VIEW_MODE_LEVEL_EDIT", :level="levelForEditing")
            levels(v-else-if="viewMode === VIEW_MODE_LEVELS", :levels="levels")
</template>

<script>
    import mixins from '../../mixins';
    import {mapState, mapGetters} from 'vuex';
    import levels from './levels';
    import loadingAlert from '../helper/loading-alert';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import FailureAlert from "../helper/failure-alert";
    import levelEdit from "./level-edit";
    import _ from 'lodash';

    export default {
        mixins: [mixins],
        data () {
            return {
                VIEW_MODE_NAV: 'navigation',
                VIEW_MODE_LEVELS: 'levels',
                VIEW_MODE_LEVEL_EDIT: 'levelEdit',
                VIEW_MODE_TOURNAMENTS: 'tournaments',
                VIEW_MODE_TOURNAMENT_EDIT: 'tournamentEdit',
            }
        },
        computed: {
            ...mapState([
                'strings',
            ]),
            ...mapGetters([
                'isAdminUser',
                'isInitialized'
            ]),
            ...mapState('admin', ['levels']),
            viewMode() {
                if (this.$route.name === 'admin-level-list') {
                    return this.VIEW_MODE_LEVELS;
                } else if (this.$route.name === 'admin-level-edit') {
                    return this.VIEW_MODE_LEVEL_EDIT;
                } else if (this.$route.name === 'admin-tournament-list') {
                    return this.VIEW_MODE_TOURNAMENTS;
                } else if (this.$route.name === 'admin-tournament-edit') {
                    return this.VIEW_MODE_TOURNAMENT_EDIT;
                } else {
                    return this.VIEW_MODE_NAV;
                }
            },
            levelForEditing() {
                // try to find the given levelId in our loaded levels.
                if (this.$route.params.hasOwnProperty('levelId') && !_.isNil(this.$route.params.levelId)) {
                    let levelId = parseInt(this.$route.params.levelId);
                    return _.find(this.levels, function (level) {
                        return level.id === levelId;
                    });
                }
                // None found. Returning null will (correctly) result in creating a new level.
                return null;
            },
        },
        components: {
            levelEdit,
            FailureAlert,
            levels,
            loadingAlert,
            VkGrid
        },
    }
</script>

<style scoped>
</style>
