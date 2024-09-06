<template>
    <Modal
        class="o-features-settings-modal"
        containerClass="w-3/4"
        :containerStyle="{ height: '80vh' }"
        @closeModal="closePageSettings"
    >
        <DataEdit
            :page="page"
            :defaultView="defaultView"
            v-bind="$attrs"
            @closeModal="closePageSettings"
        >
        </DataEdit>
    </Modal>
</template>

<script>

import DataEdit from '@/components/customize/DataEdit.vue';

export default {
    name: 'FeaturesSettingsModal',
    components: {
        DataEdit,
    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closePageSettings',
    ],
    data() {
        return {

        };
    },
    computed: {
        defaultView() {
            return this.page.isMainFeaturePage ? 'FEATURE' : 'PAGE';
        },
    },
    methods: {
        closePageSettings() {
            this.$emit('closePageSettings');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-features-settings-modal {

} */

</style>
