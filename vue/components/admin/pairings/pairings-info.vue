<template lang="pug">
    div.uk-alert.uk-alert-primary
       p
            span {{ strings.admin_tournament_pairing_info_participants | stringParams(participants.length) }}
</template>
<script>
    import Mixins from '../../../mixins';
    import {mapState} from 'vuex';
    export default {
        mixins: [Mixins],
        props: {
            tournament: Object,
            participants: Array,
        },
        computed: {
            ...mapState(['strings']),
        }
    }
</script>
