<template>
    <form
        class="c-form-wrapper"
        @submit.prevent="$emit('submit', form)"
    >
        <slot></slot>
    </form>
</template>

<script>
import Form from 'formla';

// export const formKey = Symbol('Vue form injection');
export const formKey = 'Vue form injection';

export default {
    provide() {
        return {
            [formKey]: this.sharedState,
        };
    },
    props: {
        form: {
            type: Form,
            default: () => new Form({}),
        },
        dontScroll: Boolean,
    },
    emits: [
        'submit',
    ],
    data() {
        return {
            sharedState: {
                form: this.form,
                dontScroll: this.dontScroll,
            },
        };
    },
    watch: {
        form(newForm) {
            this.sharedState.form = newForm;
        },
    },
};
</script>
