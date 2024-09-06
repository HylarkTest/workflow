<template>
    <div class="o-field-other">
        <h4 class="settings-form__title">
            Other
        </h4>
        <InputHeader>
            <ToggleButton
                :modelValue="inItemCreate"
                :disabled="isRequired"
                @update:modelValue="updateItemCreate"
            >
            </ToggleButton>
            <template #header>
                Include this field when creating an item
            </template>
            <template
                v-if="isRequired"
                #paragraph
            >
                This field was made mandatory in the "Rules" section above and will be available when creating an item
            </template>
        </InputHeader>
    </div>
</template>

<script>

import InputHeader from '@/components/display/InputHeader.vue';

export default {
    name: 'FieldOther',
    components: {
        InputHeader,
    },
    mixins: [
    ],
    props: {
        form: {
            type: Object,
            required: true,
        },
        inItemCreate: Boolean,
    },
    emits: [
        'update:inItemCreate',
    ],
    data() {
        return {
        };
    },
    computed: {
        isRequired() {
            return this.form.options?.rules?.required;
        },

    },
    methods: {
        updateItemCreate(event) {
            this.$emit('update:inItemCreate', event);
        },
    },
    watch: {
        isRequired(newVal) {
            this.updateItemCreate(newVal);
        },
    },
    created() {
        if (this.isRequired) {
            this.updateItemCreate(true);
        }
    },
};
</script>

<!-- <style scoped>
.o-field-other {

}
</style> -->
