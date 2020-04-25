<template lang="pug">
    #challenge-question_actions
        .uk-heading-divider.uk-margin-small-bottom
        .uk-align-right
            button.btn.btn-default(@click="goToMatch")
                v-icon(name="arrow-circle-right").uk-margin-small-right
                span {{ buttonContinueText }}
</template>

<script>
    import {mapMutations, mapState} from 'vuex';

    export default {
        props: {
            question: Object,
            attempt: Object,
        },
        data() {
            return {
                countdownValue: 0,
                timer: null,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
            buttonContinueText() {
                if (this.game.review_duration > 0) {
                    return this.strings.game_btn_continue + ' (' + this.countdownValue + ')';
                } else {
                    return this.strings.game_btn_continue;
                }
            },
        },
        methods: {
            ...mapMutations([
                'setGameMode'
            ]),
            goToMatch() {
                if (this.timer) {
                    clearInterval(this.timer);
                }
                const matchId = this.question.matchid;
                this.$router.push({name: 'player-match-show', params: {forcedMatchId: matchId}});
            },
        },
        mounted() {
            if (this.game.review_duration <= 0) {
                // only becomes relevant if auto-continue is enabled
                return;
            }
            if (this.attempt && this.attempt.finished) {
                this.countdownValue = this.game.review_duration;
                this.timer = setInterval(() => {
                    this.countdownValue--;
                    if (this.countdownValue <= 0) {
                        this.goToMatch();
                    }
                }, 1000);
            }
        },
    }
</script>

<style scoped>
</style>
