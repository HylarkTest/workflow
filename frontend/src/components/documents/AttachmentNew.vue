<template>
    <div class="o-attachment-new">
        <div
            class="flex justify-end w-full"
        >
            <AttachmentButton
                ref="attachButton"
                @addFile="addFile"
            >
                <button
                    class="button button-rounded button-secondary"
                    :class="{ unclickable: processing }"
                    type="button"
                    :disabled="processing"
                    @click="attachFile"
                >
                    <i class="fa-solid fa-plus mr-1">
                    </i>

                    {{ $t('features.documents.itemName') }}

                </button>
            </AttachmentButton>
        </div>
    </div>
</template>

<script>

import assistsComponentAroundAttachmentButton from '@/vue-mixins/common/assistsComponentAroundAttachmentButton.js';

export default {
    name: 'AttachmentNew',
    components: {
    },
    mixins: [
        assistsComponentAroundAttachmentButton,
    ],
    props: {
        processing: Boolean,
    },
    emits: [
        'addFile',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-attachment-new {

} */

</style>
