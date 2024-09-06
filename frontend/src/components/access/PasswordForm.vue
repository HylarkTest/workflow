<template>
    <div class="o-password-form">
        <InputPassword
            ref="passwordInput"
            formField="password"
            name="password"
            autocomplete="new-password"
            :motion="true"
            :newPasswordInput="true"
        >
        </InputPassword>

        <PasswordGuide
            class="mt-3"
            :text="form.password"
            @passwordCriteria="$emit('passwordCriteria', $event)"
        >
        </PasswordGuide>
    </div>
</template>

<script>

import PasswordGuide from '@/components/access/PasswordGuide.vue';

export default {
    name: 'PasswordForm',
    components: {
        PasswordGuide,
    },
    mixins: [
    ],
    props: {
        form: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'passwordCriteria',
    ],
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
    mounted() {
        this.$refs.passwordInput.$refs.input.$refs.input.focus();
    },
};
</script>

<style scoped>

/*.o-password-form {

} */

</style>
