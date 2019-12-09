<template lang="pug">
    div
        failureAlert(v-if="!validUserSelection", :message="strings.admin_tournament_pairing_invalid_users")
        template(v-else)
            button.btn.btn-primary(@click="generatePairings") {{ strings.admin_btn_generate }}
</template>
<script>
    import _ from 'lodash';
    import {mapState, mapActions} from 'vuex';
    import failureAlert from "../../helper/failure-alert";

    export default {
        props: {
            tournament: Object,
            participants: Array,
            value: Array,
        },
        data () {
            return {
                pairings: [],
            }
        },
        computed: {
            ...mapState([
                'strings',
            ]),
            validUserSelection() {
                const count = this.participants.length;
                return count > 0 && count % 2 === 0;
            },
        },
        methods: {
            ...mapActions({
                savePairings: 'admin/savePairings',
            }),
            generatePairings() {
                let mdlUserIds = _.shuffle(_.map(this.participants, p => p.id));
                let pairings = [];
                while(mdlUserIds.length > 1) {
                    pairings.push({
                        mdl_user_1: mdlUserIds.pop(),
                        mdl_user_2: mdlUserIds.pop(),
                    });
                }
                this.pairings = pairings;
                this.$emit('input', this.pairings);
            },
        },
        mounted() {
            this.pairings = this.value;
        },
        watch: {
            value() {
                this.pairings = this.value;
            },
            participants() {
                this.$emit('input', []);
            }
        },
        components: {failureAlert},
    }
</script>
