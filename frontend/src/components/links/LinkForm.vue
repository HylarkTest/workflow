<template>
    <FeatureFormBase
        v-model:form="form"
        v-model:formAssociations="form.associations"
        v-model:formMarkers="form.markers"
        v-model:formListId="form.linkListId"
        v-model:formAssigneeGroups="form.assigneeGroups"
        class="o-link-form"
        v-bind="baseProps"
        :changeListFunction="changeLinkList"
        @saveItem="saveItem(true)"
        @deleteItem="deleteItem"
    >
        <div class="mb-2">
            <label class="header-form">
                {{ $t('labels.url') }}*
            </label>

            <InputBox
                bgColor="gray"
                formField="url"
                :placeholder="$t('features.links.form.placeholders.url')"
                :maxLength="1000"
                :showRemainingCharactersProp="true"
                :bufferLimit="50"
            >
            </InputBox>
        </div>
    </FeatureFormBase>
</template>

<script>

import interactsWithFeatureForms from '@/vue-mixins/features/interactsWithFeatureForms.js';

import {
    changeLinkList,
    createLinkFromObject,
    updateLink,
    createLink,
    deleteLink,
} from '@/core/repositories/linkRepository.js';

import LINK from '@/graphql/links/queries/Link.gql';

export default {
    name: 'LinkForm',
    components: {
    },
    mixins: [
        interactsWithFeatureForms,
    ],
    props: {
        linkList: {
            type: [Object, null],
            default: null,
        },
        link: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        fullLink: {
            query: LINK,
            variables() {
                return { id: this.link.id };
            },
            skip() {
                return !this.link?.id;
            },
            update: ({ link }) => createLinkFromObject(link),
        },
    },
    data() {
        return {
            listKey: 'linkListId',
            listObjKey: 'linkList',
            featureType: 'LINKS',
            form: this.$apolloForm(() => {
                const data = {
                    description: this.link?.description || '',
                    url: this.link?.url || 'https://',
                    name: this.link?.name || '',
                };

                if (this.isNew) {
                    data.assigneeGroups = [];
                    data.associations = this.defaultAssociations || [];
                    data.markers = [];
                    data.linkListId = this.linkList?.id;
                } else {
                    data.id = this.link.id;
                }

                return data;
            }),
        };
    },
    computed: {
        savedItem() {
            return this.fullLink;
        },
    },
    methods: {

    },
    created() {
        this.changeLinkList = changeLinkList;
        this.createFunction = createLink;
        this.updateFunction = updateLink;
        this.deleteFunction = deleteLink;
    },
};
</script>

<style scoped>

/*.o-link-form {

} */

</style>
