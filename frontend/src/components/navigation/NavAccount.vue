<template>
    <ButtonEl
        ref="account"
        v-blur="closeOptions"
        class="o-nav-account"
        :class="justifyClass"
        @click="toggleOptions"
        @keyup.enter="toggleOptions"
        @keyUp.space="toggleOptions"
        @mouseover="hoverOver = true"
        @mouseleave="hoverOver = false"
    >
        <ProfileNameImage
            :hideFullName="!isExtended"
            :profile="user"
            :isHoverable="true"
        >
            <template
                v-if="!isPersonalActive"
                #icon
            >
                <div
                    class="absolute -top-2 -left-2 shadow-md"
                >
                    <ProfileNameImage
                        :profile="activeBase"
                        hideFullName
                        size="xs"
                    >
                    </ProfileNameImage>
                </div>
            </template>
        </ProfileNameImage>

        <TheresMore
            v-if="isExtended"
            :hoverEffect="hoverOver"
        >
        </TheresMore>

        <PopupBasic
            v-if="optionsVisible"
            zClass="z-widget"
            :activator="$refs.account"
            v-bind="popupOptions"
        >
            <NavPopup
                :user="user"
                :links="links"
                @closePopup="closeOptions"
            >
            </NavPopup>
        </PopupBasic>
    </ButtonEl>
</template>

<script>

import NavPopup from './NavPopup.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import TheresMore from '@/components/buttons/TheresMore.vue';

import {
    isActiveBasePersonal,
} from '@/core/repositories/baseRepository.js';

export default {
    name: 'NavAccount',
    components: {
        NavPopup,
        ProfileNameImage,
        TheresMore,
    },
    mixins: [
    ],
    props: {
        isExtended: Boolean,
        user: {
            type: Object,
            required: true,
        },
        links: {
            type: Object,
            required: true,
        },
        popupOptions: {
            type: Object,
            default() {
                return {
                    nudgeDownProp: '4.375rem',
                    nudgeLeftProp: '0.625rem',
                    top: true,
                };
            },
        },
    },
    data() {
        return {
            optionsVisible: false,
            hoverOver: false,
        };
    },
    computed: {
        justifyClass() {
            return this.isExtended ? 'justify-between' : 'justify-center';
        },
        bases() {
            return this.user.allBases();
        },
        activeBase() {
            return this.user.activeBase();
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
    },
    methods: {
        toggleOptions() {
            this.optionsVisible = !this.optionsVisible;
        },
        closeOptions() {
            this.optionsVisible = false;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-nav-account {
    @apply
        flex
    ;
}

</style>
