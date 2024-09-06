const fs = require('fs');

const schema = JSON.parse(fs.readFileSync(__dirname+'/../schema.json', 'utf8'));

const possibleTypes = {};

schema.__schema.types.forEach((superType) => {
    if (superType.possibleTypes && superType.possibleTypes.length) {
        possibleTypes[superType.name] = superType.possibleTypes.map(({ name }) => name);
    }
});

fs.writeFileSync(__dirname+'/../possibleTypes.json', JSON.stringify(possibleTypes));
