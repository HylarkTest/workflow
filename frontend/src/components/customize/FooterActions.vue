<template>
    <SettingsHeaderLine
        class="o-footer-actions customize__container p-6"
    >
        <template
            #header
        >
            {{ $t('customizations.footer.actions.header') }}
        </template>

        <template
            #description
        >
            {{ $t('customizations.footer.actions.description') }}
        </template>

        <div>
            <div
                class="mb-4"
            >
                <ToggleHolder
                    v-model="noneToggle"
                >
                    <span
                        class="text-smbase text-cm-700 font-semibold"
                    >
                        Show widget
                    </span>
                </ToggleHolder>
            </div>
            <div>
                <CheckHolder
                    v-for="option in featuresArr"
                    :key="option.val"
                    :modelValue="shortcutVal(option.val)"
                    holderClasses="my-2"
                    type="radio"
                    :val="option.val"
                    :canRadioClear="true"
                    @update:modelValue="setShortcut(option.val)"
                >
                    <div
                        class="text-smbase text-cm-700"
                    >
                        <i
                            class="far fa-fw mr-1 text-primary-500"
                            :class="option.symbol"
                        >
                        </i>

                        {{ $t(option.labelPath) }}
                    </div>
                </CheckHolder>
            </div>
        </div>
    </SettingsHeaderLine>
</template>

<script>

import { arrRemove } from '@/core/utils.js';
import { featureTypes } from '@/core/display/typenamesList.js';
import { updateWidgets } from '@/core/repositories/preferencesRepository.js';

const addOptions = [
    'NOTES',
    'TODOS',
    'EVENTS',
    'LINKS',
    'PINBOARD',
    'DOCUMENTS',
];

export default {
    name: 'FooterActions',
    components: {

    },
    mixins: [
    ],
    props: {

    },
    data() {
        const user = this.$root.authenticatedUser;
        const widgets = user.baseSpecificPreferences().widgets || {};
        return {
            footerForm: this.$apolloForm(() => {
                return {
                    addShortcuts: [...(widgets.addShortcuts || [])],
                };
            }),
        };
    },
    computed: {
        featuresArr() {
            return this.addOptions.map((option) => {
                const item = _.find(featureTypes, { val: option });
                const labelPath = `features.${_.camelCase(option)}.pluralName`;
                return {
                    labelPath,
                    symbol: item.symbol,
                    val: option,
                };
            });
        },
        noneToggle: {
            get() {
                return !!this.footerForm.addShortcuts.length;
            },
            set(val) {
                if (!val) {
                    this.footerForm.addShortcuts = [];
                } else {
                    this.footerForm.addShortcuts = ['NOTES'];
                }
            },
        },
    },
    methods: {
        shortcutVal(val) {
            if (this.hasShortcut(val)) {
                return val;
            }
            return [];
        },
        hasShortcut(val) {
            return this.footerForm.addShortcuts.includes(val);
        },
        setShortcut(val) {
            // Right now just one, but may expand depending
            // on demand and feasibility
            const valExists = this.hasShortcut(val);

            if (valExists) {
                this.footerForm.addShortcuts = arrRemove(this.footerForm.addShortcuts, val);
            } else {
                this.footerForm.addShortcuts = [val];
            }
        },
        async saveWidgets() {
            await updateWidgets(this.footerForm);
            this.$debouncedSaveFeedback();
        },
    },
    watch: {
        'footerForm.addShortcuts': {
            handler(newVal, oldVal) {
                if (!_.isEqual(newVal, oldVal)) {
                    this.saveWidgets();
                }
            },
        },
    },
    created() {
        this.addOptions = addOptions;
    },
};
</script>

<style scoped>

/*.o-footer-actions {

} */

</style>
