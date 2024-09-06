<template>
    <div class="o-settings-account">

        <IconContainer
            class="mb-10"
            header="General"
            icon="fal fa-user-circle"
        >

            <AccountGeneral
                :user="user"
            >
            </AccountGeneral>

        </IconContainer>

        <IconContainer
            header="Security"
            icon="fal fa-shield-keyhole"
        >

            <AccountSecurity
                :user="user"
            >
            </AccountSecurity>

        </IconContainer>

        <IconContainer
            header="Delete account"
            icon="fal fa-circle-minus"
        >

            <AccountDelete
                :user="user"
            >
            </AccountDelete>

        </IconContainer>
    </div>
</template>

<script>

import IconContainer from '@/components/display/IconContainer.vue';
import AccountGeneral from '@/components/settings/AccountGeneral.vue';
import AccountSecurity from '@/components/settings/AccountSecurity.vue';
import AccountDelete from '@/components/settings/AccountDelete.vue';

export default {
    name: 'SettingsAccount',
    components: {
        IconContainer,
        AccountGeneral,
        AccountSecurity,
        AccountDelete,
    },
    mixins: [
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*
.o-settings-account {

}
*/

</style>
