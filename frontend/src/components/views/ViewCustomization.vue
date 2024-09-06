<template>
    <div class="o-view-customization">
        <FormWrapper
            :form="form"
        >
            <div class="inline-flex mb-4">
                <ButtonEl
                    v-if="!editNameMode"
                    class="font-semibold text-lg"
                    component="h2"
                    @click="editName"
                    @keyup.enter="editName"
                    @keyup.space="editName"
                >
                    {{ form.name }}
                </ButtonEl>

                <InputSubtle
                    v-else
                    ref="nameInput"
                    v-blur="stopEditName"
                    class="text-lg"
                    :alwaysHighlighted="true"
                    formField="name"
                    @keyup.enter="stopEditName"
                >
                </InputSubtle>
            </div>

            <div
                v-if="!view.value || view.id"
                class="mb-4"
            >
                <CheckHolder
                    v-for="option in views"
                    :key="option"
                    v-model="form.type"
                    class="my-1"
                    type="radio"
                    :val="option"
                >
                    <div>
                        {{ viewDisplay(option) }}
                    </div>
                </CheckHolder>
            </div>

            <component
                v-if="form.type"
                :is="customizationComponent"
            >
            </component>
        </FormWrapper>
    </div>
</template>

<script>

import FormWrapper from '@/components/inputs/FormWrapper.vue';
import CustomizeTile from '@/components/views/CustomizeTile.vue';
import CustomizeSpreadsheet from '@/components/views/CustomizeSpreadsheet.vue';
import CustomizeLine from '@/components/views/CustomizeLine.vue';
import CustomizeKanban from '@/components/views/CustomizeKanban.vue';

const views = [
    'LINE',
    // 'KANBAN',
    'SPREADSHEET',
    'TILE',
];

export default {
    name: 'ViewCustomization',
    components: {
        FormWrapper,
        CustomizeTile,
        CustomizeSpreadsheet,
        CustomizeLine,
        CustomizeKanban,
    },
    mixins: [
    ],
    props: {
        view: {
            type: Object,
            required: true,
        },
        allViews: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            form: this.$form({
                name: null,
                id: this.view.id || null,
                type: this.view.value || 'LINE',
            }),
            editNameMode: false,
        };
    },
    computed: {
        customizationComponent() {
            return `Customize${_.pascalCase(this.form.type)}`;
        },
        viewsWithDefaultName() {
            const names = this.allViews.filter((view) => {
                return view.name?.includes(this.$t('common.views.new'));
            });
            return names.length;
        },
        newViewNumber() {
            return this.viewsWithDefaultName + 1;
        },
        setName() {
            if (this.view.name) {
                return this.view.name;
            }
            if (this.view.value) {
                return this.$t(`common.views.${_.camelCase(this.view.value)}`);
            }
            return `${this.$t('common.views.new')} ${this.newViewNumber}`;
        },
    },
    methods: {
        editName() {
            this.editNameMode = true;
        },
        stopEditName() {
            this.editNameMode = false;
        },
        viewDisplay(option) {
            return this.$t(`common.views.${_.camelCase(option)}`);
        },
    },
    created() {
        // Due to needing values that are best in a computed property
        this.form.name = this.setName;
        this.views = views;
    },
};
</script>

<style scoped>

/*
.o-view-customization {

}
*/

</style>
