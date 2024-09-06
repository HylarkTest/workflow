<template>
    <div class="o-home-feature-short">
        <div
            class="o-home-feature-short__header relative"
        >
            <h4 class="text-base md:text-lg text-primary-900 font-bold">
                {{ header }}
            </h4>
            <CloseButton
                v-if="showClose"
                class="absolute top-0 right-0"
                @click="$emit('closeDate')"
            >
            </CloseButton>
        </div>

        <LoaderFetch
            v-if="isLoading"
            class="rounded-lg h-7 flex items-center justify-center my-3"
            :sphereSize="15"
        >
        </LoaderFetch>

        <template
            v-else
        >
            <div
                v-if="!hasItems"
                class="o-home-feature-short__no-items"
            >
                <p
                    v-t="getText('noItems')"
                >
                </p>
                <p
                    v-if="!dateIsToday"
                    v-t="getText('noItemsPrompt')"
                    class="mt-4"
                >
                </p>
            </div>

            <template
                v-else-if="isEvents"
            >
                <EventMini
                    v-for="event in events"
                    :key="event.id"
                    class="my-1.5"
                    assumeToday
                    hoverable
                    showExtras
                    :actionProcessing="actionProcessingIds.includes(event.id)"
                    :deleteProcessing="deleteProcessingIds.includes(event.id)"
                    :event="event"
                    :cellDateObj="dateObj"
                    @selectEvent="openFeatureItem"
                    @update:processing="updateProcessingItems(event.id, $event)"
                >
                </EventMini>
            </template>

            <template
                v-else-if="isTodos"
            >
                <TodoMini
                    v-for="todo in todos"
                    :key="todo.id"
                    class="my-1.5"
                    hoverable
                    showExtras
                    :actionProcessing="actionProcessingIds.includes(todo.id)"
                    :deleteProcessing="deleteProcessingIds.includes(todo.id)"
                    :todo="todo"
                    @selectTodo="openFeatureItem"
                    @update:processing="updateProcessingItems(todo.id, $event)"
                >
                </TodoMini>
            </template>

            <div
                v-if="!hideCreate"
                class="flex justify-center"
            >
                <button
                    class="button--sm button-primary--light text-center mt-2"
                    type="button"
                    :disabled="isLoading"
                    @click="createFeatureItem"
                >
                    <i class="fal fa-calendar-alt mr-1">
                    </i>
                    <p
                        v-t="getText('createItem')"
                        class="inline"
                    >
                    </p>
                </button>
            </div>

            <div class="w-full center mt-2">
                <router-link
                    v-if="showRoute"
                    :to="{ name: route }"
                    class="text-xxsxs text-cm-500 font-semibold hover:underline transition-2eio"
                >
                    View all
                </router-link>
            </div>
        </template>
    </div>
</template>

<script>
import EventMini from '@/components/events/EventMini.vue';
import TodoMini from '@/components/todos/TodoMini.vue';
import LoaderFetch from '@/components/loaders/LoaderFetch.vue';
import CloseButton from '@/components/buttons/CloseButton.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import TODOS from '@/graphql/todos/queries/Todos.gql';
import EVENTS from '@/graphql/calendar/queries/Events.gql';

import { initializeTodos } from '@/core/repositories/todoRepository.js';
import { initializeEvents } from '@/core/repositories/eventRepository.js';

import {
    isToday,
    formatDateTime,
} from '@/core/dateTimeHelpers.js';

import useDateTime from '@/composables/useDateTime.js';
import useFeatureItemModal from '@/composables/useFeatureItemModal.js';

export default {
    name: 'HomeFeatureShort',
    components: {
        EventMini,
        TodoMini,
        LoaderFetch,
        CloseButton,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        featureType: {
            type: String,
            required: true,
            validator: (type) => ['EVENTS', 'TODOS'].includes(type),
        },
        eventDate: {
            type: [String, null],
            default: null,
        },
        hideClose: Boolean,
        hideRoute: Boolean,
        hideCreate: Boolean,
    },
    emits: [
        'closeDate',
    ],
    setup(props) {
        const {
            currentUtc,
        } = useDateTime(props);

        const {
            featureItemFormKey,
            createFeatureFormModal,
        } = useFeatureItemModal(props);

        return {
            currentUtc,

            featureItemFormKey,
            createFeatureFormModal,
        };
    },
    apollo: {
        todos: {
            query: TODOS,
            variables() {
                return {
                    first: 5,
                    isCompleted: false,
                    orderBy: [
                        { field: 'DUE_BY', direction: 'ASC' },
                        { field: 'CREATED_AT', direction: 'DESC' },
                    ],
                };
            },
            skip() {
                return !this.isTodos;
            },
            update: (data) => initializeTodos(data),
        },
        events: {
            query: EVENTS,
            variables() {
                return {
                    first: 5,
                    endsAfter: this.dateObj.startOf('day'),
                    startsBefore: this.dateObj.endOf('day'),
                    includeRecurringInstances: true,
                };
            },
            skip() {
                return !this.isEvents;
            },
            update: (data) => initializeEvents(data),
        },
    },
    data() {
        return {
            actionProcessingIds: [],
            deleteProcessingIds: [],
        };
    },
    computed: {
        isLoading() {
            // return this.$apollo.queries.todos.loading
            //     || this.$apollo.queries.events.loading;

            // trying a generic way to check loading... global mixin?
            return Object.keys(this.$apollo?.queries).map((key) => {
                return this.$apollo.queries[key].loading;
            }).filter((loading) => loading).length;
        },
        date() {
            return this.eventDate || this.currentUtc;
        },
        dateObj() {
            return this.$dayjs(this.date);
        },
        dateIsToday() {
            return isToday(this.date);
        },
        header() {
            if (this.isEvents && !this.dateIsToday) {
                return formatDateTime(this.date, 'MMMM D');
            }

            return this.$t(`home.side.${this.featureType.toLowerCase()}.header`);
        },
        isEvents() {
            return this.featureType === 'EVENTS';
        },
        isTodos() {
            return this.featureType === 'TODOS';
        },
        hasItems() {
            return (this.isEvents && this.events?.length)
                || (this.isTodos && this.todos?.length);
        },
        modalProps() {
            return {
                ...(this.isEvents && this.eventDate && { time: this.dateObj }),
                ...(this.isTodos && {}),
            };
        },
        route() {
            return (this.isEvents && 'calendar')
                || (this.isTodos && 'todos');
        },
        showClose() {
            return !this.hideClose;
        },
        showRoute() {
            return !this.hideRoute;
        },
    },
    methods: {

        openFeatureItem(item) {
            this.createFeatureFormModal({
                [this.featureItemFormKey]: item,
            });
        },
        createFeatureItem() {
            this.openFeatureItem(null);
        },
        getText(key) {
            let translationKey = key;
            if (key === 'noItems' && this.dateIsToday) {
                translationKey += 'Today';
            }
            return `home.side.${this.featureType.toLowerCase()}.${translationKey}`;
        },
        updateProcessingItems(itemId, { processingType, state }) {
            const processingKey = `${processingType}ProcessingIds`;
            if (state) {
                this[processingKey].push(itemId);
            } else {
                _.remove(this[processingKey], (id) => id === itemId);
            }
        },
    },
};
</script>

<style>
.o-home-feature-short {
    @apply
        lg:flex-none
        lg:w-full
    ;

    &__header {
        @apply
            flex
            items-center
            justify-between
            mb-2
        ;
    }

    &__no-items {
        @apply
            bg-cm-100
            flex
            flex-col
            font-semibold
            px-4
            py-3
            rounded-lg
            text-center
            text-cm-500
            text-sm
        ;
    }
}
</style>
