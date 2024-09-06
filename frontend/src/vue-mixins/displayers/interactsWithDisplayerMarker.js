import { removeMarker, setMarker } from '@/core/repositories/itemRepository.js';

export default {
    mixins: [
    ],
    props: {
        item: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
        isModifiable: Boolean,
        showInSelected: Boolean,
        alwaysShowPrompt: Boolean,
        showAllMarkers: Boolean,
    },
    data() {
        return {
            processing: false,
        };
    },
    computed: {
        markerGroup() {
            return this.dataInfo.info?.group;
        },
        isMarkersArr() {
            return _.isArray(this.dataValue);
        },
    },
    methods: {
        editMarker(marker) {
            const hasMarker = this.isMarkersArr
                ? _.find(this.dataValue, { id: marker.id })
                : this.dataValue?.id === marker.id;
            if (hasMarker) {
                this.removeMarker(marker);
            } else {
                this.setMarker(marker);
            }
        },
        async setMarker(marker) {
            this.processing = true;
            await setMarker(this.item, marker, this.dataInfo, this.mapping);
            this.processing = false;
        },
        async removeMarker(marker) {
            this.processing = true;
            await removeMarker(this.item, marker, this.dataInfo, this.mapping);
            this.processing = false;
        },
    },
};
