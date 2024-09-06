import FavoriteButton from '@/components/buttons/FavoriteButton.vue';
import EditableMarkerSet from '@/components/markers/EditableMarkerSet.vue';
import ExtrasButton from '@/components/buttons/ExtrasButton.vue';

import handlesFeatureItemSelection from '@/vue-mixins/features/handlesFeatureItemSelection.js';
import interactsWithAssigneesPicker from '@/vue-mixins/features/interactsWithAssigneesPicker.js';

export default {
    components: {
        FavoriteButton,
        EditableMarkerSet,
        ExtrasButton,
    },
    mixins: [
        handlesFeatureItemSelection,
        interactsWithAssigneesPicker,
    ],
    props: {
        actionProcessing: Boolean,
        deleteProcessing: Boolean,
    },
    emits: [
        'update:processing',
    ],
    data() {
        return {
        };
    },
    computed: {
        // Basic info
        name() {
            return this.featureItem.name;
        },
        isFavorite() {
            return this.featureItem.isFavorite;
        },
        description() {
            return this.featureItem.description;
        },
        trimmedDescription() {
            return _.truncate(this.description, { length: 100 });
        },

        // Marker info
        markerGroups() {
            return this.featureItem.markerGroups || [];
        },
        markersLength() {
            return this.markerGroups?.length;
        },
        grouped() {
            return _(this.markerGroups).groupBy((group) => {
                return group.group.type;
            }).value();
        },
        tags() {
            return this.grouped.TAG;
        },
        statuses() {
            return this.grouped.STATUS;
        },
        pipelines() {
            return this.grouped.PIPELINE;
        },

        // Association info
        associationsLength() {
            return this.associations.length;
        },
        associations() {
            return this.featureItem.associations || [];
        },
        firstAssociation() {
            return this.associations[0];
        },
        remainingAssociations() {
            return this.associations.slice(1);
        },
        remainingAssociationsLength() {
            return this.remainingAssociations.length;
        },

        // For interactsWithAssigneesPicker mixin
        assigneeGroupsObject() {
            return this.featureItem;
        },

        // Classes
        actionProcessingClass() {
            return { unclickable: this.actionProcessing };
        },
        deleteProcessingClass() {
            return { unclickable: this.deleteProcessing };
        },
        mainFeatureItemClasses() {
            return [
                this.actionProcessingClass,
                this.deleteProcessingClass,
                this.highlightedClass,
            ];
        },
        // additionalBgClass() {
        //     return this.bgClass === 'bg-cm-00' ? 'bg-cm-100' : 'bg-cm-00';
        // },
    },
    methods: {
        selectOption(option) {
            if (option === 'DELETE') {
                this.deleteItem();
            }
        },
        updateProcessing(processingType, state) {
            this.$emit('update:processing', { processingType, state });
        },
        async deleteItem() {
            // May replace in component
            this.updateProcessing('delete', true);
            try {
                await this.deleteFunction(this.featureItem);
            } catch (error) {
                this.updateProcessing('delete', false);
                throw error;
            }
        },
        // toggleDescription() {
        //     return this.showDescription = !this.showDescription;
        // },
    },
};
