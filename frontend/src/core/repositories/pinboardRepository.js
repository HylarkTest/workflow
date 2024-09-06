import UPDATE_PINBOARD from '@/graphql/pinboard/mutations/UpdatePinboard.gql';
import DELETE_PINBOARD from '@/graphql/pinboard/mutations/DeletePinboard.gql';
import PINBOARDS from '@/graphql/pinboard/queries/Pinboards.gql';
import PIN_STATS from '@/graphql/pinboard/queries/PinStats.gql';
import MOVE_PINBOARD from '@/graphql/pinboard/mutations/MovePinboard.gql';
import CREATE_PINBOARD from '@/graphql/pinboard/mutations/CreatePinboard.gql';
import Pinboard from '@/core/models/Pinboard.js';
import { instantiate } from '@/core/utils.js';
import {
    createList,
    deleteList,
    initializeLists, moveList,
    updateList,
} from '@/core/repositories/listRepositoryHelpers.js';

export function createPinboardFromObject(obj) {
    return instantiate(obj, Pinboard);
}

export function initializePinboards(data) {
    return initializeLists(data, createPinboardFromObject);
}

export function createPinboard(form) {
    return createList(form, CREATE_PINBOARD, PINBOARDS, createPinboardFromObject);
}

export function updatePinboard(form, list) {
    return updateList(form, list, UPDATE_PINBOARD);
}

export function deletePinboard(list) {
    return deleteList(list, DELETE_PINBOARD, PINBOARDS, PIN_STATS);
}

export function movePinboard(list, previousList = null) {
    return moveList(list, previousList, MOVE_PINBOARD, PINBOARDS);
}
