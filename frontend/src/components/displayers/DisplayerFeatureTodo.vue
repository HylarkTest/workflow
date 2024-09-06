<template>
    <div
        class="c-displayer-feature-todo"
    >
        <DisplayerFeatureItem
            featureType="TODOS"
            :list="null"
            :dataValue="dataValue"
            v-bind="$attrs"
        >
            <template #featureItem="{ createFeatureFormModal }">
                <TodoMini
                    :todo="dataValue"
                    :hoverable="true"
                    :tinyVersion="true"
                    @selectTodo="createFeatureFormModal"
                >
                </TodoMini>
            </template>
        </DisplayerFeatureItem>
    </div>
</template>

<script setup>
import { toRefs } from 'vue';

import TodoMini from '@/components/todos/TodoMini.vue';

const props = defineProps({
    dataValue: {
        type: [Object, null],
        required: true,
    },
});

const {
    dataValue,
} = toRefs(props);
</script>
