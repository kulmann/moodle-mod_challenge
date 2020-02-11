<template lang="pug">
    #challenge-admin_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Settings").uk-text-center
        failureAlert(v-else-if="!isAdminUser", :message="strings.admin_not_allowed").uk-text-center
        template(v-else)
            template(v-if="viewModeList")
                ul.uk-tab.uk-margin-small-bottom
                    li(:class="{'uk-active': viewMode === VIEW_MODE_ROUNDS}")
                        a(@click="setViewMode(VIEW_MODE_ROUNDS)") {{  strings.admin_nav_rounds }}
                rounds-list(v-if="viewMode === VIEW_MODE_ROUNDS",
                    :rounds="rounds",
                    :categories="categories",
                    :mdlCategories="mdlCategories",
                    key="rounds"
                )
            template(v-else)
                round-edit(v-if="viewMode === VIEW_MODE_ROUND_EDIT", :round="roundForEditing")
</template>

<script>
    import mixins from '../../mixins';
    import {mapState, mapGetters} from 'vuex';
    import _ from 'lodash';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import loadingAlert from '../helper/loading-alert';
    import failureAlert from "../helper/failure-alert";
    import RoundsList from "./rounds/rounds-list";
    import RoundEdit from "./rounds/round-edit";

    import constants from "../../constants";

    export default {
        mixins: [mixins],
        data() {
            return {
                VIEW_MODE_NONE: constants.ROUTE_UNKNOWN,
                VIEW_MODE_ROUNDS: constants.ROUTE_ADMIN_ROUNDS,
                VIEW_MODE_ROUND_EDIT: constants.ROUTE_ADMIN_ROUND_EDIT,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'rounds',
            ]),
            ...mapState('admin', [
                'categories',
                'mdlCategories'
            ]),
            ...mapGetters([
                'isAdminUser',
                'isInitialized'
            ]),
            viewModeList() {
                return _.endsWith(this.$route.name, '-list');
            },
            viewMode() {
                const routes = [
                    this.VIEW_MODE_ROUNDS,
                    this.VIEW_MODE_ROUND_EDIT,
                ];
                if (routes.includes(this.$route.name)) {
                    return this.$route.name;
                } else {
                    return this.VIEW_MODE_NONE;
                }
            },
            roundForEditing() {
                // try to find the given roundId in our loaded rounds.
                if (this.$route.params.hasOwnProperty('roundId') && !_.isUndefined(this.$route.params.roundId)) {
                    let roundId = parseInt(this.$route.params.roundId);
                    return _.find(this.rounds, round => round.id === round);
                }
                // None found. Returning null will (correctly) result in creating a new round.
                return null;
            },
        },
        methods: {
            setViewMode(viewMode) {
                if (viewMode !== this.viewMode) {
                    switch (viewMode) {
                        case this.VIEW_MODE_ROUNDS:
                            this.$router.push({name: 'admin-round-list'});
                            break;
                        default: // do nothing
                    }
                }
            }
        },
        mounted() {
            if (this.viewMode === this.VIEW_MODE_NONE) {
                this.setViewMode(this.VIEW_MODE_ROUNDS);
            }
        },
        components: {
            RoundsList,
            RoundEdit,
            VkGrid,
            failureAlert,
            loadingAlert,
        },
    }
</script>
