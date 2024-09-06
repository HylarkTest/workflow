import { gql } from '@apollo/client';

export function updatePossibleTypes(apolloClient) {
    apolloClient.query({
        query: gql`
            {
                __type(name: "Item") {
                    kind
                    name
                    possibleTypes {
                        name
                    }
                }
            }
        `,
        fetchPolicy: 'no-cache',
    }).then((result) => {
        // eslint-disable-next-line no-param-reassign
        apolloClient.cache.config.possibleTypes.Item = result.data.__type.possibleTypes.map(({ name }) => name);
    });
}

export function resetPossibleTypes(apolloClient) {
    // eslint-disable-next-line no-param-reassign
    delete apolloClient.cache.config.possibleTypes;
}
