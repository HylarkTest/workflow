<template>
    <SettingsHeaderLine
        class="o-navigation-links customize__container p-6"
    >
        <template
            #header
        >
            {{ $t('customizations.navigation.links.header') }}
        </template>

        <template
            #description
        >
            {{ $t('customizations.navigation.links.description') }}
        </template>

        <!-- <button
            v-t="'common.save'"
            class="button button-primary"
            :class="{ 'unclickable': cannotSave }"
            :disabled="cannotSave"
            type="submit"
            @click="saveLinks"
        >
        </button> -->

        <div class="flex mt-8 flex-wrap gap-8">
            <div class="flex-1">
                <h4 class="header-uppercase text-cm-600 mb-2">
                    Link options (Max 8)
                </h4>

                <div class="flex min-w-0 flex-wrap gap-8">
                    <div
                        v-if="hasPages"
                        class="flex-1"
                    >
                        <h5 class="text-sm mb-2 text-primary-800 font-semibold">
                            Your pages
                        </h5>
                        <div>
                            <LinkCheckbox
                                v-for="option in allPages"
                                :key="option.id"
                                :val="option"
                                class="my-1"
                                :modelValue="form.links"
                                :link="option"
                                predicate="id"
                                :disabled="isLinkDisabled(option)"
                                @update:modelValue="updateLinks"
                            >
                            </LinkCheckbox>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <h5 class="text-sm mb-2 text-primary-800 font-semibold">
                            Features
                        </h5>
                        <div>
                            <LinkCheckbox
                                v-for="option in featurePages"
                                :key="option.val"
                                :val="option"
                                class="my-1"
                                :modelValue="form.links"
                                :link="option"
                                predicate="val"
                                :disabled="isLinkDisabled(option)"
                                @update:modelValue="updateLinks"
                            >
                            </LinkCheckbox>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-if="linksLength"
                class="w-300p"
            >
                <h4 class="header-uppercase text-cm-600 mb-2">
                    Order your links
                </h4>

                <Draggable
                    v-model="form.links"
                    itemKey="id"
                    group="links"
                >
                    <template #item="{ element }">
                        <div
                            class="o-navigation-links__item cursor-move"
                        >
                            <LinkBasic
                                :link="element"
                            >

                            </LinkBasic>
                        </div>
                    </template>
                </Draggable>
            </div>
        </div>
    </SettingsHeaderLine>
</template>

<script>

import Draggable from 'vuedraggable';
import LinkBasic from '@/components/assets/LinkBasic.vue';
import LinkCheckbox from '@/components/assets/LinkCheckbox.vue';

import { updateShortcuts } from '@/core/repositories/preferencesRepository.js';
import { featurePages } from '@/core/display/typenamesList.js';

export default {
    name: 'NavigationLinks',
    components: {
        LinkBasic,
        LinkCheckbox,
        Draggable,
    },
    mixins: [
    ],
    props: {
        allPages: {
            type: Array,
            required: true,
        },
    },
    data() {
        const user = this.$root.authenticatedUser;
        return {
            form: this.$form({
                links: this.formatShortcuts(user.baseSpecificPreferences().shortcuts || []),
            }),
        };
    },
    computed: {
        hasPages() {
            return this.allPages?.length;
        },
        featurePages() {
            return featurePages;
        },
        slotsFull() {
            return this.linksLength >= 8;
        },
        linksLength() {
            return this.form.links.length;
        },
        isOneLink() {
            return this.linksLength === 1;
        },
        firstThree() {
            return this.allPages.slice(0, 3);
        },
        firstThreeIds() {
            return _.map(this.firstThree, (page) => {
                return { id: page.id, type: 'PAGE' };
            });
        },
        defaultLinks() {
            return [
                {
                    id: 'CALENDAR',
                    type: 'FEATURE',
                },
                {
                    id: 'TODOS',
                    type: 'FEATURE',
                },
                ...this.firstThreeIds,
            ];
        },
        formatted() {
            return this.formatShortcuts(this.defaultLinks);
        },
        shortcuts() {
            return this.$root.authenticatedUser.baseSpecificPreferences().shortcuts;
        },
        noChange() {
            const values = this.shortcuts?.length ? this.value : this.defaultLinks;
            return _.isEqual(this.form.links, this.formatShortcuts(values));
        },
        cannotSave() {
            return this.noChange || !this.linksLength;
        },
    },
    methods: {
        updateLinks(links) {
            this.form.links = links;
        },
        isLinkSelected(link) {
            const val = link.val ? 'val' : 'id';
            return _.find(this.form.links, { [val]: link[val] });
        },
        async saveLinks() {
            await updateShortcuts(this.form.links.map((link) => {
                if (link.id) {
                    return { id: link.id, type: 'PAGE' };
                }
                return { id: link.val, type: 'FEATURE' };
            }));
            this.$debouncedSaveFeedback();
        },
        formatShortcuts(shortcutItems) {
            return _(shortcutItems).map((shortcut) => {
                if (shortcut.type === 'PAGE') {
                    return _.find(this.allPages, ['id', shortcut.id]);
                }
                return _.find(featurePages, ['val', shortcut.id]);
            }).filter().value();
        },
        isLinkDisabled(option) {
            const isSelected = this.isLinkSelected(option);
            return (this.slotsFull && !isSelected)
                || (this.isOneLink && isSelected);
        },
    },
    watch: {
        'form.links': {
            handler(newVal, oldVal) {
                if (oldVal.length) {
                    this.saveLinks();
                }
            },
        },
    },
    created() {
        if (!this.linksLength) {
            this.form.links = this.formatShortcuts(this.defaultLinks);
        }
    },
};
</script>

<style scoped>

.o-navigation-links {
    &__item {
        @apply
            bg-cm-00
            border
            border-dashed
            border-primary-400
            my-2
            px-4
            py-2
            rounded-xl
        ;
    }
}

</style>
