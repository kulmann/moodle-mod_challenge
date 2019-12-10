<template lang="pug">
    vk-grid(matched).uk-grid-divider
        div.uk-width-1-2
            userAvatar(:size="100", :user="user1")
            br
            h3 {{ user1.firstname }} {{ user1.lastname }}
        div.uk-width-1-2
            userAvatar(:size="100", :user="user2")
            br
            h3 {{user2.firstname }} {{ user2.lastname }}
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
