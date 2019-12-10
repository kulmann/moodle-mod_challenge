<template lang="pug">
    vk-grid(matched).uk-grid-divider
        div.uk-width-1-2
            userAvatar(:size="200", :user="user1")
            .match-user-name.uk-margin-small-top {{ user1.firstname }} {{ user1.lastname }}
        div.uk-width-1-2
            userAvatar(:size="200", :user="user2")
            .match-user-name.uk-margin-small-top {{user2.firstname }} {{ user2.lastname }}
</template>

<script>
    import {mapGetters} from 'vuex';
    import userAvatar from "../../../helper/user-avatar";

    export default {
        props: {
            match: Object,
        },
        computed: {
            ...mapGetters(['getMdlUser']),
            user1 () {
                return this.getMdlUser(this.match.mdl_user_1);
            },
            user2 () {
                return this.getMdlUser(this.match.mdl_user_2);
            },
        },
        components: {userAvatar}
    }
</script>

<style lang="scss" scoped>
    .match-user-name {
        font-size: 2em;
        text-align: center;
    }
</style>
