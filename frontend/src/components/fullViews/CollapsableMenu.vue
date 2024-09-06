<template>
    <div class="c-collapsable-menu relative">
        <div
            v-show="showSide"
            class="c-collapsable-menu__side"
            :class="{ 'c-collapsable-menu__side--responsive': forceResponsiveDisplay }"
        >
            <slot
                name="menu"
                :forceResponsiveDisplay="forceResponsiveDisplay"
            >
            </slot>
        </div>

        <div
            v-show="showContent"
            class="c-collapsable-menu__content"
        >
            <slot
                name="content"
                :forceResponsiveDisplay="forceResponsiveDisplay"
            >
            </slot>
        </div>

        <ButtonEl
            v-show="showButton"
            class="c-collapsable-menu__button absolute button--xs button-primary"
            @click="$emit('showSide')"
        >
            <i class="fa-solid fa-arrow-left mr-1">
            </i>
            <span>
                {{ $t('common.menu') }}
            </span>
        </ButtonEl>
    </div>
</template>

<script>
import listensToScrollandResizeEvents from '@/vue-mixins/listensToScrollAndResizeEvents.js';

export default {
    name: 'CollapsableMenu',
    components: {
    },
    mixins: [
        listensToScrollandResizeEvents,
    ],
    props: {
        contentOnly: Boolean,
        forceResponsiveDisplay: Boolean,
        isSideVisible: Boolean,
    },
    emits: [
        'showSide',
    ],
    data() {
        return {
        };
    },
    computed: {
        deactivateSide() {
            return this.contentOnly || (this.forceResponsiveDisplay && !this.isSideVisible);
        },
        showSide() {
            return !this.deactivateSide;
        },
        showContent() {
            return !this.forceResponsiveDisplay || this.deactivateSide;
        },
        showButton() {
            return !this.contentOnly && this.deactivateSide;
        },
    },
};
</script>

<style scoped>
.c-collapsable-menu {
    @apply
        flex
        h-full
        w-full
    ;

    &__side {
        @apply
            block
            mr-2
            overflow-y-auto
            w-52
        ;

        &--responsive {
            @apply
                mr-0
                w-full
            ;
        }
    }

    &__content {
        @apply
            flex
            flex-1
            flex-col
            mb-4
            overflow-y-auto
        ;
    }

    &__button {
        @apply
            -left-2
            -top-2
            z-alert
        ;
    }
}
</style>
