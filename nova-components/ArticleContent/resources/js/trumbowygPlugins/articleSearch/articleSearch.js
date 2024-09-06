/*
 * Documentation for writing Trumbowyg plugins can be found here:
 * https://alex-d.github.io/Trumbowyg/documentation/plugins/#create-your-own
 */
(function ($) {
    'use strict';

    let articles = [];

    // Fetching articles using ArticleController@index
    // This just returns an array of articles with id, title, and url
    fetch('/nova-vendor/hylark/article-content/articles', {
        headers: {
            'Accept': 'application/json',
        }
    }).then((response) => {
        response.json().then(({ data }) => {
            articles = data;
        });
    });

    function buildButtonDef(trumbowyg) {
        return {
            fn: function () {
                /*
                 * This code was taken from the Trumbowyg link logic as the
                 * functionality is largely the same. Especially in regard to
                 * extracting information from the selected text.
                 */
                const documentSelection = trumbowyg.doc.getSelection();
                const selectedRange = documentSelection.getRangeAt(0);
                let node = documentSelection.focusNode;
                let text = new XMLSerializer().serializeToString(selectedRange.cloneContents()) || selectedRange + '';
                let article;

                while (['A', 'DIV'].indexOf(node.nodeName) < 0) {
                    node = node.parentNode;
                }

                if (node && node.nodeName === 'A') {
                    const $a = $(node);
                    text = $a.text();
                    const url = $a.attr('href');
                    // Instead of showing the URL from the selected link, we want
                    // to show the article title that corresponds to that URL.
                    article = articles.find((article) => article.url === url);
                    const range = trumbowyg.doc.createRange();
                    range.selectNode(node);
                    documentSelection.removeAllRanges();
                    documentSelection.addRange(range);
                }

                trumbowyg.saveRange();

                const options = {
                    article: {
                        label: 'Article',
                        required: true,
                        value: article?.title || '',
                    },
                    text: {
                        label: 'Text',
                        value: text
                    }
                };

                let articleInput;

                const modal = trumbowyg.openModalInsert('Insert article link', options, function (form) {
                    // When the form is saved we find the article by the title
                    // and insert the link tag with the article URL and title.
                    const articleTitle = trumbowyg.prependUrlPrefix(form.article);
                    const article = articles.find((article) => article.title === articleTitle);
                    // If the article doesn't exist, we stop the form from being saved.
                    if (!article) {
                        articleInput.parent().append(`<div class="trumbowyg-form-field-error help-text help-text-error">Article not found</div>`);
                        articleInput.addClass('form-input-border-error');
                        return false;
                    }

                    const link = $(
                        `<a
                            class="article-link"
                            data-ref="internal-link"
                            href="${article.url}"
                            title="${article.title}"
                        >${form.text || article.url}</a>`
                    );

                    trumbowyg.range.deleteContents();
                    trumbowyg.range.insertNode(link[0]);
                    trumbowyg.syncCode();
                    trumbowyg.$c.trigger('tbwchange');
                    return true;
                });

                articleInput = modal.find('input[name="article"]');
                const inputParent = articleInput.parent();
                const optionsContainer = $(`<datalist id="trumbowyg-articleSearch"></datalist>`);
                optionsContainer.html(articles.map((article) => `<option value="${article.title}">${article.title}</option>`));
                inputParent.append(optionsContainer);
                articleInput.attr('list', 'trumbowyg-articleSearch');
                articleInput.attr('autocomplete', 'off');
                if (articleInput.val()) {
                    articleInput.select();
                }
                articleInput.on('input', function () {
                    console.log('input');
                    inputParent.find('.trumbowyg-form-field-error').remove();
                });
            },
        };
    }

    function buildButtonIcon() {
        if ($("#trumbowyg-articleSearch").length > 0) {
            return;
        }

        const iconWrap = $(document.createElementNS("http://www.w3.org/2000/svg", "svg"));
        iconWrap.addClass("trumbowyg-icons");

        iconWrap.html(`
            <symbol id="trumbowyg-article-search" viewBox="0 0 384 512">
                <!--! Font Awesome Pro 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                <path d="M32 480V32H192V176v16h16H352V480H32zM224 37.3L346.7 160H224V37.3zM232 0H32 0V32 480v32H32 352h32V480 152L232 0zM64 64V96H80h64 16V64H144 80 64zm0 64v32H80h64 16V128H144 80 64zM224 448h16 64 16V416H304 240 224v32zm64-112H96V272H288v64zM96 240H64v32 64 32H96 288h32V336 272 240H288 96z"/>
            </symbol>
        `).appendTo(document.body);
    }

    $.extend(true, $.trumbowyg, {
        langs: {
            en: {
                articleSearch: 'Article Search',
            },
        },
        plugins: {
            articleSearch: {
                init: function (trumbowyg) {
                    trumbowyg.o.plugins.articleSearch = $.extend(true, {}, trumbowyg.o.plugins.articleSearch || {});

                    buildButtonIcon();
                    trumbowyg.addBtnDef('articleSearch', buildButtonDef(trumbowyg));

                    trumbowyg.$c.on('tbwchange', function () {
                        trumbowyg.$ed.find('a').each(function () {
                            $(this).toggleClass('article-link', articles.some((article) => article.url === $(this).attr('href')));
                            trumbowyg.syncCode();
                        });
                    });
                },
                tagHandler(element) {
                    if (element.classList.contains('article-link')) {
                        return ['articleSearch'];
                    }
                    return [];
                },
            }
        },
    });
})(jQuery);
