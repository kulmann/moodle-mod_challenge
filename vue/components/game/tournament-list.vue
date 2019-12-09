<template lang="pug">
    table.uk-table.uk-table-striped
        thead
            tr
                th
                    span Name
        tbody
            tr(v-for="tournament in tournaments")
                td
                    span {{ tournament.name }}
</template>
<script>
    import {mapState} from 'vuex';
    import infoAlert from "../helper/info-alert";

    export default {
        props: {
            tournaments: Array,
        },
        computed: {
            ...mapState(['strings']),
        },
        components: {infoAlert}
    }
</script>
