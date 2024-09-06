import { existsSync } from 'fs';
import { resolve } from 'path';
import notifier from 'node-notifier';

const stripAnsiCodes = (str) => str.replace(/[\u001b\u009b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/g, '');

export function parseRequest(id) {
    return id.split('?', 2)[0];
}

export function isVirtualModule(file) {
    return !existsSync(file);
}

export function notify(title, message) {
    notifier.notify({
        title,
        message: stripAnsiCodes(message),
        icon: false,
    });
}

export function pickESLintOptions(options) {
    const {
        /* eslint-disable @typescript-eslint/no-unused-vars */
        eslintPath,
        lintOnStart,
        include,
        exclude,
        formatter,
        emitWarning,
        emitError,
        failOnWarning,
        failOnError,
        async,
        shouldNotify,
        workers,
        /* eslint-enable @typescript-eslint/no-unused-vars */
        ...eslintOptions
    } = options;

    return eslintOptions;
}

export async function to(promise) {
    return promise
        .then((data) => [null, data])
        .catch((error) => [error, undefined]);
}

export async function checkModule(
    ctx,
    eslint,
    files,
    options,
    formatter,
    outputFixes,
) {
    const [error, report] = await to(eslint.lintFiles(files));

    if (error) {
        return Promise.reject(error);
    }

    const hasWarning = report.some((item) => item.warningCount > 0);
    const hasError = report.some((item) => item.errorCount > 0);
    const result = formatter(report);

    // Auto fix error
    if (options.fix && report) {
        const [error] = await to(outputFixes(report));

        if (error) {
            return Promise.reject(error);
        }
    }

    // Throw warning message
    if (hasWarning && options.emitWarning) {
        const warning = typeof result === 'string' ? result : await result;

        if (options.shouldNotify) {
            notify('ESLint Warning', warning);
        }
        if (options.failOnWarning) {
            ctx.error(warning);
        } else {
            ctx.warn(warning);
        }
    }

    // Throw error message
    if (hasError && options.emitError) {
        const error = typeof result === 'string' ? result : await result;

        if (options.shouldNotify) {
            notify('ESLint Error', error);
        }
        if (options.failOnError) {
            ctx.error(error);
        } else {
            console.log(error);
        }
    }

    return Promise.resolve();
}
