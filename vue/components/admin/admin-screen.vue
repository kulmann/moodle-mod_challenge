<template lang="pug">
    #challenge-admin_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Settings").uk-text-center
        failureAlert(v-else-if="!isAdminUser", :message="strings.admin_not_allowed", icon="exclamation-circle").uk-text-center
        template(v-else)
            levelEdit(v-if="showLevelEdit", :level="levelForEditing").uk-margin-small-top
            levels(v-else-if="showLevelList", :levels="levels").uk-margin-small-top
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
        computed: {
            ...mapState([
                'strings',
            ]),
            ...mapGetters([
                'isAdminUser',
                'isInitialized'
            ]),
            ...mapState('admin', ['levels']),
            showLevelEdit() {
                return this.$route.name === 'admin-level-edit';
            },
            levelForEditing() {
                // try to find the given levelId in our loaded levels.
                if (this.$route.params.hasOwnProperty('levelId') && !_.isUndefined(this.$route.params.levelId)) {
                    let levelId = parseInt(this.$route.params.levelId);
                    return _.find(this.levels, function (level) {
                        return level.id === levelId;
                    });
                }
                // None found. Returning null will (correctly) result in creating a new level.
                return null;
            },
            showLevelList() {
                return this.$route.name === 'admin-level-list'
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
