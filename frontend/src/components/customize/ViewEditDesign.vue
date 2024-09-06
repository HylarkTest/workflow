<template>
    <div
        class="o-view-edit-design"
        :class="containerClass"
    >
        <div
            v-for="template in templateOptions"
            :key="template"
            class="p-2"
            :class="boxClass"
        >
            <ButtonEl
                class="o-view-edit-design__item hover:shadow-lg centered"
                :class="selectedClass(template)"
                @click="selectTemplate(template)"
            >
                <i
                    v-if="isSelected(template)"
                    class="o-view-edit-design__icon fas fa-circle-check"
                >
                </i>

                <div class="w-full">
                    <component
                        :is="template"
                        :blank="true"
                    >
                    </component>
                </div>
            </ButtonEl>
        </div>
    </div>
</template>

<script>

import { getTemplates } from '@/core/display/cardInstructions.js';
import { updatePageView } from '@/core/repositories/pageRepository.js';

export default {
    name: 'ViewEditDesign',
    components: {

    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        view: {
            type: Object,
            required: true,
        },
        viewName: {
            type: String,
            required: true,
        },
    },
    data() {
        const templateOptions = getTemplates(this.view.viewType);
        return {
            templateOptions,
            form: this.$apolloForm({
                ...this.view,
                template: this.view.template || templateOptions[0],
            }, {
                reportValidation: true,
            }),
        };
    },
    computed: {
        viewVal() {
            return this.view.viewType;
        },
        containerClass() {
            return `o-view-edit-design--${this.viewVal}`;
        },
        boxClass() {
            return `o-view-edit-design__box--${this.viewVal}`;
        },
    },
    methods: {
        isSelected(template) {
            return template === this.form.template;
        },
        selectTemplate(template) {
            if (!this.isSelected(template)) {
                this.form.template = template;
            }
        },
        selectedClass(template) {
            return this.isSelected(template)
                ? 'border-primary-600 shadow-lg shadow-primary-600/20'
                : 'border-cm-100';
        },
        async saveView() {
            try {
                await updatePageView(this.form, this.page);
                this.$saveFeedback();
            } catch {
                this.$errorFeedback();
            }
        },
    },
    watch: {
        'form.template': function onTemplateUpdate() {
            this.saveView();
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-view-edit-design {
    &--KANBAN {
        @apply
            flex
            flex-wrap
            -m-2
        ;
    }

    &--TILE {
        @apply
            flex
            flex-wrap
            -m-2
        ;
    }

    &--LINE {
        @apply
            -m-2
        ;
    }

    &__box {
        &--KANBAN {
            max-width: 320px;
            min-width: 280px;
        }

        &--TILE {
            max-width: 360px;
            min-width: 300px;
        }

        /*&--LINE {
            @apply
            ;
        }*/
    }

    &__item {
        transition: 0.2s ease-in-out;

        @apply
            bg-cm-100
            border
            border-solid
            h-full
            p-4
            relative
            rounded-xl
        ;
    }
    &__icon {
        @apply
            absolute
            bg-cm-00
            -right-3
            rounded-full
            text-2xl
            text-primary-600
            -top-3
        ;
    }
}

</style>
