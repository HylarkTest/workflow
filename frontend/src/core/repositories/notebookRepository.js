import UPDATE_NOTEBOOK from '@/graphql/notes/mutations/UpdateNotebook.gql';
import DELETE_NOTEBOOK from '@/graphql/notes/mutations/DeleteNotebook.gql';
import NOTEBOOKS from '@/graphql/notes/queries/Notebooks.gql';
import NOTE_STATS from '@/graphql/notes/queries/NoteStats.gql';
import MOVE_NOTEBOOK from '@/graphql/notes/mutations/MoveNotebook.gql';
import CREATE_NOTEBOOK from '@/graphql/notes/mutations/CreateNotebook.gql';
import Notebook from '@/core/models/Notebook.js';
import { instantiate } from '@/core/utils.js';
import {
    createList,
    deleteList,
    initializeLists, moveList,
    updateList,
} from '@/core/repositories/listRepositoryHelpers.js';

export function createNotebookFromObject(obj) {
    return instantiate(obj, Notebook);
}

export function initializeNotebooks(data) {
    return initializeLists(data, createNotebookFromObject);
}

export function createNotebook(form) {
    return createList(form, CREATE_NOTEBOOK, NOTEBOOKS, createNotebookFromObject);
}

export function updateNotebook(form, list) {
    return updateList(form, list, UPDATE_NOTEBOOK);
}

export function deleteNotebook(list) {
    return deleteList(list, DELETE_NOTEBOOK, NOTEBOOKS, NOTE_STATS);
}

export function moveNotebook(list, previousList = null) {
    return moveList(list, previousList, MOVE_NOTEBOOK, NOTEBOOKS);
}
