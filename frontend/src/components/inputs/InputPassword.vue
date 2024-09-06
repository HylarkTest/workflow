<template>
    <component
        ref="input"
        :is="componentName"
        class="c-input-password"
        :icon="fullIcon"
        :type="fieldType"
        dataFormType="password"
        v-bind="$attrs"
        @clickIcon="toggleVisibility"
    >
        <template
            v-if="labelPath"
            #label
        >
            {{ $t(labelPath) }}
        </template>

    </component>
</template>

<script>

export default {
    name: 'InputPassword',
    components: {

    },
    mixins: [
    ],
    props: {
        componentName: {
            type: String,
            default: 'InputLine',
        },
        newPasswordInput: Boolean,
        labelPath: {
            type: [String, Boolean],
            default: 'labels.password',
        },
    },
    data() {
        return {
            passwordVisibility: false,
        };
    },
    computed: {
        dataFormType() {
            return this.newPasswordInput ? 'newPassword' : 'password';
        },
        fieldType() {
            return this.passwordVisibility ? 'text' : 'password';
        },
        fullIcon() {
            return {
                symbol: `fal ${this.iconClass}`,
                position: 'right',
                component: 'button',
            };
        },
        iconClass() {
            return this.passwordVisibility ? 'fa-eye-slash' : 'fa-eye';
        },
    },
    methods: {
        toggleVisibility() {
            this.passwordVisibility = !this.passwordVisibility;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*
.o-input-password {

}
 */

</style>
