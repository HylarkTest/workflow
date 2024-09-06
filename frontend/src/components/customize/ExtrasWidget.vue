<template>
    <SettingsHeaderLine class="o-extras-widget">
        <template
            #header
        >
            Quick-add widget
        </template>

        <template
            #description
        >
            Select which quick-add feature you want in the bottom right corner, or hide the widget.
        </template>

        <button
            v-t="'common.save'"
            class="button button-primary mb-8"
            :class="{ unclickable: cannotSave }"
            :disabled="cannotSave"
            type="submit"
            @click="saveWidget"
        >
        </button>

        <CheckHolder
            v-for="option in options"
            :key="option.id"
            v-model="widget"
            :val="option.id"
            type="radio"
            class="mb-2 items-center"
        >
            <div class="flex items-center">
                <LayoutWidget
                    v-if="option.id !== 'hide'"
                    class="mr-3"
                    :widgetType="option.id"
                    :displayOnly="true"
                    positionClasses="relative"
                    size="xs"
                >
                </LayoutWidget>
                <span class="font-medium text-cm-600">
                    {{ option.name }}
                </span>
            </div>

        </CheckHolder>

    </SettingsHeaderLine>
</template>

<script>

import LayoutWidget from '@/components/layout/LayoutWidget.vue';

const options = [
    {
        id: 'note',
        name: 'Add note',
    },
    {
        id: 'event',
        name: 'Add event',
    },
    {
        id: 'todo',
        name: 'Add todo',
    },
    {
        id: 'hide',
        name: 'Hide',
    },

];

export default {
    name: 'ExtrasWidget',
    components: {
        LayoutWidget,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {
            form: this.$form({
                widgets: ['note'],
            }),
        };
    },
    computed: {
        widget: {
            get() {
                return this.form.widgets ? this.form.widgets[0] : 'hide';
            },
            set(val) {
                if (val === 'hide') {
                    this.form.widgets = null;
                }
                this.form.widgets = [val];
            },
        },
        cannotSave() {
            return false;
        },
    },
    methods: {
        saveWidget() {
            this.$saveFeedback();
        },
    },
    created() {
        this.options = options;
    },
};
</script>

<style scoped>

/*.o-extras-widget {

} */

</style>
