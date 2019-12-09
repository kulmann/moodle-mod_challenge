<template lang="pug">
    table.uk-table.uk-table-striped
        thead
            tr
                th.uk-table-expand {{ strings.game_tournaments_list_name }}
                th.uk-table-shrink &nbsp;
        tbody
            tr(v-for="tournament in tournaments").uk-table-middle
                td {{ tournament.name }}
                td
                    button.btn.btn-primary(@click="goToTournament(tournament.id)") {{ strings.game_btn_show }}
</template>
<script>
    import {mapState} from 'vuex';
    import infoAlert from "../../helper/info-alert";

    export default {
        props: {
            tournaments: Array,
        },
        computed: {
            ...mapState(['strings']),
        },
        methods: {
            goToTournament(id) {
                this.$router.push({name: 'player-tournament-show', params: {tournamentId: id}});
            }
        },
        components: {infoAlert}
    }
</script>
