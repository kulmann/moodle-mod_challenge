<template lang="pug">
    #challenge-admin_screen
        .uk-clearfix
        loadingAlert(v-if="!isInitialized", message="Loading Settings").uk-text-center
        failureAlert(v-else-if="!isAdminUser", :message="strings.admin_not_allowed").uk-text-center
        template(v-else)
            template(v-if="viewModeList")
                ul.uk-tab.uk-margin-small-bottom
                    li(:class="{'uk-active': viewMode === VIEW_MODE_TOURNAMENTS}")
                        a(@click="setViewMode(VIEW_MODE_TOURNAMENTS)") {{  strings.admin_nav_tournaments }}
                    li(:class="{'uk-active': viewMode === VIEW_MODE_LEVELS}")
                        a(@click="setViewMode(VIEW_MODE_LEVELS)") {{  strings.admin_nav_levels }}
                tournaments(v-if="viewMode === VIEW_MODE_TOURNAMENTS",
                    :editableTournaments="editableTournaments",
                    :activeTournaments="activeTournaments",
                    :finishedTournaments="finishedTournaments"
                )
                levels(v-else-if="viewMode === VIEW_MODE_LEVELS", :levels="levels")
            template(v-else)
                tournamentEdit(v-if="viewMode === VIEW_MODE_TOURNAMENT_EDIT", :tournament="tournamentForEditing")
                tournamentPairings(v-else-if="viewMode === VIEW_MODE_TOURNAMENT_PAIRINGS", :tournament="tournamentForEditing")
                levelEdit(v-else-if="viewMode === VIEW_MODE_LEVEL_EDIT", :level="levelForEditing")
</template>

<script>
    import mixins from '../../mixins';
    import {mapState, mapGetters} from 'vuex';
    import _ from 'lodash';
    import VkGrid from "vuikit/src/library/grid/components/grid";
    import loadingAlert from '../helper/loading-alert';
    import failureAlert from "../helper/failure-alert";
    import levels from './levels';
    import levelEdit from "./level-edit";
    import tournamentEdit from "./tournament-edit";
    import tournaments from "./tournaments";
    import tournamentPairings from "./tournament-pairings";

    export default {
        mixins: [mixins],
        data() {
            return {
                VIEW_MODE_NONE: 'none',
                VIEW_MODE_LEVELS: 'levels',
                VIEW_MODE_LEVEL_EDIT: 'levelEdit',
                VIEW_MODE_TOURNAMENTS: 'tournaments',
                VIEW_MODE_TOURNAMENT_EDIT: 'tournamentEdit',
                VIEW_MODE_TOURNAMENT_PAIRINGS: 'tournamentPairings',
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
            ...mapState('admin', [
                'levels',
                'editableTournaments',
                'activeTournaments',
                'finishedTournaments',
            ]),
            viewModeList() {
                return _.endsWith(this.$route.name, '-list');
            },
            viewMode() {
                if (this.$route.name === 'admin-level-list') {
                    return this.VIEW_MODE_LEVELS;
                } else if (this.$route.name === 'admin-level-edit') {
                    return this.VIEW_MODE_LEVEL_EDIT;
                } else if (this.$route.name === 'admin-tournament-list') {
                    return this.VIEW_MODE_TOURNAMENTS;
                } else if (this.$route.name === 'admin-tournament-edit') {
                    return this.VIEW_MODE_TOURNAMENT_EDIT;
                } else if (this.$route.name === 'admin-tournament-pairings') {
                    return this.VIEW_MODE_TOURNAMENT_PAIRINGS;
                } else {
                    return this.VIEW_MODE_NONE;
                }
            },
            levelForEditing() {
                // try to find the given levelId in our loaded levels.
                if (this.$route.params.hasOwnProperty('levelId') && !_.isUndefined(this.$route.params.levelId)) {
                    let levelId = parseInt(this.$route.params.levelId);
                    return _.find(this.levels, level => level.id === levelId);
                }
                // None found. Returning null will (correctly) result in creating a new level.
                return null;
            },
            tournamentForEditing() {
                // try to find the given tournamentId in our loaded tournaments.
                if (this.$route.params.hasOwnProperty('tournamentId') && !_.isUndefined(this.$route.params.tournamentId)) {
                    let tournamentId = parseInt(this.$route.params.tournamentId);
                    return _.find(this.editableTournaments, tournament => tournament.id === tournamentId);
                }
                // None found. Returning null will (correctly) result in creating a new tournament.
                return null;
            },
        },
        methods: {
            setViewMode(viewMode) {
                if (viewMode !== this.viewMode) {
                    switch (viewMode) {
                        case this.VIEW_MODE_TOURNAMENTS:
                            this.$router.push({name: 'admin-tournament-list'});
                            break;
                        case this.VIEW_MODE_LEVELS:
                            this.$router.push({name: 'admin-level-list'});
                            break;
                        default: // do nothing
                    }
                }
            }
        },
        mounted() {
            if (this.viewMode === this.VIEW_MODE_NONE) {
                this.setViewMode(this.VIEW_MODE_TOURNAMENTS);
            }
        },
        components: {
            VkGrid,
            failureAlert,
            loadingAlert,
            levels,
            levelEdit,
            tournamentEdit,
            tournamentPairings,
            tournaments
        },
    }
</script>

<style scoped>
</style>
