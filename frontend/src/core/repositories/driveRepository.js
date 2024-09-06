import UPDATE_DRIVE from '@/graphql/documents/mutations/UpdateDrive.gql';
import DELETE_DRIVE from '@/graphql/documents/mutations/DeleteDrive.gql';
import DRIVES from '@/graphql/documents/queries/Drives.gql';
import DRIVE_STATS from '@/graphql/documents/queries/DocumentStats.gql';
import MOVE_DRIVE from '@/graphql/documents/mutations/MoveDrive.gql';
import CREATE_DRIVE from '@/graphql/documents/mutations/CreateDrive.gql';
import Drive from '@/core/models/Drive.js';
import { instantiate } from '@/core/utils.js';
import {
    createList,
    deleteList,
    initializeLists,
    moveList,
    updateList,
} from '@/core/repositories/listRepositoryHelpers.js';

export function createDriveFromObject(obj) {
    return instantiate(obj, Drive);
}

export function initializeDrives(data) {
    return initializeLists(data, createDriveFromObject);
}

export function createDrive(form) {
    return createList(form, CREATE_DRIVE, DRIVES, createDriveFromObject);
}

export function updateDrive(form, list) {
    return updateList(form, list, UPDATE_DRIVE);
}

export function deleteDrive(list) {
    return deleteList(list, DELETE_DRIVE, DRIVES, DRIVE_STATS);
}

export function moveDrive(list, previousList = null) {
    return moveList(list, previousList, MOVE_DRIVE, DRIVES);
}
