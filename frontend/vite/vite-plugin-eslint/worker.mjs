import { workerData, parentPort } from 'worker_threads';
import { ESLint } from 'eslint';
import { createFilter } from '@rollup/pluginutils';
import { checkModule, isVirtualModule, to } from './utils.mjs';

const eslint = new ESLint(workerData.eslintOptions);
const outputFixes = ESLint.outputFixes;
const filter = createFilter(workerData.options.include, workerData.options.exclude)
const formatter = (await eslint.loadFormatter(workerData.options.formatter)).format;
const fileCache = new Set();

parentPort.on('message', async (path) => {
    const isVirtual = isVirtualModule(path)

    if (isVirtual && fileCache.has(path)) {
        fileCache.delete(path)
    }

    if (isVirtual || !filter(path) || (await eslint.isPathIgnored(path))) {
        return;
    }

    if (workerData.options.cache) {
        fileCache.add(path)
    }

    const [error] = await to(
        checkModule(
            // this,
            console,
            eslint,
            workerData.options.cache ? Array.from(fileCache) : path,
            workerData.options,
            formatter,
            outputFixes,
        )
    );

    if (error) {
        console.log(error);
    }
});
