<template>
    <Modal
        class="c-shortcuts-modal"
        containerClass="p-4 w-96"
        :header="true"
        v-bind="$attrs"
        @closeModal="$emit('closeModal')"
    >
        <template
            #header
        >
            Action shortcuts

        </template>

        <div>
            <h3 class="text-cm-600 font-medium">
                What do you want to do?
            </h3>

            <div>
                <ButtonEl
                    v-for="prompt in filteredPrompts"
                    :key="prompt.val"
                    class="c-shortcuts-modal__button my-2"
                    @click="openShortcut(prompt)"
                >
                    <i
                        class="fal mr-3"
                        :class="prompt.icon"
                    >
                    </i>
                    <p class="text-sm text-cm-800">
                        {{ promptText(prompt) }}
                    </p>
                </ButtonEl>
            </div>
        </div>
    </Modal>
</template>

<script>

const prompts = [
    {
        val: 'EDIT_FIELDS',
        icon: 'fa-sitemap',
        nameKey: 'blueprintName',
        typeCondition: ['ENTITIES', 'ENTITY'],
    },
    {
        val: 'UPDATE_DESIGN',
        icon: 'fa-table-columns',
        nameKey: 'pageName',
        typeCondition: ['ENTITIES'],
    },
    {
        val: 'UPDATE_DISPLAY',
        icon: 'fa-browser',
        nameKey: 'pageName',
        typeCondition: ['ENTITY'],
    },
    {
        val: 'EDIT_RELATIONSHIPS',
        icon: 'fa-draw-circle',
        nameKey: 'blueprintName',
        typeCondition: ['ENTITIES', 'ENTITY'],
    },
];

export default {
    name: 'ShortcutsModal',
    components: {

    },
    mixins: [
    ],
    props: {
        page: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
        'goToShortcut',
    ],
    data() {
        return {

        };
    },
    computed: {
        blueprint() {
            return this.page?.mapping;
        },
        filteredPrompts() {
            return prompts.filter((prompt) => {
                return prompt.typeCondition.includes(this.page?.type);
            });
        },
    },
    methods: {
        promptText(prompt) {
            return this.$t(`shortcuts.${this.camelPromptVal(prompt)}`,
                { [prompt.nameKey]: this.getDataName(prompt) }
            );
        },
        camelPromptVal(prompt) {
            return _.camelCase(prompt.val);
        },
        getDataName(prompt) {
            if (prompt.nameKey === 'pageName') {
                return this.page?.name;
            }
            if (prompt.nameKey === 'blueprintName') {
                return this.blueprint?.name;
            }
            return '';
        },
        openShortcut(prompt) {
            const pascalVal = _.pascalCase(prompt.val);
            return this[`go${pascalVal}`]();
        },
        goEditRelationships() {
            this.$emit('goToShortcut', { tab: 'RELATIONSHIPS', selectedView: 'MAPPING' });
        },
        goEditFields() {
            this.$emit('goToShortcut', { tab: 'FIELDS', selectedView: 'MAPPING' });
        },
        goUpdateDesign() {
            this.$emit('goToShortcut', { tab: 'VIEWS', selectedView: 'PAGE' });
        },
        goUpdateDisplay() {
            this.$emit('goToShortcut', { tab: 'DISPLAY', selectedView: 'PAGE' });
        },
    },
    created() {
    },
};
</script>

<style scoped>

.c-shortcuts-modal {
    &__button {
        transition: 0.2s ease-in-out;

        @apply
            bg-primary-100
            border
            border-primary-500
            border-solid
            flex
            font-semibold
            items-baseline
            px-4
            py-3
            rounded-md
            text-primary-600
        ;

        &:hover {
            @apply
                border-primary-400
                shadow-md
                text-primary-500
            ;
        }
    }
}

</style>
