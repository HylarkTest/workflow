<template>
    <div class="o-nav-base">
        <component
            :is="base.pivot?.isActive ? 'div' : 'router-link'"
            :to="{ name: 'home', params: { baseId: base.id } }"
            class="relative"
            @click="selectBase"
        >
            <ProfileNameImage
                class="mb-2"
                :profile="baseData"
                imageFallbackClassesProp="z-over"
                size="sm"
            >
                <template
                    #icon
                >
                    <div
                        v-if="base.pivot?.isActive"
                        class="o-nav-base__highlight"
                        :class="getBorderColor(base)"
                    >
                    </div>
                </template>
            </ProfileNameImage>
        </component>

        <div class="flex flex-col items-start">
            <component
                v-for="link in baseOptions"
                :key="link.val"
                :is="link.component || 'router-link'"
                :to="{
                    name: link.link,
                    state: { previousLinkPath: $route.path },
                    params: link.scoped ? { baseId: base.id } : {},
                }"
                :class="linkClass(link)"
                @click="selectLink(link)"
            >
                <i
                    v-if="link.icon"
                    class="o-nav-base__icon fa-light mr-2"
                    :class="link.icon"
                >
                </i>

                <span
                    v-t="linkName(link)"
                >
                </span>
            </component>
        </div>
    </div>
</template>

<script>

import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';

const history = {
    val: 'HISTORY',
    link: 'history',
    icon: 'fa-list-timeline',
    scoped: true,
};

const customize = {
    val: 'CUSTOMIZE',
    link: 'customizePage',
    icon: 'fa-sliders',
    scoped: true,
};

const data = {
    val: 'DATA',
    link: 'dataManagement',
    icon: 'fa-database',
    scoped: true,
};

const settings = (settingsLink) => ({
    val: 'SETTINGS',
    link: settingsLink,
    icon: 'fa-cog',
    scoped: true,
});

const personalOptions = (settingsLink) => ([
    {
        val: 'ACCOUNT',
        link: 'settings.account',
        icon: 'fa-user-circle',
    },
    settings(settingsLink),
    history,
    customize,
    data,
    {
        val: 'SIGN_OUT',
        action: 'signOut',
        langPath: 'common.signOut',
        component: 'ButtonEl',
        display: 'BUTTON',
    },
]);

const collaborativeOptions = (settingsLink) => ([
    settings(settingsLink),
    history,
    customize,
    data,
]);

export default {
    name: 'NavBase',
    components: {
        ProfileNameImage,
    },
    mixins: [
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'selectLink',
        'closePopup',
    ],
    data() {
        return {

        };
    },
    computed: {
        isPersonal() {
            return this.baseType === 'PERSONAL';
        },
        baseData() {
            return this.isPersonal ? this.user : this.base;
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        baseOptions() {
            return this[`${this.baseTypeFormatted}Options`](this.settingsLink);
        },
        settingsLink() {
            if (this.isPersonal || this.isOwnerOrAdmin) {
                return 'settings.general';
            }
            return 'settings.profile';
        },
        isAdmin() {
            return this.base.pivot.role === 'ADMIN';
        },
        isOwner() {
            return this.base.pivot.role === 'OWNER';
        },
        isOwnerOrAdmin() {
            return this.isOwner || this.isAdmin;
        },
    },
    methods: {
        linkName(link) {
            return link.langPath || `links.${_.camelCase(link.val)}`;
        },
        selectLink(link) {
            this.$emit('selectLink', link);
        },
        linkClass(link) {
            return link.display === 'BUTTON'
                ? 'button--sm button-secondary--light mt-1 text-center'
                : 'o-nav-base__line';
        },
        getBorderColor(base) {
            const isPersonal = base.baseType === 'PERSONAL';
            const color = isPersonal ? 'gold' : 'azure';
            return `border-${color}-400`;
        },
        selectBase() {
            if (!this.base.pivot?.isActive) {
                this.$emit('closePopup');
            }
        },
    },
    created() {
        this.personalOptions = personalOptions;
        this.collaborativeOptions = collaborativeOptions;
    },
};
</script>

<style scoped>

.o-nav-base {
    &__line {
        transition: 0.2s ease-in-out;

        @apply
            flex
            items-center
            py-1.5
            text-cm-600
            text-sm
        ;

        &:hover {
            @apply
                text-cm-800
            ;

            .o-nav-base__icon {
                @apply
                    text-cm-600
                ;
            }
        }
    }

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
            z-0
        ;
    }

    &__icon {
        @apply
            text-cm-400
        ;
    }
}

</style>
