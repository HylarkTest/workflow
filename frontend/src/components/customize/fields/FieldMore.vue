<template>
    <div class="o-field-more">
        <SettingsHeaderLine>
            <template
                #header
            >
                <ButtonEl
                    class="flex"
                    @click="toggleMore"
                >
                    <div
                        class="button-primary circle-center h-5 w-5 mr-2"
                    >
                        <i
                            class="far"
                            :class="showMore ? 'fa-angle-up' : 'fa-angle-down'"
                        >
                        </i>
                    </div>
                    More options
                </ButtonEl>
            </template>

            <div
                v-if="showMore"
                class="p-3 rounded-xl bg-cm-100"
            >
                <FieldOptions
                    :options="options"
                    :meta="meta"
                    :field="field"
                    :isNew="isNew"
                    @update:options="$emit('update:options', $event)"
                    @update:meta="$emit('update:meta', $event)"
                >
                </FieldOptions>
            </div>
        </SettingsHeaderLine>
    </div>
</template>

<script>

import FieldOptions from '@/components/customize/fields/FieldOptions.vue';

export default {
    name: 'FieldMore',
    components: {
        FieldOptions,
    },
    mixins: [
    ],
    props: {
        meta: {
            type: [Object, null],
            required: true,
        },
        options: {
            type: Object,
            required: true,
        },
        isNew: Boolean,
        field: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:options',
        'update:meta',
    ],
    data() {
        return {
            showMore: false,
        };
    },
    computed: {
    },
    methods: {
        toggleMore() {
            this.showMore = !this.showMore;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-field-more {

}*/

</style>
