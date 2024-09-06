<template>
    <Modal
        v-bind="$attrs"
        class="o-image-search-modal"
        containerClass="p-4 w-600p"
        positioning="TOP"
        @closeModal="$emit('closeModal')"
    >
        <div
            class="relative"
        >
            <FullLoaderProcessing
                v-if="processing"
                positionClass="absolute"
            >
            </FullLoaderProcessing>

            <div class="mb-4">
                <h2
                    v-t="'imageSearch.header'"
                    class="header-2 mb-1"
                >
                </h2>
                <p
                    v-t="'imageSearch.description'"
                    class="text-smbase text-cm-500"
                >
                </p>
            </div>

            <form
                class="flex gap-4"
                @submit.prevent="getImages"
            >
                <InputBox
                    ref="queryInput"
                    v-model="imageQuery"
                    class="flex-1"
                    bgColor="gray"
                    placeholder="Type a search term"
                >
                </InputBox>

                <button
                    class="button button-primary"
                    :class="{ unclickable: hasNoQuery }"
                    :disabled="hasNoQuery || processing"
                    type="submit"
                >
                    {{ $t('imageSearch.prompt') }}
                </button>
            </form>

            <div v-if="!processing && searchCompleted">
                <div
                    v-if="hasValidImages"
                    class="o-image-search-modal__results mt-10 grid gap-4"
                >
                    <ButtonEl
                        v-for="image in validImages"
                        :key="image.link"
                        class="o-image-search-modal__result shadow-primary-600/20"
                        @click="selectImage(image)"
                        @keyup.enter="selectImage(image)"
                        @keyup.space="selectImage(image)"
                    >
                        <!-- <ImageOrFallback
                            imageClass="rounded-lg"
                            :image="image.link"
                            :contain="true"
                        >
                        </ImageOrFallback> -->

                        <img
                            :src="`${proxyUrl}${image.link}`"
                            :alt="image.title"
                            crossorigin="anonymous"
                            referrerpolicy="no-referrer"
                            class="object-contain w-full h-full rounded-lg"
                        >
                    </ButtonEl>
                </div>
                <p
                    v-else-if="!validatingImages"
                    v-t="'imageSearch.noResults'"
                    class="mt-6 mb-3 text-center text-sm text-cm-500"
                >
                </p>
            </div>
        </div>

    </Modal>
</template>

<script>

import axios from 'axios';
import FullLoaderProcessing from '@/components/loaders/FullLoaderProcessing.vue';
import config from '@/core/config.js';
import { fetchFileFromUrl } from '@/core/fileHelpers.js';

const imagesPerRequest = 10;
const desiredImageCount = 30;

export default {
    name: 'ImageSearchModal',
    components: {
        FullLoaderProcessing,
    },
    mixins: [
    ],
    props: {
        presetQuery: {
            type: String,
            default: '',
        },
        // By default this will emit the selected image URL, but as it needs to
        // download the image to check if it's valid we can also emit the full
        // image file.
        emitDownloadedImage: Boolean,
    },
    emits: [
        'selectedImage',
        'closeModal',
    ],
    data() {
        return {
            imageQuery: this.presetQuery || '',
            imageResults: null,
            downloadedImages: null,
            processing: false,
            validatingImages: false,
            searchCompleted: false,
        };
    },
    computed: {
        hasNoQuery() {
            return this.imageQuery.length < 3;
        },
        proxyUrl() {
            return config('app.cors-proxy-url');
        },
        validImages() {
            return this.imageResults?.filter((image) => this.downloadedImages?.[image.link]) || [];
        },
        hasValidImages() {
            return !!this.validImages.length;
        },
    },
    methods: {
        async getImages() {
            this.imageResults = [];
            this.searchCompleted = false;
            this.processing = true;

            try {
                await this.fetchImagesRecursively(1);
            } finally {
                this.processing = false;
                this.searchCompleted = true;
            }
        },

        async fetchImagesRecursively(start) {
            if (this.imageResults.length >= desiredImageCount) {
                this.imageResults = this.imageResults.slice(0, desiredImageCount);
            } else {
                const images = await this.fetchImagesBatch(start);

                if (images.length > 0) {
                    this.imageResults = this.imageResults.concat(images);
                    const num = start + imagesPerRequest;
                    await this.fetchImagesRecursively(num);
                }
            }
        },

        async fetchImagesBatch(start) {
            const num = desiredImageCount;
            try {
                const apiUrl = `/image-search?query=${this.imageQuery}&start=${start}&num=${num}`;
                const response = await axios.get(apiUrl);

                return response.data?.data || [];
            } catch (e) {
                this.handleFetchError(e);
                return [];
            }
        },

        handleFetchError(e) {
            if (e.request.status === 400) {
                this.$errorFeedback({
                    customHeaderPath: 'feedback.responses.imageSearch.400.header',
                    customMessagePath: 'feedback.responses.imageSearch.400.message',
                });
            } else {
                throw e;
            }
        },
        // async getImages() {
        //     this.processing = true;
        //     this.imageResults = [];
        //     let start = 1;
        //     const imagesPerRequest = 10;
        //     const desiredImageCount = 30;

        //     const fetchImages = async () => {
        //         if (this.imageResults.length >= desiredImageCount) {
        //             this.imageResults = this.imageResults.slice(0, desiredImageCount);
        //             return;
        //         }

        //         try {
        //             const apiUrl = `/image-search?query=${this.imageQuery}&start=${start}&num=${imagesPerRequest}`;
        //             const results = await axios.get(apiUrl);

        //             if (!results.data.data || results.data.data.length === 0) {
        //                 return;
        //             }

        //             this.imageResults = this.imageResults.concat(results.data.data);
        //             start += imagesPerRequest;

        //             await fetchImages();
        //         } catch (e) {
        //             if (e.request.status === 400) {
        //                 this.$errorFeedback({
        //                     customHeaderPath: 'feedback.responses.imageSearch.400.header',
        //                     customMessagePath: 'feedback.responses.imageSearch.400.message',
        //                 });
        //             } else {
        //                 throw e;
        //             }
        //         }
        //     };

        //     try {
        //         await fetchImages();
        //     } finally {
        //         this.processing = false;
        //     }
        // },
        selectImage(image) {
            if (this.emitDownloadedImage) {
                this.$emit('selectedImage', this.downloadedImages[image.link]);
            } else {
                this.$emit('selectedImage', image.link);
            }
            this.$emit('closeModal');
        },
        async downloadImages(urls) {
            this.validatingImages = true;
            this.downloadedImages = {};
            await Promise.all(urls.map(({ link }) => {
                return this.fetchImage(link).then((image) => {
                    this.downloadedImages[link] = image;
                });
            }));
            this.validatingImages = false;
        },
        async fetchImage(image) {
            try {
                const imageUrl = `${this.proxyUrl}${image}`;
                return await fetchFileFromUrl(imageUrl);
            } catch (error) {
                return false;
            }
        },
    },
    watch: {
        imageResults(results) {
            this.downloadImages(results);
        },
    },
    created() {

    },
    mounted() {
        this.$refs.queryInput.focus();
    },

};
</script>

<style scoped>

.o-image-search-modal {
    &__results {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }

    &__result {
        transition: 0.2s ease-in-out;

        @apply
            border
            border-cm-200
            border-solid
            h-24
            rounded-lg
            w-24
        ;

        &:hover {
            @apply
                border-primary-600
                shadow-lg
            ;
        }
    }
}

</style>
