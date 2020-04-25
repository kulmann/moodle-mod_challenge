<template lang="pug">
    .uk-flex.uk-flex-middle.uk-flex-center.uk-text-center.uk-margin-small-bottom
        button.btn.btn-default(:disabled="isFirstMatch", @click="goToPrevMatch")
            v-icon(name="chevron-left")
        div
            b.uk-margin-small-left.uk-margin-small-right {{ strings.game_match_step | stringParams({step: value + 1, total: matches.length}) }}
            template(v-if="match")
                br
                i(v-if="match.open") {{ strings.game_match_lbl_open }}
                b.uk-text-success(v-else-if="match.mdl_user_winner === ownUserId") {{ strings.game_match_lbl_won }}
                b.uk-text-danger(v-else) {{ strings.game_match_lbl_lost }}
        button.btn.btn-default(:disabled="isLastMatch", @click="goToNextMatch")
            v-icon(name="chevron-right")
</template>

<script>
    import {mapState} from 'vuex';
    import LangMixins from '../../../mixins/lang-mixins';
    export default {
        mixins: [LangMixins],
        props: {
            value: {
                type: Number,
                required: true
            },
            ownUserId: {
                type: Number,
                required: true
            },
            match: {
                type: Object,
                required: false
            },
            matches: {
                type: Array,
                required: true
            }
        },
        computed: {
            ...mapState(['strings']),
            isFirstMatch() {
                return this.value === 0;
            },
            isLastMatch() {
                return this.value === this.matches.length - 1;
            },
        },
        methods: {
            goToPrevMatch() {
                if (this.value > 0) {
                    this.$emit('input', this.value - 1);
                }
            },
            goToNextMatch() {
                if (this.value < this.matches.length - 1) {
                    this.$emit('input', this.value + 1);
                }
            }
        }
    }
</script>
