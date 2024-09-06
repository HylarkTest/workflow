import { memoize, throttle } from 'lodash';

import TWEEN from '@tweenjs/tween.js';

const body = document.body;

export function hasScrollbar(el, shouldIgnoreFixedElements = false, vertical = true) {
    if (!el || el.nodeType !== Node.ELEMENT_NODE) {
        return false;
    }

    const overflowProperty = vertical ? 'overflowY' : 'overflowX';
    const scrollProperty = vertical ? 'scrollHeight' : 'scrollWidth';
    const clientProperty = vertical ? 'clientHeight' : 'clientWidth';
    const style = window.getComputedStyle(el);
    if (style[overflowProperty] === 'scroll') {
        return true;
    }
    if (style[overflowProperty] === 'auto' && el[scrollProperty] > el[clientProperty]) {
        return true;
    }
    if (shouldIgnoreFixedElements) {
        return false;
    }
    return style.position === 'absolute'
        || style.position === 'fixed';
}

function _getScrollParent(el, shouldIgnoreFixedElements = false, vertical = true) {
    let scrollParent = el;
    while (scrollParent) {
        if (hasScrollbar(scrollParent, shouldIgnoreFixedElements, vertical)) {
            return scrollParent;
        }
        scrollParent = scrollParent.parentElement;
    }

    return body;
}

const getScrollParentWithFixed = memoize((el) => _getScrollParent(el, true));
const getScrollParentWithoutFixed = memoize((el) => _getScrollParent(el, false));
const getXScrollParentWithFixed = memoize((el) => _getScrollParent(el, true, false));
const getXcrollParentWithoutFixed = memoize((el) => _getScrollParent(el, false, false));

export function getScrollParent(el, shouldIgnoreFixedElements = false, vertical = true) {
    if (shouldIgnoreFixedElements) {
        return vertical
            ? getScrollParentWithFixed(el)
            : getXScrollParentWithFixed(el);
    }
    return vertical
        ? getScrollParentWithoutFixed(el)
        : getXcrollParentWithoutFixed(el);
}

export function smoothScroll(newY, el = null, speed = 300, oldY = null, easing = null) {
    // Because at this time, not all browsers support behavior: smooth
    const scrollEl = el ? getScrollParent(el) : body;

    const scrollTo = (y) => {
        if (scrollEl === body) {
            body.scrollTo(0, y);
        } else {
            scrollEl.scrollTop = y;
        }
    };

    return new Promise((resolve) => {
        const y = { val: oldY || body.scrollTop };
        scrollTo(y.val);
        const tween = new TWEEN.Tween(y)
            .to({ val: newY }, speed)
            .easing(easing || TWEEN.Easing.Quadratic.InOut)
            .onUpdate(() => {
                scrollTo(y.val);
            });

        let finished = false;

        tween.onComplete(() => {
            finished = true;
        });
        tween.start();

        function step(time) {
            TWEEN.update(time);
            if (finished) {
                resolve();
            } else {
                requestAnimationFrame(step);
            }
        }
        requestAnimationFrame(step);
    });
}

export function smoothScrollFromCurrent(newY, el = null, speed = 300, easing = null) {
    const current = body.scrollTop;
    return smoothScroll(newY, el, speed, current, easing);
}

export function distanceFromBottom(el = null) {
    const scrollBody = getScrollParent(el);
    return scrollBody.scrollHeight - (body.scrollTop + window.innerHeight);
}

export function distanceFromTop() {
    return body.scrollTop;
}

const onScrollListeners = new WeakMap();
const onResizeListeners = [];

const scrollListener = throttle((event) => {
    const scrollListeners = onScrollListeners.get(event.target);
    scrollListeners.forEach((listener) => listener(event));
}, 10, { leading: true, trailing: true });

const resizeListener = throttle((event) => {
    onResizeListeners.forEach((listener) => listener(event));
}, 50, { leading: true, trailing: true });

export function addScrollListener(listener, el = null, elIsScrollEl = false, shouldIgnoreFixedElements = false) {
    const scrollYParent = (el && elIsScrollEl)
        ? el
        : getScrollParent(el, shouldIgnoreFixedElements); // Defaults to body if el is null
    const scrollXParent = (el && elIsScrollEl)
        ? el
        : getScrollParent(el, shouldIgnoreFixedElements, false);
    [scrollYParent, scrollXParent].forEach((scrollParent) => {
        const scrollEl = scrollParent;
        const scrollListeners = onScrollListeners.get(scrollEl) || [];
        if (!scrollListeners.length) {
            scrollEl.addEventListener('scroll', scrollListener, { passive: true });
        }
        const index = scrollListeners.indexOf(listener);
        if (index === -1) {
            scrollListeners.push(listener);
        }
        onScrollListeners.set(scrollEl, scrollListeners);
    });
}

export function removeScrollListener(listener, el = null, elIsScrollEl = false) {
    const scrollYParent = (el && elIsScrollEl)
        ? el
        : getScrollParent(el); // Defaults to body if el is null
    const scrollXParent = (el && elIsScrollEl)
        ? el
        : getScrollParent(el, false, false); // Defaults to body if el is null
    [scrollYParent, scrollXParent].forEach((scrollParent) => {
        const scrollListeners = onScrollListeners.get(scrollParent) || [];
        const index = scrollListeners.indexOf(listener);
        if (~index) {
            scrollListeners.splice(index, 1);
        }
        if (!scrollListeners.length) {
            scrollParent.removeEventListener('scroll', scrollListener);
        }
        const key = scrollParent;
        onScrollListeners.set(key, scrollListeners);
    });
}

export function addResizeListener(listener) {
    if (!onResizeListeners.length) {
        window.addEventListener('resize', resizeListener, { passive: true });
    }
    const index = onResizeListeners.indexOf(listener);
    if (index === -1) {
        onResizeListeners.push(listener);
    }
}

export function removeResizeListener(listener) {
    const index = onResizeListeners.indexOf(listener);
    if (~index) {
        onResizeListeners.splice(index, 1);
    }
    if (!onResizeListeners.length) {
        window.removeEventListener('resize', resizeListener);
    }
}

export function onScroll(listener, el, elIsScrollEl = false) {
    addScrollListener(listener, el, elIsScrollEl);
    return () => removeScrollListener(listener, el, elIsScrollEl);
}

export function onResize(listener, el) {
    addResizeListener(listener, el);
    return () => removeResizeListener(listener, el);
}

export function onScrollAndResize(listener, el) {
    addScrollListener(listener, el);
    addResizeListener(listener, el);
    return () => {
        removeScrollListener(listener, el);
        removeResizeListener(listener, el);
    };
}
