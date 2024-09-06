import CustomizeFoundation from '@/components/customize/CustomizeFoundation.vue';
import GroupNew from '@/components/customize/GroupNew.vue';
import GroupList from '@/components/customize/GroupList.vue';
import { aspectTypesList, featurePages } from '@/core/display/typenamesList.js';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    components: {
        CustomizeFoundation,
        GroupNew,
        GroupList,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
    },
    data() {
        return {
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.loading;
        },
        combined() {
            return {
                ...aspectTypesList,
                ...featurePages,
            };
        },
        symbol() {
            return this.combined[this.groupType].symbol;
        },
    },
    methods: {
    },
    watch: {
        groups: {
            handler(newGroups, oldGroups) {
                const oldLength = oldGroups?.length || 0;
                if (newGroups.length === oldLength + 1) {
                    this.$nextTick(() => {
                        this.$refs.list?.focusOnLastAdded(newGroups, oldGroups);
                    });
                }
            },
        },

    },
};
