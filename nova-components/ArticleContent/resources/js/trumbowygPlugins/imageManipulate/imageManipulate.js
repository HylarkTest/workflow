(function ($) {
    'use strict';
    const defaultOptions = {
        minSize: 32,
        step: 4
    };

    function preventDefault(e) {
        e.stopPropagation();
        e.preventDefault();
    }

    class ResizeWithCanvas {
        constructor(trumbowyg) {
            // variable to create canvas and save img in resize mode
            this.resizeCanvas = document.createElement('canvas');
            // to allow canvas to get focus
            this.resizeCanvas.setAttribute('tabindex', '0');
            this.resizeCanvas.id = 'trumbowyg-resizimg-' + (+new Date());
            this.ctx = null;
            this.resizeImg = null;
            this.trumbowyg = trumbowyg;

            // PRIVATE FUNCTION
            this.focusedNow = false;
            this.isCursorSeResize = false;

            // calculate offset to change mouse over square in the canvas
            this.offsetX = null;
            this.offsetY = null;
        }

        reOffset(canvas) {
            const BB = canvas.getBoundingClientRect();
            this.offsetX = BB.left;
            this.offsetY = BB.top;
        }

        pressEscape(obj) {
            obj.reset();
        }

        pressBackspaceOrDelete(obj) {
            $(obj.resizeCanvas).remove();
            obj.resizeImg = null;
            if (this.trumbowyg !== null) {
                this.trumbowyg.syncCode();
                // notify changes
                this.trumbowyg.$c.trigger('tbwchange');
            }
        };

        updateCanvas() {
            const canvas = this.resizeCanvas;
            const ctx = this.ctx;
            const img = this.resizeImg;
            const canvasWidth = canvas.width;
            const canvasHeight = canvas.height;

            ctx.translate(0.5, 0.5);
            ctx.lineWidth = 1;

            // image
            ctx.drawImage(img, 5, 5, canvasWidth - 10, canvasHeight - 10);

            // border
            ctx.beginPath();
            ctx.rect(5, 5, canvasWidth - 10, canvasHeight - 10);
            ctx.stroke();

            // square in the angle
            ctx.beginPath();
            ctx.fillStyle = 'rgb(255, 255, 255)';
            ctx.rect(canvasWidth - 10, canvasHeight - 10, 9, 9);
            ctx.fill();
            ctx.stroke();

            // get the offset to change the mouse cursor
            this.reOffset(canvas);

            return ctx;
        };

        // PUBLIC FUNCTION
        // necessary to correctly print cursor over square. Called once for instance. Useless with trumbowyg.
        init() {
            $(window).on('scroll resize', () => {
                this.reCalcOffset();
            });
        };

        reCalcOffset() {
            this.reOffset(this.resizeCanvas);
        };

        canvasId() {
            return this.resizeCanvas.id;
        };

        isActive() {
            return this.resizeImg !== null;
        };

        isFocusedNow() {
            return this.focusedNow;
        };

        blurNow() {
            this.focusedNow = false;
        };

        // restore image in the HTML of the editor
        reset() {
            if (this.resizeImg === null) {
                return;
            }

            // set style of image to avoid issue on resize because this attribute have priority over width and height attribute
            const margin = this.resizeCanvas.style.margin?.replace(
                /(\d+)px/g,
                (match, p1) => parseInt(p1) + 5 + 'px'
            );
            this.resizeImg.setAttribute(
                'style',
                `
                width: 100%;
                max-width: ${this.resizeCanvas.clientWidth - 10}px;
                height: auto;
                max-height: ${this.resizeCanvas.clientHeight - 10}px;
                margin: ${margin};
                `
            );

            $(this.resizeCanvas).parent().replaceWith($(this.resizeImg));

            // reset canvas style
            this.resizeCanvas.removeAttribute('style');
            this.resizeImg = null;
        };

        // setup canvas with points and border to allow the resizing operation
        setup(img, resizableOptions) {
            this.resizeImg = img;

            if (!this.resizeCanvas.getContext) {
                return false;
            }

            const $img = $(img);
            const $canvas = $(this.resizeCanvas);

            this.focusedNow = true;

            // draw canvas
            this.resizeCanvas.width = $img.width() + 10;
            this.resizeCanvas.height = $img.height() + 10;
            this.resizeCanvas.style.margin = img.style.margin?.replace(
                /(\d+)px/g,
                (match, p1) => parseInt(p1) - 5 + 'px'
            );
            this.ctx = this.resizeCanvas.getContext('2d');

            const container = $('<div class="relative"></div>')
                .append(
                    $canvas,
                    $(`<div
                        class="absolute flex justify-center items-center bg-white border border-gray-300 rounded-full shadow-md p-3"
                        style="height: 25px; top: 10px; left: 10px;"
                    >
                        <button type="button" data-align="left" title="Align Left" tabindex="-1"><svg><use xlink:href="#trumbowyg-justify-left"></use></svg></button>
                        <button type="button" data-align="center" title="Align Center" tabindex="-1"><svg><use xlink:href="#trumbowyg-justify-center"></use></svg></button>
                        <button type="button" data-align="right" title="Align Right" tabindex="-1"><svg><use xlink:href="#trumbowyg-justify-right"></use></svg></button>
                    </div>`)
                );

            container.find('div').click(preventDefault);
            container.find('button').click((e) => {
                preventDefault(e);
                const target = $(e.currentTarget);
                const align = target.data('align');
                this.resizeCanvas.style.margin = {
                    left: '0px auto 0px -5px',
                    center: '0px auto',
                    right: '0px -5px 0px auto',
                }[align];
            });

            // replace image with canvas
            $img.replaceWith(container);

            this.updateCanvas();

            // enable resize
            $canvas.resizableSafe(resizableOptions)
                .on('mousedown', preventDefault);

            const _this = this;
            $canvas
                .on('mousemove', function (e) {
                    const mouseX = Math.round(e.clientX - this.offsetX);
                    const mouseY = Math.round(e.clientY - this.offsetY);

                    const wasCursorSeResize = _this.isCursorSeResize;

                    _this.ctx.rect(_this.resizeCanvas.width - 10, _this.resizeCanvas.height - 10, 9, 9);
                    _this.isCursorSeResize = _this.ctx.isPointInPath(mouseX, mouseY);
                    if (wasCursorSeResize !== _this.isCursorSeResize) {
                        this.style.cursor = _this.isCursorSeResize ? 'se-resize' : 'default';
                    }
                })
                .on('keydown', (e) => {
                    if (!this.isActive()) {
                        return;
                    }

                    const x = e.keyCode;
                    if (x === 27) { // ESC
                        this.pressEscape(this);
                    } else if (x === 8 || x === 46) { // BACKSPACE or DELETE
                        this.pressBackspaceOrDelete(this);
                    }
                })
                .on('focus', preventDefault);
            container.on('blur', () => {
                this.reset();
                // save changes
                if (this.trumbowyg !== null) {
                    this.trumbowyg.syncCode();
                    // notify changes
                    this.trumbowyg.$c.trigger('tbwchange');
                }
            });

            this.resizeCanvas.focus();

            return true;
        };

        // update the canvas after the resizing
        refresh() {
            if (!this.resizeCanvas.getContext) {
                return;
            }

            this.resizeCanvas.width = this.resizeCanvas.clientWidth;
            this.resizeCanvas.height = this.resizeCanvas.clientHeight;
            this.updateCanvas();
        };
    };

    $.extend(true, $.trumbowyg, {
        plugins: {
            resizimg: {
                destroyResizable: function () {
                },
                init: function (trumbowyg) {
                    // object to interact with canvas
                    const resizeWithCanvas = new ResizeWithCanvas(trumbowyg);

                    this.destroyResizable = function () {
                        // clean html code
                        trumbowyg.$ed.find('canvas.resizable')
                            .resizableSafe('destroy')
                            .off('mousedown', preventDefault)
                            .removeClass('resizable');

                        resizeWithCanvas.reset();

                        trumbowyg.syncCode();
                    };

                    trumbowyg.o.plugins.resizimg = $.extend(true, {},
                        defaultOptions,
                        trumbowyg.o.plugins.resizimg || {},
                        {
                            resizable: {
                                resizeWidth: false,
                                onDragStart: function (ev, $el) {
                                    const opt = trumbowyg.o.plugins.resizimg;
                                    const x = ev.pageX - $el.offset().left;
                                    const y = ev.pageY - $el.offset().top;
                                    if (x < $el.width() - opt.minSize || y < $el.height() - opt.minSize) {
                                        return false;
                                    }
                                },
                                onDrag: function (ev, $el, newWidth, newHeight) {
                                    var opt = trumbowyg.o.plugins.resizimg;
                                    if (newHeight < opt.minSize) {
                                        newHeight = opt.minSize;
                                    }
                                    newHeight -= newHeight % opt.step;
                                    $el.height(newHeight);
                                    return false;
                                },
                                onDragEnd: function () {
                                    // resize update canvas information
                                    resizeWithCanvas.refresh();
                                    trumbowyg.syncCode();
                                }
                            }
                        }
                    );

                    function initResizable() {
                        trumbowyg.$ed.find('img')
                            .off('click')
                            .on('click', function (e) {
                                // if I'm already do a resize, reset it
                                if (resizeWithCanvas.isActive()) {
                                    resizeWithCanvas.reset();
                                }
                                // initialize resize of image
                                resizeWithCanvas.setup(this, trumbowyg.o.plugins.resizimg.resizable);

                                preventDefault(e);
                            });
                    }

                    trumbowyg.$c.on('tbwinit', () => {
                        initResizable();

                        // disable resize when click on other items
                        trumbowyg.$ed.on('click', (e) => {
                            // check if I've clicked out of canvas or image to reset it
                            if ($(e.target).is('img') || e.target.id === resizeWithCanvas.canvasId()) {
                                return;
                            }

                            preventDefault(e);
                            resizeWithCanvas.reset();
                            //sync
                            trumbowyg.syncCode();
                            // notify changes
                            trumbowyg.$c.trigger('tbwchange');
                        });

                        trumbowyg.$ed.on('scroll', () => {
                            resizeWithCanvas.reCalcOffset();
                        });
                    });

                    trumbowyg.$c.on('tbwfocus tbwchange', initResizable);
                    trumbowyg.$c.on('tbwresize', () => {
                        resizeWithCanvas.reCalcOffset();
                    });

                    // Destroy
                    trumbowyg.$c.on('tbwblur', () => {
                        // when canvas is created the tbwblur is called
                        // this code avoid to destroy the canvas that allow the image resizing
                        if (resizeWithCanvas.isFocusedNow()) {
                            resizeWithCanvas.blurNow();
                        } else {
                            this.destroyResizable();
                        }
                    });
                },
                destroy: function () {
                    this.destroyResizable();
                }
            }
        }
    });
})(jQuery);
