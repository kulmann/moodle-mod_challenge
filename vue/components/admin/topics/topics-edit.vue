<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="matches === null || topics === null")
            loadingAlert(:message="strings.admin_tournament_topics_loading")
        template(v-else)
            .uk-card-body
                h3 {{ strings.admin_tournament_title_topics }}
                
            .uk-card-footer.uk-text-right
                button.btn.btn-primary(@click="save()", :disabled="saving || isDataInvalid")
                    v-icon(name="save").uk-margin-small-right
                    span {{ strings.admin_btn_save }}
                button.btn.btn-default(@click="goToTournamentList()", :disabled="saving").uk-margin-small-left
                    v-icon(name="ban").uk-margin-small-right
                    span {{ strings.admin_btn_cancel }}
                .uk-alert.uk-alert-primary.uk-text-center(uk-alert, v-if="saving")
                    p
                        span {{ strings.admin_tournament_topics_saving }}
                        loadingIcon
</template>

<script>
    import {mapActions, mapState} from 'vuex';
    import mixins from '../../../mixins';
    import loadingAlert from "../../helper/loading-alert";
    import loadingIcon from "../../helper/loading-icon";

    export default {
        mixins: [mixins],
        props: {
            tournament: Object,
        },
        data() {
            return {
                matches: null,
                topics: null,
                saving: false,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
                'mdlUsers',
            ]),
            ...mapState('admin', [
                'levels',
            ]),
            isDataInvalid() {
                return this.topics === null || this.topics.length < this.matches.length;
            },
        },
        methods: {
            ...mapActions({
                fetchMatches: 'admin/fetchMatches',
                fetchTopics: 'admin/fetchTopics',
                saveTopics: 'admin/saveTopics',
            }),
            initData(tournament) {
                this.fetchMatches({tournamentid: tournament.id}).then(matches => {
                    this.matches = _.map(matches, match => {
                        return {
                            mdl_user_1: match.mdl_user_1,
                            mdl_user_2: match.mdl_user_2,
                        };
                    });
                });
                this.fetchTopics({tournamentid: tournament.id}).then(topics => {
                    this.topics = topics;
                });
            },
            goToTournamentList() {
                this.$router.push({name: 'admin-tournament-list'});
            },
            save() {
                if (this.isDataInvalid) {
                    return;
                }
                let payload = {
                    tournamentid: this.tournament.id,
                    topics: this.topics,
                };
                this.saving = true;
                this.saveTopics(payload)
                    .then((successful) => {
                        this.saving = false;
                        if (successful) {
                            this.goToTournamentList();
                        }
                    });
            },
        },
        mounted() {
            this.initData(this.tournament);
        },
        watch: {
            tournament(tournament) {
                this.initData(tournament);
            },
        },
        components: {
            loadingIcon,
            loadingAlert,
        },
    }
</script>
d
