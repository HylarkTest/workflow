<template>
    <component
        :is="link.component || 'RouterLink'"
        :to="pageLink"
        class="o-nav-link text-cm-600"
        :class="mainClasses"
        tabindex="0"
        role="button"
        aria-pressed="false"
        :title="linkName(link)"
        @click="runAction(link)"
    >
        <div class="o-nav-link__icon center relative">
            <QuarterCircle
                v-if="isSelectedLink && hasQuarterCircleStyle"
                class="o-nav-link__quarter bg-primary-600"
                point="bottom-left"
            >
            </QuarterCircle>

            <i
                class="far fa-fw z-over"
                :class="iconClasses"
            >
            </i>

            <span
                v-if="link.alertCircle && !isSelectedLink"
                class="o-nav-link__circle border-cm-100"
            >
            </span>

        </div>

        <span
            v-if="isExtended"
            class="o-nav-link__text ml-1"
            :class="{ 'text-primary-600 font-semibold': isSelectedLink }"
        >
            {{ linkName(link) }}
        </span>
    </component>
</template>

<script>

import checksNavLinks from '@/vue-mixins/checksNavLinks.js';

export default {
    name: 'NavLink',
    components: {

    },
    mixins: [
        checksNavLinks,
    ],
    props: {
        link: {
            type: Object,
            required: true,
        },
        activeStyle: {
            type: String,
            default: 'QUARTER_CIRCLE',
            validator(val) {
                return ['QUARTER_CIRCLE', 'HIGHLIGHT'].includes(val);
            },
        },
        isActive: Boolean,
        isExtended: Boolean,
        hoverClass: {
            type: String,
            default: 'hover:bg-cm-100',
        },
    },
    emits: [
        'runAction',
    ],
    data() {
        return {

        };
    },
    computed: {
        pageLink() {
            return this.getLink(this.link);
        },
        iconClasses() {
            return [
                this.iconSymbolClass,
                this.iconSelectedClass,
            ];
        },
        iconSelectedClass() {
            if (this.isSelectedLink) {
                if (this.hasQuarterCircleStyle) {
                    return 'text-cm-00';
                }
                if (this.hasHighlightStyle) {
                    return 'text-primary-600';
                }
                return '';
            }
            return '';
        },
        iconSymbolClass() {
            return this.link.icon || this.link.symbol;
        },
        isSelectedLink() {
            return this.isOnLink(this.link) || this.isActive;
        },
        hasQuarterCircleStyle() {
            return this.activeStyle === 'QUARTER_CIRCLE';
        },
        hasHighlightStyle() {
            return this.activeStyle === 'HIGHLIGHT';
        },
        mainClasses() {
            return [
                !this.isSelectedLink ? this.hoverClass : '',
                { 'o-nav-link--selected': this.isSelectedLink },
                { 'o-nav-link--extended': this.isExtended },
            ];
        },
    },
    methods: {
        linkName(link) {
            // For dynamic links that are user provided
            if (link.name) {
                return link.name;
            }
            // For system links and actions
            let path;
            if (link.langPath) {
                path = link.langPath;
            } else {
                path = `links.${_.camelCase(link.val)}`;
            }
            return this.$t(path);
        },
        runAction(link) {
            if (link.action) {
                this.$emit('runAction', link.action);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-nav-link {
    min-width: 30px;
    transition:  background-color 0.2s ease-in-out;
    width: 30px;

    @apply
        flex
        items-baseline
        relative
        rounded-lg
    ;

    @media (min-width: 500px) {
        & {
            min-width: 36px;
            width: 36px;
        }
    }

    &--extended {
        @apply
            w-full
        ;
    }

    &:hover:not(.o-nav-link--selected) {
        @apply
            text-cm-700
        ;
    }

    &__quarter {
        height: 27px;
        left: 6px;
        top: 2px;
        width: 27px;

        @apply
            absolute
        ;
    }

    &__icon {
        height: 34px;
        min-width: 34px;
        width: 34px;

        @apply
            p-1
        ;
    }

    &__circle {
        right: 4px;
        top: 5px;

        @apply
            absolute
            bg-peach-500
            border-2
            border-solid
            h-3
            rounded-full
            w-3
        ;
    }
}

</style>
