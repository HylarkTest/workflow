<template>
    <div class="o-emails-layout">
        <template
            v-if="!hasIntegrationError"
        >
            <FeatureMain
                v-if="!isLoading && emailIntegrationsLength"
                v-bind="$attrs"
                :lists="[]"
                :node="node"
                :hideAllLineOptions="true"
                freePlaceholder="Search emails"
                layoutSideClass="feature-page__side--subless"
            >
                <template
                    #default="{
                        displayedList,
                        isSideMinimized,
                        events,
                        filtersObj,
                    }"
                >
                    <EmailsListing
                        :displayedList="displayedList"
                        :filtersObj="filtersObj"
                        :isSideMinimized="isSideMinimized"
                        :lastUsedIntegration="lastUsedIntegration"
                        :suggestedEmailAddresses="suggestedEmailAddresses"
                        :emailAddressesForAssociation="emailAddressesForAssociation"
                        :topHeaderClass="topHeaderClass"
                        :node="node"
                        @minimizeSide="events.minimize"
                    >
                        <template #headerButtonOption>
                            <slot name="headerButtonOption">

                            </slot>
                        </template>
                    </EmailsListing>
                </template>
            </FeatureMain>

            <NoContentText
                v-else-if="!isLoading"
                class="mt-10"
                customHeaderPath="emails.noContent.main.header"
                customMessagePath="emails.noContent.main.description"
                customIcon="fa-envelope-dot"
            >
                <router-link
                    :to="{ name: 'settings.integrations' }"
                    class="button button-primary mt-4 inline-flex"
                >
                    Set up emails
                </router-link>
            </NoContentText>
        </template>

        <NoContentText
            v-else
            class="mt-10"
            customHeaderPath="emails.integrationError.header"
            customMessagePath="emails.integrationError.description"
        >
            <template
                #graphic
            >
                <BirdImage
                    class="h-28"
                    whichBird="LostAndConfusedBird_72dpi.png"
                >
                </BirdImage>
            </template>

        </NoContentText>
    </div>
</template>

<script>

// import FreeFilter from '@/components/sorting/FreeFilter.vue';
import EmailsListing from '@/components/emails/EmailsListing.vue';
import FeatureMain from '@/components/features/FeatureMain.vue';

export default {
    name: 'EmailsLayout',
    components: {
        // FreeFilter,
        EmailsListing,
        FeatureMain,
    },
    mixins: [
    ],
    props: {
        isLoading: Boolean,
        hasIntegrationError: Boolean,
        emailIntegrationsLength: {
            type: Number,
            required: true,
        },
        lastUsedIntegration: {
            type: [String, null],
            default: '',
        },
        suggestedEmailAddresses: {
            type: [Array, null],
            default: null,
        },
        emailAddressesForAssociation: {
            type: [Array, null],
            default: null,
        },
        topHeaderClass: {
            type: [String],
            default: 'nav-spacing--sticky',
        },
        node: {
            type: [Object, null],
            default: null,
        },
    },
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

/*.o-emails-layout {

} */

</style>
