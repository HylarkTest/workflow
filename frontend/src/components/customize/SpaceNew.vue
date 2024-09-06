<template>
    <div class="o-space-new">

        <h1
            class="header-2 mb-8"
        >
            Create a new space
        </h1>

        <FormWrapper
            class="flex flex-col justify-between flex-1"
            :form="form"
            @submit="saveNewSpace"
        >
            <InputBox
                ref="input"
                formField="name"
                bgColor="gray"
            >
                <template #label>
                    What is your new space called?
                </template>
            </InputBox>

            <div class="flex justify-end mt-4">
                <button
                    class="button text-cm-00 bg-primary-600"
                    :class="{ unclickable: disabled }"
                    type="submit"
                    :disabled="disabled"
                >
                    Add space
                </button>
            </div>
        </FormWrapper>

        <UpgradeOverlaySpaces
            v-if="needsToUpgrade"
        >
        </UpgradeOverlaySpaces>
    </div>
</template>

<script>

import UpgradeOverlaySpaces from '@/components/upgrades/UpgradeOverlaySpaces.vue';

export default {
    name: 'SpaceNew',
    components: {
        UpgradeOverlaySpaces,
    },
    mixins: [
    ],
    props: {

    },
    emits: [
        'saveNewSpace',
    ],
    data() {
        return {
            form: this.$apolloForm({
                name: '',
            }),
            processing: false,
        };
    },
    computed: {
        disabled() {
            return !this.form.name || this.processing;
        },
        needsToUpgrade() {
            return false;
        },
    },
    methods: {
        saveNewSpace() {
            this.processing = true;
            this.$emit('saveNewSpace', this.form);
        },
    },
    created() {

    },
    mounted() {
        if (!this.needsToUpgrade) {
            this.$refs.input.select();
        }
    },
};
</script>

<style scoped>

.o-space-new {
    @apply
        flex
        flex-col
        h-full
        relative
    ;
}

</style>
