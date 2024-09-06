import UPDATE_LINK_LIST from '@/graphql/links/mutations/UpdateLinkList.gql';
import DELETE_LINK_LIST from '@/graphql/links/mutations/DeleteLinkList.gql';
import LINK_LISTS from '@/graphql/links/queries/LinkLists.gql';
import MOVE_LINK_LIST from '@/graphql/links/mutations/MoveLinkList.gql';
import CREATE_LINK_LIST from '@/graphql/links/mutations/CreateLinkList.gql';
import LinkList from '@/core/models/LinkList.js';
import { instantiate } from '@/core/utils.js';
import LINK_STATS from '@/graphql/links/queries/LinkStats.gql';
import {
    createList,
    deleteList,
    initializeLists,
    moveList, updateList,
} from '@/core/repositories/listRepositoryHelpers.js';

export function createLinkListFromObject(obj) {
    return instantiate(obj, LinkList);
}

export function initializeLinkLists(data) {
    return initializeLists(data, createLinkListFromObject);
}

export function createLinkList(form) {
    return createList(form, CREATE_LINK_LIST, LINK_LISTS, createLinkListFromObject);
}

export function updateLinkList(form, list) {
    return updateList(form, list, UPDATE_LINK_LIST);
}

export function deleteLinkList(list) {
    return deleteList(list, DELETE_LINK_LIST, LINK_LISTS, LINK_STATS);
}

export function moveLinkList(list, previousList = null) {
    return moveList(list, previousList, MOVE_LINK_LIST, LINK_LISTS);
}
