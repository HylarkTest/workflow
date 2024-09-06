import _ from 'lodash';
import { nextTick } from 'vue';

const blurList = [];

const teleportedComponents = new WeakMap();
const overlayStack = [];

/**
 * When a component is moved somewhere else in the DOM using teleport we want to
 * use the Vue hierarchy instead of the DOM hierarchy when deciding where the
 * user has clicked.
 * There doesn't seem to be an easy way to inherently test if a node has been
 * teleported so instead we use the `v-blur` directive with the `teleport`
 * modifier pointing to the parent to let us know where a node has been
 * teleported to. All the teleported elements are stored in the
 * `teleportedComponents` `WeakMap` keyed by the teleported component with the
 * value being the trace up to the document of the teleported component's
 * logical parent.
 * So in this function we can replace the DOM stack of an event with the Vue
 * stack by replacing any teleported components with the stack from the
 * `WeakMap`.
 */
function getTeleportedStack(stack) {
    const teleportedIndex = stack.findIndex((el) => teleportedComponents.has(el));

    if (~teleportedIndex) {
        return [
            ...stack.slice(0, teleportedIndex + 1),
            ...getTeleportedStack(teleportedComponents.get(stack[teleportedIndex])),
        ];
    }
    return stack;
}

let mouseDownElement = null;
let mouseDownPath = null;

function getEventPath(e) {
    if (!e.target || !(e.target instanceof Element)) {
        return null;
    }

    // @NOTE: working with path is better,
    //        because it tests whether the element was there at the time of
    //        the click, not whether it is there now, that the event has arrived
    //        to the top.
    let path = e.composedPath ? e.composedPath() : undefined;

    if (!path) {
        return null;
    }

    if (_.some(path, (el) => el.classList?.contains('stop-blur'))) {
        return null;
    }

    path = getTeleportedStack(path);

    return path;
}

const blurEvent = (e) => {
    const currentOverlay = overlayStack[overlayStack.length - 1];
    const path = getEventPath(e);

    if (!path) {
        return;
    }

    // Check if path contains an element with id #app starting from the end of the array
    const appIndex = path.lastIndexOf(document.getElementById('app'));
    if (appIndex === -1) {
        return;
    }

    const targetEl = e.target;

    const startedPath = mouseDownPath && getTeleportedStack(mouseDownPath);

    blurList.forEach((item) => {
        if (currentOverlay && !currentOverlay.contains(item.el)) {
            return;
        }
        const target = path ? path.indexOf(item.el) !== -1 : (item.el.contains(targetEl) || item.el === targetEl);
        let startedInside = false;
        if (mouseDownElement) {
            startedInside = startedPath
                ? startedPath.indexOf(item.el) !== -1
                : (item.el.contains(mouseDownElement) || item.el === mouseDownElement);
        }
        if (!target && !startedInside) {
            item.handler.call(item.el, e);
        }
    });
    mouseDownElement = null;
    mouseDownPath = null;
};

const escapeEvent = (e) => {
    if (e.code === 'Escape') {
        const path = getEventPath(e);

        let deepestBlurChildInPath = null;
        if (path) {
            path.reverse();
            let foundIndex = -1;
            blurList.forEach((item) => {
                if (!item.escapeDisabled) {
                    const index = path.indexOf(item.el);
                    if (index > foundIndex) {
                        foundIndex = index;
                        deepestBlurChildInPath = item;
                    }
                }
            });
        }
        if (deepestBlurChildInPath) {
            deepestBlurChildInPath.handler.call(deepestBlurChildInPath.el, e);
        } else {
            blurList.forEach((item) => item.escapeDisabled || item.handler.call(item.el, e));
        }
    }
};

document.addEventListener('mousedown', (e) => {
    mouseDownElement = e.target;
    mouseDownPath = e.composedPath && e.composedPath();
}, false);
document.addEventListener('mouseup', blurEvent, false);
document.addEventListener('keydown', escapeEvent, false);

async function addToTeleportStack(_parent, el) {
    let parent = _parent;
    if (_.hasIn(parent, '$el')) {
        // In some weird cases the parent hasn't been rendered at the
        // time the child has so here we just wait a bit for that to
        // happen.
        if (!parent.$el) {
            await nextTick();
        }

        parent = parent.$el;
    }

    const parentStack = [];

    for (; parent && parent !== document; parent = parent.parentNode) {
        parentStack.push(parent);
    }

    teleportedComponents.set(el, parentStack);
}

function addToOverlayStack(el) {
    overlayStack.push(el);
}

function removeFromTeleportStack(el) {
    teleportedComponents.delete(el);
}

function removeFromOverlayStack(el) {
    overlayStack.splice(overlayStack.indexOf(el), 1);
}

const blurDirective = {
    async beforeMount(el, binding) {
        // Define Handler and cache it on the element
        const { noescape, teleport } = binding.modifiers;

        if (teleport) {
            // When defining a teleport component you can add the parent element
            // as the value of the directive. This means the blur can still
            // determine the _Vue_ hierarchy of the component even when it is
            // different from the DOM hierarchy.
            //
            // If there is no paremt element defined then we assume the component
            // is an overlay, and we should not trigger any listeners "behind" it.
            const parent = binding.value;
            if (!parent) {
                addToOverlayStack(el);
            } else {
                await addToTeleportStack(parent, el);
            }
            return;
        }

        const handler = (e) => {
            if (!e.target || !(e.target instanceof Element)) {
                return;
            }
            if ((!el.contains(e.target) && el !== e.target) || e instanceof KeyboardEvent) {
                if (binding.value) {
                    binding.value(e);
                }
            }
        };
        window.setTimeout(() => blurList.push({ el, handler, escapeDisabled: noescape }), 0);
    },

    // Allows the teleported element to dynamically change between an overlay
    // and a regular teleported element.
    async updated(el, binding) {
        if (binding.modifiers.teleport) {
            const parent = binding.value;
            const oldParent = binding.oldValue;
            if (parent && !oldParent) {
                removeFromOverlayStack(el);
                await addToTeleportStack(parent, el);
            } else if (!parent && oldParent) {
                removeFromTeleportStack(el);
                addToOverlayStack(el);
            }
        }
    },

    unmounted(el, binding) {
        if (binding.modifiers.teleport) {
            if (binding.value) {
                removeFromTeleportStack(el);
            } else {
                removeFromOverlayStack(el);
            }
        } else {
            // Remove Event Listeners
            _.remove(blurList, { el });
        }
    },
};

const plugin = {
    install(app) {
        app.directive('blur', blurDirective);
    },
};

export default plugin;
