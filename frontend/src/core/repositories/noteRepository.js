import NOTES from '@/graphql/notes/queries/Notes.gql';
import GROUPED_NOTES from '@/graphql/notes/queries/GroupedNotes.gql';
import NOTEBOOKS from '@/graphql/notes/queries/Notebooks.gql';
import UPDATE_NOTE from '@/graphql/notes/mutations/UpdateNote.gql';
import CREATE_NOTE from '@/graphql/notes/mutations/CreateNote.gql';
import DELETE_NOTE from '@/graphql/notes/mutations/DeleteNote.gql';
import DUPLICATE_NOTE from '@/graphql/notes/mutations/DuplicateNote.gql';
import MOVE_NOTE from '@/graphql/notes/mutations/MoveNote.gql';
import { instantiate } from '@/core/utils.js';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import Note from '@/core/models/Note.js';
import NOTE_STATS from '@/graphql/notes/queries/NoteStats.gql';
import eventBus, { dispatchPromise } from '@/core/eventBus.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { getCachedOperationNames } from '@/core/helpers/apolloHelpers.js';

export const NOTE_CREATED = Symbol('Note created');
export const NOTE_UPDATED = Symbol('Note updated');
export const NOTE_DELETED = Symbol('Note deleted');

export function createNoteFromObject(obj) {
    return instantiate(obj, Note);
}

export function initializeNotes(data) {
    return _.getFirstKey(initializeConnections(data));
}

export function moveNoteToList(note, list) {
    const client = baseApolloClient();
    client.mutate({
        mutation: MOVE_NOTE,
        variables: {
            input: {
                notebookId: list.id,
                id: note.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            NOTEBOOKS,
            NOTES,
        ], client),
    });
}

export function updateNote(form) {
    return dispatchPromise(form.graphql(
        UPDATE_NOTE
    ), NOTE_UPDATED, 'data.updateNote.note');
}

export function duplicateNote(note, data) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DUPLICATE_NOTE,
        variables: {
            input: {
                id: note.id,
                ...data,
            },
        },
        refetchQueries: getCachedOperationNames([
            NOTES,
            GROUPED_NOTES,
            NOTEBOOKS,
            NOTE_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(NOTE_CREATED, result.data.duplicateNote.note);
        return result;
    });
}

export function changeNotebook(note, notebookId) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_NOTE,
        variables: {
            input: {
                notebookId,
                id: note.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            GROUPED_NOTES,
            NOTEBOOKS,
            NOTES,
        ], client),
    });
}

export function createNote(form) {
    return dispatchPromise(form.graphql(CREATE_NOTE, {
        refetchQueries: [
            NOTES,
            GROUPED_NOTES,
            NOTEBOOKS,
            NOTE_STATS,
        ],
        formatData(data) {
            return {
                ...data,
                associations: data.associations.map((item) => item?.id || item),
                assigneeGroups: (data.assigneeGroups || []).map((assigneeGroup) => ({
                    groupId: assigneeGroup.groupId,
                    assignees: _.map(assigneeGroup.assignees, 'id'),
                })),
            };
        },
    }), NOTE_CREATED, 'data.createNote.note');
}

export function deleteNote(note) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: DELETE_NOTE,
        variables: {
            input: {
                id: note.id,
            },
        },
        refetchQueries: getCachedOperationNames([
            NOTES,
            GROUPED_NOTES,
            NOTEBOOKS,
            NOTE_STATS,
        ], client),
    }).then((result) => {
        eventBus.dispatch(NOTE_DELETED, note);
        return result;
    });
}

export function toggleFavorite(note) {
    const client = baseApolloClient();
    return client.mutate({
        mutation: UPDATE_NOTE,
        variables: {
            input: {
                id: note.id,
                isFavorite: !note.isFavorite,
            },
        },
        optimisticResponse: {
            updateNote: {
                code: '200',
                note: {
                    ...note,
                    isFavorite: !note.isFavorite,
                },
            },
        },
        refetchQueries: getCachedOperationNames([
            NOTE_STATS,
        ], client),
    });
}
