<template>
    <div class="c-icon-search">
        <InputBox
            v-model="query"
            bgColor="gray"
            placeholder="Type a search term to find an icon..."
        >
        </InputBox>

        <div class="mt-10">
            <h3 class="font-semibold text-lg mb-2 text-center">
                {{ resultsLength ? 'Results' : 'Popular' }}
            </h3>
            <div class="flex flex-wrap justify-center">
                <button
                    v-for="icon in icons"
                    :key="icon"
                    class="c-icon-search__icon centered"
                    :class="{ 'c-icon-search__icon--selected': isSelected(icon) }"
                    type="button"
                    @click="selectIcon(icon)"
                >
                    <i
                        class="fal fa-fw"
                        :class="icon"
                    >
                    </i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import _ from 'lodash';
import axios from 'axios';

import popularIcons from '@/core/data/popularIcons.js';

export default {
    name: 'IconSearch',
    components: {

    },
    mixins: [
    ],
    props: {
        selectedIcon: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:selectedIcon',
    ],
    data() {
        return {
            query: '',
            token: null,
            results: [],
            abortController: new AbortController(),
        };
    },
    computed: {
        resultsLength() {
            return this.results.length;
        },
        icons() {
            return this.resultsLength ? this.results : popularIcons;
        },
    },
    methods: {
        isSelected(icon) {
            return icon === this.selectedIcon;
        },
        selectIcon(icon) {
            if (this.isSelected(icon)) {
                this.$emit('update:selectedIcon', null);
            } else {
                this.$emit('update:selectedIcon', icon);
            }
        },
    },
    watch: {
        query: _.debounce(async function onQueryChange(q) {
            this.abortController.abort();
            this.abortController = new AbortController();
            if (q) {
                try {
                    const response = await axios.get(`/font-awesome-query/${q}`, {
                        signal: this.abortController.signal,
                    });
                    this.results = response.data.search.map((icon) => `fa-${icon}`);
                } catch (e) {
                    if (e.name !== 'CanceledError') {
                        throw e;
                    }
                    this.results = [];
                }
            } else {
                this.results = [];
            }
        }, 200, { leading: true, maxWait: 500, trailing: true }),
    },
    created() {
    },
};
</script>

<style scoped>

.c-icon-search {
    &__icon {
        transition: 0.2s ease-in-out;
        @apply
            border
            border-cm-300
            border-solid
            h-12
            m-1
            rounded-lg
            text-2xl
            text-cm-400
            w-12
        ;

        &:hover {
            @apply
                bg-cm-100
                shadow-md
                text-cm-500
            ;
        }

        &--selected {
            @apply
                bg-primary-600
                text-cm-00
            ;

            &:hover {
                @apply
                    bg-primary-600
                    text-cm-00
                ;
            }
        }
    }
}

</style>
