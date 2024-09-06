import { property } from 'lodash';
import CREATE_MAPPING from '@/graphql/mappings/mutations/CreateMapping.gql';
import UPDATE_MAPPING from '@/graphql/mappings/mutations/UpdateMapping.gql';
import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';
import MAPPING from '@/graphql/mappings/queries/Mapping.gql';
import {
    addNodeToQueryConnectionCallback,
} from '@/core/helpers/apolloHelpers.js';
import UPDATE_MAPPING_RELATIONSHIP from '@/graphql/mappings/mutations/UpdateMappingRelationship.gql';
import CREATE_MAPPING_RELATIONSHIP from '@/graphql/mappings/mutations/CreateMappingRelationship.gql';
import DELETE_MAPPING_RELATIONSHIP from '@/graphql/mappings/mutations/DeleteMappingRelationship.gql';
import CREATE_MAPPING_FIELD from '@/graphql/mappings/mutations/CreateMappingField.gql';
import UPDATE_MAPPING_FIELD from '@/graphql/mappings/mutations/UpdateMappingField.gql';
import DELETE_MAPPING_FIELD from '@/graphql/mappings/mutations/DeleteMappingField.gql';
import CREATE_MAPPING_SECTION from '@/graphql/mappings/mutations/CreateMappingSection.gql';
import UPDATE_MAPPING_SECTION from '@/graphql/mappings/mutations/UpdateMappingSection.gql';
import DELETE_MAPPING_SECTION from '@/graphql/mappings/mutations/DeleteMappingSection.gql';
import { getValidationMessages, isValidationError } from '@/http/checkResponse.js';
import { validationFeedback } from '@/core/uiGenerators/userFeedbackGenerators.js';
import { baseApolloClient } from '@/core/repositories/baseRepository.js';
import { createApolloForm } from '@/core/plugins/formlaPlugin.js';

export function createMapping(form, space) {
    return form.graphql(
        CREATE_MAPPING,
        {
            formatData(data) {
                return {
                    spaceId: space.id,
                    ...data,
                    fields: (data.fields || []).map((field) => _.omit(field, ['val', 'nameKey', 'exampleKey'])),
                    features: (data.features || []).map((feature) => _.omit(feature, ['relatesTo'])),
                };
            },
            update: addNodeToQueryConnectionCallback(
                { query: MAPPINGS },
                'createMapping.mapping',
                'mappings'
            ),
            refetchQueries: [MAPPINGS],
        }
    ).then(property('data.createMapping'));
}

export function updateMapping(form) {
    return form.graphql(
        UPDATE_MAPPING
    ).then(property('data.updateMapping'));
}

export function updateMappingFeatures(mapping, newFeatures) {
    return updateMapping(createApolloForm(baseApolloClient(), {
        id: mapping.id,
        features: newFeatures.map((feature) => (_.isString(feature)
            ? { val: feature }
            : _.pick(feature, ['val', 'options']))),
    }));
}

export function updateMappingMarkerGroups(mapping, newMarkerGroups) {
    return updateMapping(createApolloForm(baseApolloClient(), {
        id: mapping.id,
        markerGroups: newMarkerGroups.map((markerGroup) => (_.isString(markerGroup)
            ? { group: markerGroup }
            : _.pick(markerGroup, ['group']))),
    }));
}

export function createMappingRelationship(form) {
    return form.graphql(CREATE_MAPPING_RELATIONSHIP);
}

export function updateMappingRelationship(form) {
    return form.graphql(UPDATE_MAPPING_RELATIONSHIP);
}

export function deleteMappingRelationship(mapping, relationship) {
    return baseApolloClient().mutate({
        mutation: DELETE_MAPPING_RELATIONSHIP,
        variables: { input: { mappingId: mapping.id, id: relationship.id } },
        update: (cache) => {
            const inverse = relationship.to;

            const inverseMapping = cache.readQuery({
                query: MAPPING,
                variables: { id: inverse.id },
            });

            if (inverseMapping) {
                cache.writeQuery({
                    query: MAPPING,
                    variables: { id: inverse.id },
                    data: {
                        ...inverseMapping,
                        mapping: {
                            ...inverseMapping.mapping,
                            relationships: inverseMapping.mapping.relationships.filter(
                                (rel) => rel.id !== relationship.id
                            ),
                        },
                    },
                });
            }
        },
    });
}

export function createMappingField(form) {
    return form.graphql(
        CREATE_MAPPING_FIELD,
        {
            formatData(data) {
                return _.omit(data, 'val');
            },
        }
    );
}

export function updateMappingField(form) {
    return form.graphql(UPDATE_MAPPING_FIELD);
}

export function deleteMappingField(mapping, field) {
    return baseApolloClient().mutate({
        mutation: DELETE_MAPPING_FIELD,
        variables: { input: { mappingId: mapping.id, id: field.id } },
    }).catch((error) => {
        if (isValidationError(error)) {
            const messages = getValidationMessages(error);
            if (messages['input.id']) {
                validationFeedback(messages['input.id']);
            }
        }
        return Promise.reject(error);
    });
}

export function createMappingSection(mapping, sectionName) {
    return baseApolloClient().mutate({
        mutation: CREATE_MAPPING_SECTION,
        variables: { input: { mappingId: mapping.id, name: sectionName } },
    }).then(property('data.createMappingSection'));
}

export function updateMappingSection(form) {
    return form.graphql(UPDATE_MAPPING_SECTION);
}

export function deleteMappingSection(mapping, section) {
    return baseApolloClient().mutate({
        mutation: DELETE_MAPPING_SECTION,
        variables: { input: { mappingId: mapping.id, id: section.id } },
    });
}
