<template lang="pug">
    div
        ul.uk-list.uk-list-striped
            li(v-for="user in mdlUsers")
                label
                    input.uk-checkbox(type="checkbox", v-model="selectedUsers", :value="user", @change="onChange").uk-margin-small-right
                    userAvatar._pointer(:size="20", :user="user")
                    span._pointer {{ user.firstname + ' ' + user.lastname }}
        div.uk-alert.uk-alert-primary
            span {{ strings.admin_tournament_match_info_participants | stringParams(selectedUsers.length) }}
</template>

<script>
    import {mapState} from 'vuex';
    import langMixins from '../../../mixins/lang-mixins';
    import loadingAlert from "../../helper/loading-alert";
    import btnAdd from '../btn-add';
    import loadingIcon from "../../helper/loading-icon";
    import userAvatar from "../../helper/user-avatar";

    export default {
        mixins: [langMixins],
        props: {
            tournament: Object,
            value: Array,
        },
        data () {
            return {
                selectedUsers: [],
            }
        },
        computed: {
            ...mapState([
                'strings',
                'game',
                'mdlUsers',
            ]),
        },
        methods: {
            onChange () {
                this.$emit('input', this.selectedUsers);
            }
        },
        mounted() {
            this.selectedUsers = this.value;
        },
        watch: {
            selectedUsers () {
                this.selectedUsers = this.value;
            }
        },
        components: {
            userAvatar,
            loadingIcon,
            loadingAlert,
            btnAdd,
        },
    }
</script>
