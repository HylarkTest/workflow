const corsAnywhere = require('./cors-anywhere');
const dotenv = require('dotenv').config({ path: __dirname+'/../.env' });
const variableExpansion = require('dotenv-expand');
variableExpansion.expand(dotenv);

const host = '0.0.0.0';
const port = 6789;

const originWhitelist = [
    process.env.APP_URL,
];

if (process.env.APP_ENV === 'local') {
    originWhitelist.push(`http://dev.${process.env.DOMAIN}:8080`);
}

corsAnywhere.createServer({
    originWhitelist,
    requireHeader: ['origin'],
    removeHeaders: ['cookie', 'cookie2']
}).listen(port, host, function() {
    console.log('Running CORS Anywhere on ' + host + ':' + port);
});
