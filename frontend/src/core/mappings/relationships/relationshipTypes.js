const relationshipTypes = [
    {
        text: 'One to one',
        to: {
            plural: false,
            text: 'One',
        },
        from: {
            plural: false,
            text: 'One',
        },
        type: 'ONE_TO_ONE',
    },
    {
        text: 'Many to many',
        to: {
            plural: true,
            text: 'Many',
        },
        from: {
            plural: true,
            text: 'Many',
        },
        type: 'MANY_TO_MANY',
    },
    {
        text: 'One to many',
        to: {
            plural: false,
            text: 'Many',
        },
        from: {
            plural: true,
            text: 'One',
        },
        type: 'ONE_TO_MANY',
    },
    {
        text: 'Many to one',
        to: {
            plural: true,
            text: 'One',
        },
        from: {
            plural: false,
            text: 'Many',
        },
        type: 'MANY_TO_ONE',
    },
];

export default relationshipTypes;
