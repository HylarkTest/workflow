/**
 * @property {string} id
 * @property {string} createdAt
 * @property {string} updatedAt
 * @property {string} __typename
 */
export default class Model {
    is(model) {
        return model && model.id === this.id;
    }

    // eslint-disable-next-line class-methods-use-this
    hasActivity() {
        return true;
    }
}
