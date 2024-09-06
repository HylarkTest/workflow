import { gql } from '@apollo/client';
import PARSE_IMPORT_FILE from '@/graphql/imports/ParseImportFile.gql';
import IMPORT_FILE from '@/graphql/imports/ImportFile.gql';
import IMPORT from '@/graphql/imports/Import.gql';
import CANCEL_IMPORT from '@/graphql/imports/CancelImport.gql';
import REVERT_IMPORT from '@/graphql/imports/RevertImport.gql';
import { buildPreviewItemFragment } from '@/http/apollo/buildMappingRequests.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';

export function parseImportFile(form) {
    return form.post({
        query: PARSE_IMPORT_FILE,
        formatData(data) {
            return data.fileId ? _.omit(data, 'file') : data;
        },
        maxUpload: 10,
    }).then(({ data }) => data.parseSpreadsheet.data);
}

export function importFile(form) {
    return form.post({
        query: IMPORT_FILE,
        formatData(data) {
            return _.omit(data, 'fileId');
            // return data.fileId ? _.omit(data, 'file') : data;
        },
        maxUpload: 10,
        update(cache, response) {
            cache.writeQuery({
                query: IMPORT,
                variables: { id: response.data.importSpreadsheet.import.id },
                data: { import: response.data.importSpreadsheet.import },
            });
        },
    }).then(({ data }) => data.importSpreadsheet.import);
}

export function previewFile(form, mapping, page) {
    return form.post({
        query: gql`
            mutation ${mapping.apiName}Preview($input: PreviewSpreadsheetInput!) {
                items {
                    ${mapping.apiName} {
                        preview${_.upperFirst(mapping.apiName)}(input: $input) {
                            ${mapping.apiName}(page: ${page}) {
                                data {
                                    ${buildPreviewItemFragment(mapping)}
                                    errors {
                                        row
                                        column
                                        fieldId
                                        errors
                                        value
                                    }
                                }
                                errors {
                                    row
                                    error
                                    path
                                }
                                pageInfo {
                                    hasMorePages
                                    currentPage
                                }
                            }
                        }
                    }
                }
            }
        `,
        formatData(data) {
            return data.fileId ? _.omit(data, 'file') : data;
        },
        maxUpload: 10,
        fetchPolicy: 'no-cache',
    }).then(({ data }) => {
        return data.items[`${mapping.apiName}`][`preview${_.upperFirst(mapping.apiName)}`][`${mapping.apiName}`];
    });
}

export function cancelImport(importId) {
    return baseApolloClient().mutate({
        mutation: CANCEL_IMPORT,
        variables: { input: { id: importId } },
    });
}

export function revertImport(importId) {
    return baseApolloClient().mutate({
        mutation: REVERT_IMPORT,
        variables: { input: { id: importId } },
    });
}
