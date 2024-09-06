<template>
    <Modal
        class="o-home-customize"
        containerClass="w-3/4"
        :containerStyle="{ height: '80vh' }"
        v-bind="$attrs"
        :header="header"
        @closeModal="$emit('closeModal')"
    >
        <EditFoundation
            :tabs="tabs"
            :hideHeader="true"
            :selectedTab="selectedTab"
            :selectedTabHeader="selectedTabHeader"
            tabComponent="IconVertical"
            tabClasses="p-0 rounded-b-xl"
            @selectTab="selectTab"
        >
            <component
                :is="selectedComponent"
                :activeBase="activeBase"
                :links="links"
            >
            </component>
        </EditFoundation>
    </Modal>
</template>

<script>

import EditFoundation from '@/components/customize/EditFoundation.vue';
import HomeEditDefaults from '@/components/home/HomeEditDefaults.vue';
import HomeEditPages from '@/components/home/HomeEditPages.vue';
import HomeEditWidgets from '@/components/home/HomeEditWidgets.vue';

import interactsWithAuthenticatedUser from '@/vue-mixins/interactsWithAuthenticatedUser.js';
import setsTabSelection from '@/vue-mixins/setsTabSelection.js';

import { isActiveBasePersonal, activeBase } from '@/core/repositories/baseRepository.js';

import LINKS from '@/graphql/Links.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

const myWidgetsTab = {
    name: 'My home widgets',
    icon: 'fal fa-circle-calendar',
    subtitle: 'Customize what widgets you see on your home page',
    value: 'WIDGETS',
};

const myDataTab = {
    name: 'My home data',
    icon: 'fal fa-house',
    subtitle: 'Customize what pages show up on your home page',
    value: 'PAGES',
};

const everyoneDataTab = {
    name: 'Base defaults',
    icon: 'fal fa-grid-horizontal',
    subtitle: 'Customize what data shows by default for everyone',
    value: 'DEFAULTS',
};

export default {
    name: 'HomeCustomize',
    components: {
        EditFoundation,
        HomeEditDefaults,
        HomeEditPages,
        HomeEditWidgets,
    },
    mixins: [
        interactsWithAuthenticatedUser,
        setsTabSelection,
    ],
    props: {
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        links: {
            query: LINKS,
            update: initializeConnections,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
            selectedTab: null,
            componentKey: 'HOME_EDIT',
        };
    },
    computed: {
        baseName() {
            return this.activeBase.name;
        },
        header() {
            let pathKey;
            if (this.isPersonalActive) {
                pathKey = 'personal';
            } else {
                pathKey = this.isBaseOwnerOrAdmin ? 'general' : 'self';
            }
            return this.$t(`home.customize.headers.${pathKey}`, { baseName: this.baseName });
        },
        selectedTabHeader() {
            return `home.customize.tabs.${_.camelCase(this.selectedTab)}.name`;
        },
        activeBase() {
            return activeBase();
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        isBaseOwnerOrAdmin() {
            return this.authenticatedUser.isOwnerOrAdmin();
        },
        spaces() {
            return this.links?.spaces || [];
        },
        spacesWithPagesLength() {
            return this.spacesWithPages?.length;
        },
        spacesWithPages() {
            return this.spaces.filter((space) => {
                return space.pages?.length;
            });
        },
        showFullBaseCustomizations() {
            return !this.isPersonalActive
                && this.isBaseOwnerOrAdmin;
        },
        tabs() {
            const personal = [myWidgetsTab, myDataTab];

            if (this.showFullBaseCustomizations) {
                return [...personal, everyoneDataTab];
            }
            return personal;
        },
    },
    methods: {
        saveBaseForm() {
            // TODO: Once the request is done
        },
    },

    created() {
        this.selectedTab = this.tabs[0].value;
    },
};
</script>

<style scoped>

/*.o-home-customize {

} */

</style>
