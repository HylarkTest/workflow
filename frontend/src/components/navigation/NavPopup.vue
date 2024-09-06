<template>
    <div class="o-nav-popup">
        <CloseButton
            class="o-nav-popup__close"
            @click="closePopup"
        >
        </CloseButton>

        <div class="flex flex-1 min-h-0">
            <div
                class="o-nav-popup__side"
            >
                <component
                    v-for="base in allDisplayedBases"
                    :key="base.id"
                    :is="base.pivot?.isActive ? 'div' : 'router-link'"
                    class="mb-2.5 relative"
                    :to="{ name: 'home', params: { baseId: base.id } }"
                    @click="selectBase(base)"
                >
                    <div
                        v-if="base.pivot?.isActive"
                        class="o-nav-popup__highlight shadow-azure-400"
                        :class="getBorderColor(base)"
                    >
                    </div>

                    <ProfileNameImage
                        class="relative z-over"
                        :profile="getBaseDisplay(base)"
                        hideFullName
                        isHoverable
                    >
                    </ProfileNameImage>
                </component>

                <RouterLink
                    v-if="allowNewBase"
                    :to="{ name: 'newBase' }"
                    class="o-nav-popup__add centered hover:shadow-lg transition-2eio"
                    title="Create a new collaborative base"
                >
                    <i
                        class="fa-light fa-plus text-cm-500 text-sm"
                    >
                    </i>
                </RouterLink>
            </div>

            <div class="p-5 flex gap-6 min-w-0 flex-wrap overflow-y-auto">

                <NavBase
                    v-for="base in displayedBases"
                    :key="base.id"
                    class="o-nav-popup__base"
                    :base="base"
                    :user="user"
                    @selectLink="selectLink"
                    @closePopup="closePopup"
                >
                </NavBase>
            </div>
        </div>

        <div class="py-2 px-4 bg-cm-100">
            <div class="flex justify-between items-center">
                <p
                    v-t="'settings.colorMode.colorMode'"
                    class="o-nav-popup__label text-cm-600 mr-8"
                >
                </p>

                <ColorModeToggle
                    :modelValue="colorMode"
                    size="sm"
                    @update:modelValue="updateColorMode"
                >
                </ColorModeToggle>
            </div>
        </div>
    </div>
</template>

<script>

import NavBase from './NavBase.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import CloseButton from '@/components/buttons/CloseButton.vue';
import ColorModeToggle from '@/components/buttons/ColorModeToggle.vue';

import { colorMode, updateColorMode } from '@/core/repositories/preferencesRepository.js';
import { logout } from '@/core/auth.js';
import {
    switchToBase,
    isActiveBasePersonal,
} from '@/core/repositories/baseRepository.js';
import { maxBases } from '@/core/data/bases.js';

const allSpaces = {
    langPath: 'common.all',
    value: null,
};

export default {
    name: 'NavPopup',
    components: {
        ProfileNameImage,
        CloseButton,
        ColorModeToggle,
        NavBase,
    },
    mixins: [
    ],
    props: {
        user: {
            type: Object,
            required: true,
        },
        links: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'closePopup',
    ],
    data() {
        return {
            spaceFilter: null,
            colorMode: 'LIGHT',
        };
    },
    computed: {
        spaces() {
            return this.links.spaces;
        },
        bases() {
            return this.user.allBases();
        },
        personalBase() {
            return this.user.personalBase();
        },
        collaborativeBases() {
            return this.bases.filter((base) => base.baseType === 'COLLABORATIVE');
        },
        activeBase() {
            return this.user.activeBase();
        },
        allDisplayedBases() {
            const bases = [this.personalBase];
            if (this.hasMultipleBases) {
                bases.push(...this.collaborativeBases);
            }
            return bases;
        },
        basesLength() {
            return this.bases.length;
        },
        hasMultipleBases() {
            return this.basesLength > 1;
        },
        firstCollabBase() {
            if (!this.hasMultipleBases) {
                return null;
            }
            if (!this.isPersonalActive) {
                return this.activeBase;
            }
            return this.collaborativeBases[0];
        },
        displayedBases() {
            const bases = [this.personalBase];
            if (this.hasMultipleBases) {
                bases.push(this.firstCollabBase);
            }
            return bases;
        },
        spaceOptions() {
            return this.spaces.map((space) => {
                return {
                    name: space.name,
                    value: space.id,
                };
            });
        },
        spaceFilterOptions() {
            return [
                allSpaces,
                ...this.spaceOptions,
            ];
        },
        allowNewBase() {
            return this.basesLength < maxBases;
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
    },
    methods: {
        selectBase(base) {
            if (this.isActiveBase(base) && this.isADisplayedBase(base)) {
                this.closePopup();
            }
        },
        isActiveBase(base) {
            return !base.pivot?.isActive;
        },
        isADisplayedBase(base) {
            return this.displayedBases.find((displayedBase) => {
                return displayedBase.id === base.id;
            });
        },
        closePopup() {
            this.$emit('closePopup');
        },
        updateColorMode(mode) {
            updateColorMode(mode);
        },
        signOut() {
            this.$root.showSignoutLoader = true;
            logout();
        },
        runAction(action) {
            if (_.isFunction(action)) {
                action();
            } else {
                this[action]();
            }
        },
        selectLink(link) {
            if (link.action) {
                this.runAction(link.action);
            }
            this.closePopup();
        },
        changeBase(base) {
            switchToBase(base);
            this.closePopup();
        },
        getBaseDisplay(base) {
            if (base.baseType === 'PERSONAL') {
                return this.user;
            }
            return base;
        },
        getBorderColor(base) {
            const isPersonal = base.baseType === 'PERSONAL';
            const color = isPersonal ? 'gold' : 'azure';
            return `border-${color}-400`;
        },
    },
    created() {
        this.spacesDisplay = (option) => option.name || this.$t(option.langPath);
        this.colorMode = colorMode;
    },
};
</script>

<style scoped>

.o-nav-popup {
    max-height: 350px;
    max-width: 350px;

    @apply
        flex
        flex-col
        relative
    ;

    &__close {
        @apply
            absolute
            right-1
            top-1
        ;
    }

    &__side {
        @apply
            border-cm-200
            border-r
            border-solid
            flex
            flex-col
            items-center
            overflow-y-auto
            px-2.5
            py-4
            shrink-0
        ;
    }

    /*&__item {
        transition: 0.1s ease-in-out;

        @apply
            text-sm
        ;
    }*/

    &__label {
        @apply
            font-semibold
            text-sm
        ;
    }

    /*&__highlight {
        @apply
            absolute
            border-2
            border-solid
            h-full
            rounded-lg
            shadow-lg
            w-full
            z-over
        ;
    }*/

    &__highlight {
        height: calc(100% + 6px);
        width: calc(100% + 6px);

        @apply
            absolute
            bg-cm-00
            border-2
            border-solid
            -left-[3px]
            rounded-lg
            shadow-lg
            -top-[3px]
            z-over
        ;
    }

    &__add {
        @apply
            bg-cm-100
            border
            border-cm-300
            border-dashed
            h-8
            min-h-[2rem]
            rounded-lg
            w-8
        ;
    }

    &__base {
        min-width: 100px;
        @apply
            flex-1
        ;
    }
}

</style>
