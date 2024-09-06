import { computed } from 'vue';

import useTipTapEditor from './useTipTapEditor.js';
import useTipTapEditorColors from './useTipTapEditorColors.js';

import { $t } from '@/i18n.js';

export default (props, openModal) => {
    const {
        isActiveNode,
        runCommands,
        getAttribute,
        getSelection,
    } = useTipTapEditor(props);

    const {
        colorOptions,
    } = useTipTapEditorColors(props);

    const allOptions = computed(() => {
        return {
            HEADING: {
                component: 'TipTapHeading',
                isGroupDeactivated: isActiveNode('codeBlock'),
                options: [
                    {
                        key: 'paragraph',
                        text: $t('tiptap.paragraph'),
                        isActive: isActiveNode('paragraph'),
                        action: () => runCommands((commands) => commands.setParagraph()),
                    },
                    ...[1, 2, 3, 4, 5, 6].map((level) => {
                        return {
                            key: `heading${level}`,
                            text: $t(`tiptap.heading${level}`),
                            isActive: isActiveNode('heading', { level }),
                            action: () => runCommands((commands) => commands.toggleHeading(level)),
                        };
                    }),
                ],
            },
            STYLE: {
                component: 'TipTapButtonGroup',
                options: [
                    {
                        key: 'bold',
                        text: $t('tiptap.bold'),
                        icon: 'fal fa-bold',
                        isActive: isActiveNode('bold'),
                        action: () => runCommands((commands) => commands.toggleBold()),
                    },
                    {
                        key: 'italic',
                        text: $t('tiptap.italic'),
                        icon: 'fal fa-italic',
                        isActive: isActiveNode('italic'),
                        action: () => runCommands((commands) => commands.toggleItalic()),
                    },
                    {
                        key: 'underline',
                        text: $t('tiptap.underline'),
                        icon: 'fal fa-underline',
                        isActive: isActiveNode('underline'),
                        action: () => runCommands((commands) => commands.toggleUnderline()),
                    },
                    {
                        key: 'strike',
                        text: $t('tiptap.strike'),
                        icon: 'fal fa-strikethrough',
                        isActive: isActiveNode('strike'),
                        action: () => runCommands((commands) => commands.toggleStrike()),
                    },
                ],
            },
            COLOR: {
                component: 'TipTapColor',
                isGroupDeactivated: isActiveNode('codeBlock'),
                options: colorOptions.value,
            },
            SUPERSCRIPT: {
                component: 'TipTapButtonGroup',
                isGroupDeactivated: isActiveNode('codeBlock'),
                options: [
                    {
                        key: 'superscript',
                        text: $t('tiptap.superscript'),
                        icon: 'fal fa-superscript',
                        isActive: isActiveNode('superscript'),
                        action: () => runCommands((commands) => {
                            commands.unsetSubscript();
                            commands.toggleSuperscript();
                        }),
                    },
                    {
                        key: 'subscript',
                        text: $t('tiptap.subscript'),
                        icon: 'fal fa-subscript',
                        isActive: isActiveNode('subscript'),
                        action: () => runCommands((commands) => {
                            commands.unsetSuperscript();
                            commands.toggleSubscript();
                        }),
                    },
                ],
            },
            ALIGNMENT: {
                component: 'TipTapButtonGroup',
                isGroupDeactivated: isActiveNode('codeBlock'),
                options: [
                    {
                        key: 'left',
                        text: $t('tiptap.alignLeft'),
                        icon: 'fal fa-align-left',
                        isActive: isActiveNode({ alignment: 'left' }),
                        action: () => runCommands((commands) => commands.alignText('left')),
                    },
                    {
                        key: 'center',
                        text: $t('tiptap.alignCenter'),
                        icon: 'fal fa-align-center',
                        isActive: isActiveNode({ alignment: 'center' }),
                        action: () => runCommands((commands) => commands.alignText('center')),
                    },
                    {
                        key: 'right',
                        text: $t('tiptap.alignRight'),
                        icon: 'fal fa-align-right',
                        isActive: isActiveNode({ alignment: 'right' }),
                        action: () => runCommands((commands) => commands.alignText('right')),
                    },
                ],
            },
            INDENT: {
                component: 'TipTapButtonGroup',
                isGroupDeactivated: isActiveNode({ alignment: 'center' }),
                options: [
                    {
                        key: 'indent',
                        text: $t('tiptap.indent'),
                        icon: 'fal fa-indent',
                        action: () => runCommands((commands) => commands.incrementIndent()),
                    },
                    {
                        key: 'outdent',
                        text: $t('tiptap.outdent'),
                        icon: 'fal fa-outdent',
                        action: () => runCommands((commands) => commands.decrementIndent()),
                    },
                ],
            },
            BLOCK: {
                component: 'TipTapButtonGroup',
                options: [
                    {
                        key: 'blockquote',
                        text: $t('tiptap.blockquote'),
                        icon: 'fal fa-message-quote',
                        isActive: isActiveNode('blockquote'),
                        action: () => runCommands((commands) => {
                            commands.alignText('left');
                            commands.toggleBlockquote();
                        }),
                    },
                    {
                        key: 'codeblock',
                        text: $t('tiptap.codeblock'),
                        icon: 'fal fa-code',
                        isActive: isActiveNode('codeBlock'),
                        action: () => runCommands((commands) => {
                            commands.alignText('left');
                            commands.unsetAllMarks();
                            commands.toggleCodeBlock();
                        }),
                    },
                    {
                        key: 'hyperlink',
                        text: $t('tiptap.hyperlink'),
                        icon: 'fal fa-link-horizontal', // different from Link feature
                        isActive: isActiveNode('link'),
                        isDeactivated: isActiveNode('codeBlock'),
                        action: () => {
                            const previousUrl = getAttribute('link', 'href');
                            openModal({
                                modalComponent: 'TipTapHyperlinkModal',
                                props: { previousUrl, selection: getSelection() },
                            });
                        },
                    },
                ],
            },
            LIST: {
                component: 'TipTapButtonGroup',
                isGroupDeactivated: isActiveNode('codeBlock'),
                options: [
                    {
                        key: 'bulletList',
                        textPath: 'tiptap.unorderedList',
                        icon: 'fal fa-list',
                        isActive: !isActiveNode({ hasBullet: false }),
                        action: () => runCommands((commands) => commands.toggleBullet()),
                    },
                ],
            },
            RESET: {
                component: 'TipTapButtonGroup',
                options: [
                    {
                        key: 'clearFormatting',
                        textPath: 'tiptap.clearFormatting',
                        icon: 'fal fa-text-slash',
                        action: () => {
                            runCommands((commands) => {
                                commands.unsetAllMarks();
                                commands.unsetIndent();
                                commands.unsetBullet();
                                commands.unsetAlignment();
                                commands.setParagraph();
                            });
                            // "blockquote" is a block Node that contains other block Nodes.
                            // when setting selected Nodes to "paragraph", if a "blockquote" is selected
                            // we need to resolve its content to a paragraph first,
                            // and then resolve the blockquote separately to return to a single-depth "paragraph".
                            runCommands((commands) => {
                                if (isActiveNode('blockquote')) {
                                    commands.setParagraph();
                                }
                            });
                        },
                    },
                ],
            },
        };
    });

    const getOptionsGroup = (optionKey) => allOptions.value[optionKey].options;

    return {
        getOptionsGroup,
        allOptions,
    };
};
