import { getOperationName } from '@apollo/client/utilities';
import SAVED_FILTERS from '@/graphql/savedFilters/queries/SavedFilters.gql';
import CREATE_SAVED_FILTER from '@/graphql/savedFilters/mutations/CreateSavedFilter.gql';
import UPDATE_SAVED_FILTER from '@/graphql/savedFilters/mutations/UpdateSavedFilter.gql';
import DELETE_SAVED_FILTER from '@/graphql/savedFilters/mutations/DeleteSavedFilter.gql';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { convertLocalFiltersToApiFilters } from '@/core/helpers/filterConverter.js';
import { createApolloForm } from '@/core/plugins/formlaPlugin.js';
import { updateMappingPage } from '@/core/repositories/pageRepository.js';

export async function saveFilter(form, page) {
    const isNew = !form.id;
    const query = isNew ? CREATE_SAVED_FILTER : UPDATE_SAVED_FILTER;

    const response = await form.graphql(
        query,
        {
            formatData(data) {
                const keysToPick = isNew ? ['nodeId', 'name', 'private'] : ['id', 'name'];
                return {
                    ..._.pick(data, keysToPick),
                    ...convertLocalFiltersToApiFilters(data),
                };
            },
            refetchQueries: [getOperationName(SAVED_FILTERS)],
        }
    );

    const savedFilter = _.getFirstKey(response.data).savedFilter;

    const formData = {};
    if (form.personalDefault && page.personalDefaultFilter?.id !== savedFilter.id) {
        formData.personalDefaultFilterId = savedFilter.id;
    } else if (!form.personalDefault && page.personalDefaultFilter?.id === savedFilter.id) {
        formData.personalDefaultFilterId = null;
    }
    if (form.generalDefault && page.defaultFilter?.id !== savedFilter.id) {
        formData.defaultFilterId = savedFilter.id;
    } else if (!form.generalDefault && page.defaultFilter?.id === savedFilter.id) {
        formData.defaultFilterId = null;
    }
    if (!_.isEmpty(formData)) {
        updateMappingPage(createApolloForm(
            form._apolloClient,
            {
                id: page.id,
                ...formData,
            }
        ), page);
    }

    return savedFilter;
}

export function deleteSavedFilter(savedFilter) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_SAVED_FILTER,
        variables: {
            input: {
                id: savedFilter.id,
            },
        },
        refetchQueries: [SAVED_FILTERS],
        update(cache) {
            Object.values(cache.data.data).forEach((value) => {
                if (value.__typename === 'EntitiesPage') {
                    ['defaultFilter', 'personalDefaultFilter'].forEach((key) => {
                        if (cache.data.getFieldValue(value[key], 'id') === savedFilter.id) {
                            cache.modify({
                                id: cache.identify(value),
                                fields: {
                                    [key]() {
                                        return null;
                                    },
                                },
                            });
                        }
                    });
                }
            });
        },
    });
}
