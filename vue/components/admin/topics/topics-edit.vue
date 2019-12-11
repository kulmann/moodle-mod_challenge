<template lang="pug">
    .uk-card.uk-card-default
        .uk-card-body(v-if="topics === null")
            loadingAlert(:message="strings.admin_tournament_topics_loading")
        template(v-else)
            .uk-card-body
                h3 {{ strings.admin_tournament_title_topics }}
                table.uk-table.uk-table-striped
                    thead
                        tr
                            th.uk-table-shrink {{ strings.admin_tournament_topics_lbl_step }}
                            th.uk-table-expand {{ strings.admin_tournament_topics_lbl_levels }}
                    tbody
                        tr(v-for="step in stepIndices")
                            td.uk-text-center
                                b {{ step + 1 }}
                            td
                                b TODO: have to add level selection here


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
    import rangeInclusive from 'range-inclusive';
    import loadingAlert from "../../helper/loading-alert";
    import loadingIcon from "../../helper/loading-icon";
    import editableDropDown from "../../helper/editable-drop-down";

    export default {
        mixins: [mixins],
        props: {
            tournament: Object,
        },
        data() {
            return {
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
                return this.topics === null || this.topics.length < this.steps;
            },
            steps() {
                return this.tournament.number_of_steps;
            },
            stepIndices() {
                return rangeInclusive(0, this.steps - 1, 1);
            }
        },
        methods: {
            ...mapActions({
                fetchTopics: 'admin/fetchTopics',
                saveTopics: 'admin/saveTopics',
            }),
            initData(tournament) {
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
            editableDropDown,
            loadingIcon,
            loadingAlert,
        },
    }
</script>
d
