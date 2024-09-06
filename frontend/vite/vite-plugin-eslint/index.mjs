import { resolve } from 'path'
import { Worker } from 'worker_threads';
import { checkModule, isVirtualModule, parseRequest, pickESLintOptions, to } from './utils.mjs'
import { ESLint } from 'eslint';
import { createFilter } from '@rollup/pluginutils';

export default function eslintPlugin(rawOptions = {}) {
    let eslint;
    let filter;
    let options;
    let outputFixes;
    // If cache is true, it will save all path.
    let fileCache;
    let workers = [];
    let formatter;
    let workerIndex = 0;

    return {
        name: 'eslint',
        async configResolved(config) {
            options = Object.assign(
                {
                    include: ['**/*.js', '**/*.jsx', '**/*.ts', '**/*.tsx', '**/*.vue', '**/*.svelte'],
                    exclude: ['**/node_modules/**'],
                    // Use vite cacheDir as default
                    cacheLocation: resolve(config.cacheDir, '.eslintcache'),
                    formatter: 'stylish',
                    emitWarning: true,
                    emitError: true,
                    failOnWarning: false,
                    failOnError: true,
                    errorOnUnmatchedPattern: false,
                    async: true,
                    shouldNotify: true,
                    workers: 4,
                },
                rawOptions
            )
        },
        async buildStart() {
            const eslintOptions = pickESLintOptions(options)

            if (options.async) {
                for (let i = 0; i < options.workers; i++) {
                    workers.push(new Worker(resolve(__dirname, 'worker.mjs'), {
                        workerData: {
                            eslintOptions,
                            options,
                        }
                    }));
                }
            } else {
                eslint = new ESLint(eslintOptions);
                outputFixes = ESLint.outputFixes;
                filter = createFilter(options.include, options.exclude)
                formatter = (await eslint.loadFormatter(options.formatter)).format;
                fileCache = new Set();
            }
        },
        async transform(_, id) {
            const path = parseRequest(id)

            if (options.async) {
                workers[workerIndex].postMessage(path);
                workerIndex = (workerIndex + 1) % options.workers;
            } else {
                const isVirtual = isVirtualModule(path)

                if (isVirtual && fileCache.has(path)) {
                    fileCache.delete(path)
                }

                if (isVirtual || !filter(path) || (await eslint.isPathIgnored(path))) {
                    return;
                }

                if (options.cache) {
                    fileCache.add(path)
                }

                const [error] = await to(
                    checkModule(
                        // this,
                        console,
                        eslint,
                        options.cache ? Array.from(fileCache) : path,
                        options,
                        formatter,
                        outputFixes,
                    )
                );

                if (error) {
                    this.error(error);
                }
            }

            return null
        },
    }
}
