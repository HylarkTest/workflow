// eslint-disable-next-line import/no-extraneous-dependencies
import plugin from 'tailwindcss/plugin';

const colors = {
    f4: '#f4f4f4',
};

const intensities = ['00', '50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950', '1000'];

const percentages = ['10', '20', '30', '40', '50', '60', '70', '80', '90'];

const accentColorComponent = {};
const supportColors = ['gold', 'turquoise', 'violet', 'sky', 'rose', 'peach', 'emerald', 'azure'];

[...intensities, 'main'].forEach((intensity) => {
    accentColorComponent[`.bg-cm-${intensity}`] = { backgroundColor: `var(--hl-cm-color-${intensity})` };
    accentColorComponent[`.text-cm-${intensity}`] = { color: `var(--hl-cm-color-${intensity})` };
    accentColorComponent[`.border-cm-${intensity}`] = { borderColor: `var(--hl-cm-color-${intensity})` };

    ['primary', 'secondary'].forEach((primary) => {
        accentColorComponent[`.bg-${primary}-${intensity}`] = {
            backgroundColor: `var(--hl-${primary}-color-${intensity})`,
        };
        accentColorComponent[`.text-${primary}-${intensity}`] = {
            color: `var(--hl-${primary}-color-${intensity})`,
        };
        accentColorComponent[`.border-${primary}-${intensity}`] = {
            borderColor: `var(--hl-${primary}-color-${intensity})`,
        };
        accentColorComponent[`.hover\\:bg-${primary}-${intensity}:hover:not(.no-color-hover)`] = {
            backgroundColor: `var(--hl-${primary}-color-${intensity}) !important`,
        };
        accentColorComponent[`.hover\\:text-${primary}-${intensity}:hover:not(.no-color-hover)`] = {
            color: `var(--hl-${primary}-color-${intensity}) !important`,
        };
        accentColorComponent[`.to-${primary}-${intensity}`] = {
            '--tw-gradient-to': `var(--hl-${primary}-color-${intensity})`,
        };
        accentColorComponent[`.via-${primary}-${intensity}`] = {
            // eslint-disable-next-line max-len,vue/max-len
            '--tw-gradient-stops': `var(--tw-gradient-from), var(--hl-${primary}-color-${intensity}), var(--tw-gradient-to, var(--hl-${primary}-color-${intensity}))`,
        };
        accentColorComponent[`.from-${primary}-${intensity}`] = {
            '--tw-gradient-from': `var(--hl-${primary}-color-${intensity})`,
            // eslint-disable-next-line max-len,vue/max-len
            '--tw-gradient-stops': `var(--tw-gradient-from), var(--tw-gradient-to, var(--hl-${primary}-color-${intensity}))`,
        };
        accentColorComponent[`.shadow-${primary}-${intensity}`] = {
            '--tw-shadow-color': `var(--hl-${primary}-color-${intensity})`,
            '--tw-shadow': 'var(--tw-shadow-colored) !important',
        };

        percentages.forEach((percentage) => {
            accentColorComponent[`.shadow-${primary}-${intensity}\\/${percentage}`] = {
                '--tw-shadow-color': `var(--hl-${primary}-color-${intensity}-${percentage})`,
                '--tw-shadow': 'var(--tw-shadow-colored) !important',
            };
        });
    });

    supportColors.forEach((name) => {
        if (!colors[name]) {
            colors[name] = {};
        }
        colors[name][intensity] = `var(--hl-color-${name}-${intensity})`;
        accentColorComponent[`.shadow-${name}-${intensity}`] = {
            '--tw-shadow-color': `var(--hl-color-${name}-${intensity})`,
            '--tw-shadow': 'var(--tw-shadow-colored) !important',
        };

        percentages.forEach((percentage) => {
            accentColorComponent[`.shadow-${name}-${intensity}\\/${percentage}`] = {
                '--tw-shadow-color': `var(--hl-color-${name}-${intensity}-${percentage})`,
                '--tw-shadow': 'var(--tw-shadow-colored) !important',
            };
        });
    });
});

const tailwindColors = Object.keys(colors)
    .concat([
        'black', 'white', 'primary', 'secondary',
    ]);


module.exports = {
    theme: {
        extend: {
            colors,
        },
    },
    safelist: tailwindColors.map((c) => ({
        pattern: new RegExp(`\\b${c}\\b`),
    })).concat(['body']).concat(
        percentages.flatMap((p) => {
            return intensities.flatMap((i) => {
                return tailwindColors.map((c) => `shadow-${c}-${i}/${p}`).concat(
                    tailwindColors.map((c) => `hover:bg-${c}-${i}`)
                );
            });
        })
    ),
    plugins: [
        plugin(({ addComponents }) => {
            addComponents(accentColorComponent);
        }),
    ],
    accentColorComponent,
};
