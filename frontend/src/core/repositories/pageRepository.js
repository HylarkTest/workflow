import { property } from 'lodash';
import axios from 'axios';
import { createTodoList } from '@/core/repositories/todoListRepository.js';
import { createApolloForm } from '@/core/plugins/formlaPlugin.js';
import { createCalendar } from '@/core/repositories/calendarRepository.js';
import CREATE_LIST_PAGE from '@/graphql/pages/mutations/CreateListPage.gql';
import CREATE_ENTITY_PAGE from '@/graphql/pages/mutations/CreateEntityPage.gql';
import CREATE_ENTITIES_PAGE from '@/graphql/pages/mutations/CreateEntitiesPage.gql';
import UPDATE_ENTITY_PAGE from '@/graphql/pages/mutations/UpdateEntityPage.gql';
import UPDATE_ENTITIES_PAGE from '@/graphql/pages/mutations/UpdateEntitiesPage.gql';
import UPDATE_LIST_PAGE from '@/graphql/pages/mutations/UpdateListPage.gql';
import NAV_LINKS from '@/graphql/pages/queries/Pages.gql';
import PAGES from '@/graphql/pages/queries/AllPages.gql';
import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';
import LINKS from '@/graphql/Links.gql';
import { createMapping } from '@/core/repositories/mappingRepository.js';
import DELETE_PAGE from '@/graphql/pages/mutations/DeletePage.gql';
import NOTEBOOKS from '@/graphql/notes/queries/Notebooks.gql';
import NOTE_STATS from '@/graphql/notes/queries/NoteStats.gql';
import NOTEBOOK_FRAGMENT from '@/graphql/notes/NotebookWithCountFragment.gql';
import PINBOARDS from '@/graphql/pinboard/queries/Pinboards.gql';
import PIN_STATS from '@/graphql/pinboard/queries/PinStats.gql';
import PINBOARD_FRAGMENT from '@/graphql/pinboard/PinboardWithCountFragment.gql';
import LINK_LISTS from '@/graphql/links/queries/LinkLists.gql';
import LINK_STATS from '@/graphql/links/queries/LinkStats.gql';
import LINK_LIST_FRAGMENT from '@/graphql/links/LinkListWithCountFragment.gql';
import DRIVES from '@/graphql/documents/queries/Drives.gql';
import DOCUMENT_STATS from '@/graphql/documents/queries/DocumentStats.gql';
import DRIVE_FRAGMENT from '@/graphql/documents/DriveWithCountFragment.gql';
import TODO_LISTS from '@/graphql/todos/queries/TodoLists.gql';
import TODO_STATS from '@/graphql/todos/queries/TodoStats.gql';
import TODO_LIST_FRAGMENT from '@/graphql/todos/TodoListWithCountFragment.gql';
import CALENDARS from '@/graphql/calendar/queries/Calendars.gql';
import CALENDAR_FRAGMENT from '@/graphql/calendar/CalendarFragment.gql';
import {
    arrRemoveId, generateId, instantiate,
} from '@/core/utils.js';
import Page from '@/core/models/Page.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { dashboardViews } from '@/core/display/fullViews.js';
import { createPinboard } from '@/core/repositories/pinboardRepository.js';
import { createLinkList } from '@/core/repositories/linkListRepository.js';
import { createDrive } from '@/core/repositories/driveRepository.js';
import { createNotebook } from '@/core/repositories/notebookRepository.js';
import { limitFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import { isLimitError } from '@/http/checkResponse.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

const apiMap = {
    TODOS: {
        createFn: createTodoList,
        listQuery: TODO_LISTS,
        statsQuery: TODO_STATS,
        listFragment: [TODO_LIST_FRAGMENT, 'TodoListWithCount'],
    },
    CALENDAR: {
        createFn: createCalendar,
        listQuery: CALENDARS,
        listFragment: [CALENDAR_FRAGMENT, 'Calendar'],
    },
    PINBOARD: {
        createFn: createPinboard,
        listQuery: PINBOARDS,
        statsQuery: PIN_STATS,
        listFragment: [PINBOARD_FRAGMENT, 'PinboardWithCount'],
    },
    LINKS: {
        createFn: createLinkList,
        listQuery: LINK_LISTS,
        statsQuery: LINK_STATS,
        listFragment: [LINK_LIST_FRAGMENT, 'LinkListWithCount'],
    },
    DOCUMENTS: {
        createFn: createDrive,
        listQuery: DRIVES,
        statsQuery: DOCUMENT_STATS,
        listFragment: [DRIVE_FRAGMENT, 'DriveWithCount'],
    },
    NOTES: {
        createFn: createNotebook,
        listQuery: NOTEBOOKS,
        statsQuery: NOTE_STATS,
        listFragment: [NOTEBOOK_FRAGMENT, 'NotebookWithCount'],
    },
};

export function createPageFromObject(obj) {
    return instantiate(obj, Page);
}

export function initializePages(data) {
    return initializeConnections(data);
}

export function createList(type, data, space, templateRefs) {
    const listForm = createApolloForm(
        baseApolloClient(),
        {
            ..._.pick(data, ['name', 'color', 'image']),
            spaceId: space.id,
            templateRefs,
        }
    );
    return apiMap[type]?.createFn(listForm, space);
}

function replaceFilter(data) {
    const replacedFields = {};
    // No filter exists / a filter is being removed
    if (_.has(data, 'filter') && data.filter === null) {
        return {
            ..._.omit(data, 'filter'),
            fieldFilters: null,
            markerFilters: null,
        };
    }
    // A filter exists / is being added
    if (_.has(data, 'filter.by')) {
        if (data.filter.by === 'FIELD') {
            replacedFields.fieldFilters = [{
                fieldId: data.filter.fieldId,
                operator: data.filter.match,
                match: JSON.stringify(data.filter.matchValue),
            }];
            replacedFields.markerFilters = null;
        } else {
            replacedFields.markerFilters = [{
                operator: data.filter.match,
                markerId: data.filter.matchValue,
                context: data.filter.context,
            }];
            replacedFields.fieldFilters = null;
        }
        return {
            ..._.omit(data, 'filter'),
            ...replacedFields,
        };
    }
    // No filter is specified so don't alter the form data
    return _.omit(data, 'filter');
}

export function createPage(pageForm, space) {
    const fields = ['symbol', 'path', 'name', 'folder', 'description'];
    let query;
    let responseKey;
    if (['ENTITIES', 'ENTITY'].includes(pageForm.type)) {
        if (pageForm.type === 'ENTITIES') {
            query = CREATE_ENTITIES_PAGE;
            fields.push('singularName');
            fields.push('filter');
            fields.push('newData');
            responseKey = 'data.createEntitiesPage';
        } else {
            query = CREATE_ENTITY_PAGE;
            responseKey = 'data.createEntityPage';
        }
        fields.push('mapping');
    } else {
        query = CREATE_LIST_PAGE;
        fields.push('lists');
        fields.push('type');
        fields.push('templateRefs');
        responseKey = 'data.createListPage';
    }
    return pageForm.post({
        query,
        formatData(data) {
            const form = {
                spaceId: space.id,
                ..._.pick(data, fields),
            };
            if (_.has(form, 'mapping')) {
                form.mappingId = form.mapping;
                delete form.mapping;
            }

            if (data.type === 'ENTITIES') {
                return replaceFilter(form);
            }
            return form;
        },
        refetchQueries: [
            LINKS,
            NAV_LINKS,
            PAGES,
        ],
    }).then(property(responseKey));
}

export async function createPageFromWizard(data) {
    try {
        const response = await axios.post('/page-wizard', data);
        if (response.data.errors) {
            throw response.data;
        }
        const client = baseApolloClient();
        client.refetchQueries({ include: getCachedOperationNames([LINKS, NAV_LINKS, PAGES, MAPPINGS], client) });
        return response.data.data.pages;
    } catch (e) {
        if (isLimitError(e)) {
            limitFeedback();
        }
        throw e;
    }
}

export async function createListPage(pageForm, listForm, space) {
    const promises = listForm.newLists.map((list) => {
        return createList(pageForm.type, list, space, pageForm.templateRefs);
    });

    const responses = await Promise.all(promises);

    const listIds = _.map(responses, 'id');
    // eslint-disable-next-line no-param-reassign
    pageForm.append('lists', _.map(listForm.lists, 'id').concat(listIds));

    return createPage(pageForm, space);
}

export async function createMappingPage(pageForm, blueprintForm, space) {
    if (pageForm.mapping === 'NEW') {
        const response = await createMapping(blueprintForm, space);
        // eslint-disable-next-line no-param-reassign
        pageForm.mapping = response.mapping.id;
    }

    return createPage(pageForm, space);
}

export function createFullPage(pageForm, listForm, blueprintForm, space) {
    if (['TODOS', 'CALENDAR', 'LINKS', 'DOCUMENTS', 'PINBOARD', 'NOTES'].includes(pageForm.type)) {
        return createListPage(pageForm, listForm, space);
    }
    if (['ENTITIES', 'ENTITY'].includes(pageForm.type)) {
        return createMappingPage(pageForm, blueprintForm, space);
    }
    return null;
}

export function updateListPage(form) {
    return form.post({
        query: UPDATE_LIST_PAGE,
        formatData(data) {
            return _.pick(data, [
                'id',
                'path',
                'name',
                'folder',
                'symbol',
                'description',
                'design',
                'lists',
                'image',
            ]);
        },
    });
}

export function setListsOnPage(page, lists) {
    return updateListPage(createApolloForm(baseApolloClient(), {
        id: page.id,
        lists,
    }, {
        update: (cache) => {
            const listQuery = apiMap[page.type].listQuery;
            const statQuery = apiMap[page.type].statsQuery;

            const data = _.cloneDeep(cache.readQuery({
                query: listQuery,
                variables: { spaceId: page.space.id, forLists: page.lists },
            }));
            if (!data) {
                return;
            }
            const dataKey = _.firstKey(data);
            const typeName = data[dataKey].__typename.replace('Connection', '');
            const [fragment, fragmentName] = apiMap[page.type].listFragment;
            data[dataKey].edges = lists.map((list) => ({
                __typename: `${typeName}Edge`,
                node: cache.readFragment({
                    id: `${typeName}:${list}`,
                    fragment,
                    fragmentName,
                }),
            })).filter(({ node }) => node);
            cache.writeQuery({
                query: listQuery,
                variables: { spaceId: page.space.id, forLists: lists },
                data,
            });
            if (statQuery) {
                cache.writeQuery({
                    query: statQuery,
                    variables: { forLists: lists },
                    data: cache.readQuery({
                        query: statQuery,
                        variables: { forLists: page.lists },
                    }),
                });
            }
        },
    }));
}

export function addListToPage(page, list) {
    const lists = [...page.lists, list.id];
    return setListsOnPage(page, lists);
}

export function updateMappingPage(form, page) {
    const query = page.type === 'ENTITIES' ? UPDATE_ENTITIES_PAGE : UPDATE_ENTITY_PAGE;
    return form.post({
        query,
        ...(page.type === 'ENTITIES' ? { formatData: replaceFilter } : {}),
    });
}

export function deletePage(page) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_PAGE,
        variables: {
            input: { id: page.id },
        },
        refetchQueries: getCachedOperationNames([
            LINKS,
            NAV_LINKS,
            PAGES,
        ], client),
    });
}

function updateQueryAndPathFromPage(page) {
    let query;
    let path;
    if (page.type === 'ENTITY') {
        query = UPDATE_ENTITY_PAGE;
        path = 'updateEntityPage';
    } else if (page.type === 'ENTITIES') {
        query = UPDATE_ENTITIES_PAGE;
        path = 'updateEntitiesPage';
    } else {
        query = UPDATE_LIST_PAGE;
        path = 'updateListPage';
    }
    return { query, path };
}

export async function updatePageView(form, page) {
    const pageViews = _.get(page, 'design.views', []);
    const foundView = _.findIndex(pageViews, ['id', form.id]);

    let id = '';
    let deletedViews = page.design?.deletedViews || [];

    const isExistingView = !!~foundView;
    const isFirstViewOfType = deletedViews.includes(form.viewType);

    if (isExistingView) {
        id = pageViews[foundView].id;
    } else if (isFirstViewOfType) {
        id = form.viewType;
        deletedViews = [
            ...deletedViews.filter((viewType) => viewType !== id),
        ];
    } else {
        id = generateId(8);
    }

    const originalFormatData = form._options.formatData || _.identity;

    const { query, path } = updateQueryAndPathFromPage(page);
    const result = await form.graphql(
        query,
        {
            formatData(unformattedData) {
                const data = originalFormatData(unformattedData);
                if (data.visibleData) {
                    data.visibleData = data.visibleData.map(
                        (field) => _.pick(field, [
                            'dataType',
                            'formattedId',
                            'combo',
                            'width',
                            'slot',
                            'designAdditional',
                        ])
                    );
                }
                let views;
                if (isExistingView) {
                    views = [
                        ...pageViews.slice(0, foundView),
                        {
                            ...pageViews[foundView],
                            ...data,
                        },
                        ...pageViews.slice(foundView + 1),
                    ];
                } else {
                    views = [...pageViews, { id, ...data }];
                }
                return {
                    id: page.id,
                    design: {
                        ...(page.design || {}),
                        views,
                        deletedViews: _.uniq(deletedViews),
                    },
                };
            },
        }
    );

    const views = _.get(result, `data.${path}.page.design.views`);

    return _.find(views, (view) => view.id === id || view.val === id);
}

export async function deletePageView(id, page) {
    const pageViews = _.get(page, 'design.views', []);
    const foundView = _.findIndex(pageViews, ['id', id]);

    const design = {
        ...(page.design || {}),
        views: pageViews,
    };

    if (~foundView) {
        design.views = arrRemoveId(design.views, id);
    }

    if (_.keys(dashboardViews).includes(id)) {
        design.deletedViews = [...(design.deletedViews || []), id];
    }

    const { query, path } = updateQueryAndPathFromPage(page);
    const result = await baseApolloClient().mutate({
        mutation: query,
        variables: {
            input: {
                id: page.id,
                design,
            },
        },
    });

    return _.get(result, `data.${path}.page.design.views`);
}

export function updatePageDesign(form, page) {
    let query;
    if (page.type === 'ENTITY') {
        query = UPDATE_ENTITY_PAGE;
    } else if (page.type === 'ENTITIES') {
        query = UPDATE_ENTITIES_PAGE;
    } else {
        query = UPDATE_LIST_PAGE;
    }
    return form.graphql(
        query,
        {
            formatData(data) {
                return {
                    id: page.id,
                    design: {
                        ...(page.design || {}),
                        ...data,
                    },
                };
            },
        }
    );
}
