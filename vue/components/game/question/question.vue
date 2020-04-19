<template lang="pug">
    div
        loadingAlert(v-if="loading", :message="strings.game_loading_question")
        template(v-else)
            div(:is="componentByType", :game="game", :question="question", :mdl_question="mdl_question", :mdl_answers="mdl_answers", @reloadQuestion="loadQuestion")
            question-actions(v-if="areActionsAllowed", :question="question").uk-margin-small-top
</template>

<script>
    import {mapState, mapActions} from 'vuex';
    import langMixins from '../../../mixins/lang-mixins';
    import questionActions from './question-actions';
    import questionError from './question-error';
    import questionSingleChoice from './question-singlechoice';
    import loadingAlert from '../../helper/loading-alert';

    export default {
        mixins: [langMixins],
        data () {
            return {
                question: null,
                mdl_question: null,
                mdl_answers: null,
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
            ]),
            loading() {
                return this.question === null || this.mdl_question === null || this.mdl_answers === null;
            },
            componentByType() {
                switch (this.question.mdl_question_type) {
                    case 'qtype_multichoice_single_question':
                        return 'question-single-choice';
                    default:
                        return 'question-error';
                }
            },
            areActionsAllowed() {
                // TODO: we have to determine for the logged in user, whether or not they answered their question (i.e. mdl_user_1_finished or mdl_user_2_finished).
                return false;
            },
            matchId () {
                return parseInt(this.$route.params.matchId);
            },
            questionNumber () {
                return parseInt(this.$route.params.questionNumber);
            }
        },
        methods: {
            ...mapActions({
                requestQuestionForMatch: 'player/requestQuestionForMatch',
                fetchMdlQuestion: 'fetchMdlQuestion',
                fetchMdlAnswers: 'fetchMdlAnswers',
            }),
            async loadQuestion() {
                this.question = null;
                this.mdl_question = null;
                this.mdl_answers = null;
                this.question = await this.requestQuestionForMatch({
                    matchid: this.matchId,
                    number: this.questionNumber
                });
                this.mdl_question = await this.fetchMdlQuestion({
                    questionid: this.question.id
                });
                this.mdl_answers = await this.fetchMdlAnswers({
                    questionid: this.question.id
                });
            }
        },
        mounted() {
            this.loadQuestion();
        },
        watch: {
            matchId() {
                this.loadQuestion();
            },
            questionNumber() {
                this.loadQuestion();
            }
        },
        components: {
            loadingAlert,
            questionActions,
            questionError,
            questionSingleChoice,
        }
    }
</script>
