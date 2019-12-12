<template lang="pug">
    div
        loadingAlert(v-if="loading", :message="strings.game_loading_question")
        template(v-else)
            div(:is="componentByType", :game="game", :question="question", :mdl_question="mdl_question", :mdl_answers="mdl_answers", @reloadQuestion="loadQuestion")
            actions(v-if="areActionsAllowed", :question="question").uk-margin-small-top
</template>

<script>
    import {mapState, mapActions} from 'vuex';
    import finished from './finished';
    import mixins from '../../../mixins';
    import questionActions from './question-actions';
    import questionError from './question-error';
    import questionSingleChoice from './question-singlechoice';
    import loadingAlert from '../../helper/loading-alert';

    export default {
        mixins: [mixins],
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
            componentByType() {
                switch (this.question.mdl_question_type) {
                    case 'qtype_multichoice_single_question':
                        return 'singlechoice';
                    default:
                        return 'error';
                }
            },
            areActionsAllowed() {
                return this.question.finished;
            },
            topicId () {
                return parseInt(this.$route.params.topicId);
            },
            matchId () {
                return parseInt(this.$route.params.matchId);
            },
            loading() {
                return this.question === null || this.mdl_question === null || this.mdl_answers === null;
            }
        },
        methods: {
            ...mapActions({
                requestQuestionByTopic: 'player/requestQuestionByTopic',
                fetchMdlQuestion: 'player/fetchMdlQuestion',
                fetchMdlAnswers: 'player/fetchMdlAnswers',
            }),
            loadQuestion() {
                this.requestQuestionByTopic({
                    matchid: this.matchId,
                    topicid: this.topicId
                }).then(question => {
                    this.question = question;
                    this.fetchMdlQuestion({
                        questionid: this.question.id
                    }).then(mdl_question => this.mdl_question = mdl_question);
                    this.fetchMdlAnswers({
                        questionid: this.question.id
                    }).then(mdl_answers => this.mdl_answers = mdl_answers);
                });
            }
        },
        mounted() {
            this.loadQuestion();
        },
        components: {
            loadingAlert,
            finished,
            'actions': questionActions,
            'error': questionError,
            'singlechoice': questionSingleChoice,
        }
    }
</script>
