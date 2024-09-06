// Category is fields, tags, relationships, etc...
// and is used for frontend and backend and proper display of dataMap

// subType is MAIN and just used for display and switching designs

export const line1 = {
    id: 'a',
    style: 'line',
    rows: [
        {
            hOrientation: 'center',
            vOrientation: 'center',
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'GRAPHIC',
                    style: {
                        size: 'sm',
                        shape: 'circle',
                    },
                    id: 'a',
                },
                {
                    type: 'row',
                    rows: [
                        {
                            containers: [
                                {
                                    data: null,
                                    subType: 'MAIN',
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        weight: 'bold',
                                        color: 'brand',
                                    },
                                    id: 'b',
                                },
                                {
                                    placement: 'center',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'c',
                                },
                                {
                                    placement: 'end',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'd',
                                },
                            ],
                        },
                    ],
                },
            ],
        },
    ],
};

export const line2 = {
    id: 'b',
    style: 'line',
    rows: [
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    type: 'GRAPHIC',
                    category: null,
                    style: {
                        size: 'sm',
                        shape: 'square',
                    },
                    id: 'a',
                },
                {
                    type: 'row',
                    rows: [
                        {
                            hOrientation: 'end',
                            containers: [
                                {
                                    placement: 'end',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        size: 'sm',
                                    },
                                    id: 'd',
                                },
                            ],
                        },
                        {
                            containers: [
                                {
                                    data: null,
                                    subType: 'MAIN',
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        size: 'lg',
                                        weight: 'bold',
                                        color: 'brand',
                                    },
                                    id: 'b',
                                },
                                {
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        trim: 'hyphen',
                                    },
                                    id: 'c',
                                },
                                {
                                    placement: 'end',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        trim: 'wrap',
                                    },
                                    id: 'dddd',
                                },
                            ],
                        },
                    ],
                },
            ],
        },
    ],
};

export const lineExamples = [
    line1,
    line2,
];

export const card1 = {
    id: 'v',
    style: 'card',
    rows: [
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'GRAPHIC',
                    style: {
                        size: 'xl',
                        shape: 'hRectangle',
                    },
                    id: 'a',
                },
            ],
        },
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'CONTENT',
                    style: {
                        size: 'lg',
                        weight: 'bold',
                        color: 'brand',
                    },
                    id: 'b',
                },
            ],
        },
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'CONTENT',
                    style: {
                        size: 'sm',
                    },
                    id: 'c',
                },
            ],
        },
    ],
};

export const card2 = {
    id: 'gg',
    style: 'card',
    rows: [
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'GRAPHIC',
                    style: {
                        size: 'xl',
                        shape: 'circle',
                    },
                    id: 'a',
                },
            ],
        },
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    type: 'CONTENT',
                    category: null,
                    style: {
                        weight: 'bold',
                        color: 'light',
                    },
                    id: 'b',
                },
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'CONTENT',
                    style: {
                        weight: 'bold',
                    },
                    id: 'iii',
                },
            ],
        },
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'CONTENT',
                    style: {

                    },
                    id: 'c',
                },
            ],
        },
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'CONTENT',
                    style: {

                    },
                    id: 'clll', // cspell:disable-line
                },
            ],
        },
    ],
};

export const cardExamples = [
    card1,
    card2,
];

export const wide1 = {
    id: 'r',
    style: 'wideCard',
    rows: [
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'GRAPHIC',
                    style: {
                        size: 'lg',
                        shape: 'vRectangle',
                    },
                    id: 'a',
                },
                {
                    type: 'row',
                    rows: [
                        {
                            hOrientation: 'end',
                            containers: [
                                {
                                    placement: 'center',
                                    data: null,
                                    category: null,
                                    subType: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'd',
                                },
                            ],
                        },
                        {
                            containers: [
                                {
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'b',
                                },
                                {
                                    placement: 'center',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'c',
                                },
                                {
                                    placement: 'end',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'dd',
                                },
                            ],
                        },
                        {
                            hOrientation: 'end',
                            containers: [
                                {
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        size: 'sm',
                                    },
                                    id: 'ddd',
                                },
                            ],
                        },
                        {
                            hOrientation: 'end',
                            containers: [
                                {
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        size: 'sm',
                                    },
                                    id: 'dddddd',
                                },
                            ],
                        },
                    ],
                },
            ],
        },
    ],
};
export const wide2 = {
    id: 'tasdasd', // cspell:disable-line
    style: 'wideCard',
    rows: [
        {
            containers: [
                {
                    data: null,
                    subType: null,
                    category: null,
                    type: 'GRAPHIC',
                    style: {
                        size: 'lg',
                        shape: 'circle',
                    },
                    id: 'a',
                },
                {
                    type: 'row',
                    rows: [
                        {
                            hOrientation: 'end',
                            containers: [
                                {
                                    placement: 'end',
                                    data: null,
                                    category: null,
                                    subType: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'd',
                                },
                            ],
                        },
                        {
                            containers: [
                                {
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'b',
                                },
                                {
                                    placement: 'center',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'c',
                                },
                                {
                                    placement: 'end',
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {

                                    },
                                    id: 'dd',
                                },
                            ],
                        },
                        {
                            hOrientation: 'end',
                            containers: [
                                {
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        size: 'sm',
                                    },
                                    id: 'ddd',
                                },
                            ],
                        },
                        {
                            hOrientation: 'end',
                            containers: [
                                {
                                    data: null,
                                    subType: null,
                                    category: null,
                                    type: 'CONTENT',
                                    style: {
                                        size: 'sm',
                                    },
                                    id: 'dddddd',
                                },
                            ],
                        },
                    ],
                },
            ],
        },
    ],
};

export const wideExamples = [
    wide1,
    wide2,
];

export const styles = {
    line: {
        grid: '',
        single: 'w-full',
        name: 'Line',
        examples: lineExamples,
    },
    card: {
        grid: 'grid-fill-card',
        single: 'max-w-xs w-full',
        name: 'Card',
        examples: cardExamples,
    },
    wideCard: {
        grid: 'grid-fill-wide',
        single: 'max-w-lg w-full',
        name: 'Wide card',
        examples: wideExamples,
    },
};

export default {

};
