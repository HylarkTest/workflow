import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

import CATEGORIES from '@/graphql/categories/queries/Categories.gql';
import BASIC_CATEGORIES from '@/graphql/categories/queries/BasicCategories.gql';
import UPDATE_CATEGORY from '@/graphql/categories/mutations/UpdateCategory.gql';
import CREATE_CATEGORY from '@/graphql/categories/mutations/CreateCategory.gql';
import DELETE_CATEGORY from '@/graphql/categories/mutations/DeleteCategory.gql';
import CREATE_CATEGORY_ITEM from '@/graphql/categories/mutations/CreateCategoryItem.gql';
import UPDATE_CATEGORY_ITEM from '@/graphql/categories/mutations/UpdateCategoryItem.gql';
import DELETE_CATEGORY_ITEM from '@/graphql/categories/mutations/DeleteCategoryItem.gql';
import MOVE_CATEGORY_ITEM from '@/graphql/categories/mutations/MoveCategoryItem.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';

export function initializeCategories(data) {
    return initializeConnections(data);
}

function changeGroupId(data) {
    return {
        ..._.omit(data, ['groupId', 'color']),
        categoryId: data.groupId,
    };
}

export function updateCategory(form) {
    return form.graphql(
        UPDATE_CATEGORY
    );
}

export function createCategory(form) {
    return form.graphql(CREATE_CATEGORY, {
        refetchQueries: [CATEGORIES],
    });
}

export function deleteCategory(category) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_CATEGORY,
        variables: {
            input: {
                id: category.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            CATEGORIES,
            BASIC_CATEGORIES,
        ], client),
        update(cache) {
            function removeCategory(fields) {
                return fields.flatMap((field) => {
                    if (field.type === 'CATEGORY' && field.options && field.options.category === category.id) {
                        return [];
                    } if (field.type === 'MULTI') {
                        const optionsFields = [{
                            ...field,
                            options: {
                                ...field.options,
                                fields: removeCategory(field.options.fields),
                            },
                        }];
                        return optionsFields[0].options.fields.length > 0 ? optionsFields : [];
                    }
                    return [field];
                });
            }

            Object.values(cache.data.data).forEach((value) => {
                if (value.__typename === 'Mapping' && Array.isArray(value.fields)) {
                    cache.modify({
                        id: cache.identify(value),
                        fields: {
                            fields() {
                                return removeCategory(value.fields);
                            },
                        },
                    });
                }
            });
        },
    });
}

export function createCategoryItem(form) {
    return form.graphql(
        CREATE_CATEGORY_ITEM,
        { formatData: changeGroupId }
    );
}

export function deleteCategoryItem(item) {
    return baseApolloClient().mutate({
        mutation: DELETE_CATEGORY_ITEM,
        variables: {
            input: {
                categoryId: item.group.id,
                id: item.id,
            },
        },
        update(cache) {
            cache.evict({ id: cache.identify(item) });
            cache.gc();
        },
    });
}

export function updateCategoryItem(form) {
    return form.graphql(
        UPDATE_CATEGORY_ITEM,
        { formatData: changeGroupId }
    );
}

export function moveCategoryItem(categoryItem, previousItem) {
    const previousId = previousItem?.id || null;

    baseApolloClient().mutate({
        mutation: MOVE_CATEGORY_ITEM,
        variables: {
            input: {
                categoryId: categoryItem.group.id,
                id: categoryItem.id,
                previousId,
            },
        },
    });
}

export const groupRepository = {
    updateGroup: updateCategory,
    deleteGroup: deleteCategory,
    createGroup: createCategory,
    updateGroupItem: updateCategoryItem,
    createGroupItem: createCategoryItem,
    deleteGroupItem: deleteCategoryItem,
    moveGroupItem: moveCategoryItem,
};
