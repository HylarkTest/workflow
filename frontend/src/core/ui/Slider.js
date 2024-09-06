const getSlideFromCoords = ({ x, y }, rect, orientation) => {
    const isVertical = orientation === 'vertical';

    const position = isVertical ? y : x;
    const max = isVertical ? rect.bottom : rect.right;
    const min = isVertical ? rect.top : rect.left;
    const size = isVertical ? rect.height : rect.width;

    if (position > max) {
        return 100;
    }
    if (position < min) {
        return 0;
    }
    return ((position - min) / size) * 100;
};

export default class Slider {
    constructor(containerSlider, ball, options, orientation) {
        this.active = false;
        this._position = 0;
        this.containerSlider = containerSlider;
        this.ball = ball;
        this.orientation = orientation;

        this.initOptions(options);
        this.updateCSS();
        this.bindHandlers();
        this.addListeners();
    }

    get position() {
        return this._position;
    }

    set position(value) {
        if (this._position !== value) {
            this._position = value;
            this.updateCSS();
        }
    }

    initOptions(options) {
        const customOptions = options || {};

        this.onSlide = customOptions.onSlide || _.noop;
        this.onDragStart = customOptions.onDragStart || _.noop;
        this.onDragStop = customOptions.onDragStop || _.noop;

        this._position = customOptions.position || 0;
    }

    bindHandlers() {
        this.onSlideStart = this.onSlideStart.bind(this);
        this.onSlid = this.onSlid.bind(this);
        this.onSlideStop = this.onSlideStop.bind(this);
    }

    addListeners() {
        this.containerSlider.addEventListener('touchstart', this.onSlideStart, { passive: true });
        document.addEventListener('touchmove', this.onSlid, { passive: false });
        document.addEventListener('touchend', this.onSlideStop, { passive: true });
        document.addEventListener('touchcancel', this.onSlideStop, { passive: true });

        this.containerSlider.addEventListener('mousedown', this.onSlideStart, { passive: true });
        document.addEventListener('mousemove', this.onSlid, { passive: false });
        document.addEventListener('mouseup', this.onSlideStop, { passive: true });
        document.addEventListener('mouseleave', this.onSlideStop, { passive: false });
    }

    removeListeners() {
        this.containerSlider.removeEventListener('touchstart', this.onSlideStart);
        document.removeEventListener('touchmove', this.onSlid);
        document.removeEventListener('touchend', this.onSlideStop);
        document.removeEventListener('touchcancel', this.onSlideStop);

        this.containerSlider.removeEventListener('mousedown', this.onSlideStart);
        document.removeEventListener('mousemove', this.onSlid);
        document.removeEventListener('mouseup', this.onSlideStop);
        document.removeEventListener('mouseleave', this.onSlideStop);
    }

    destroy() {
        this.onSlideStop();
        this.removeListeners();
    }

    onSlideStart(event) {
        if (event.type === 'touchstart' || event.button === 0) {
            this.initDrag();
            this.onDragStart(event);
        }
    }

    onSlideStop() {
        if (this.active) {
            this.active = false;
            this.onDragStop();
        }

        this.active = false;
    }

    onSlid(event) {
        if (this.active) {
            event.preventDefault();

            const point = event.targetTouches ? event.targetTouches[0] : event;

            this.updatePositionToMouse({
                x: point.clientX,
                y: point.clientY,
            });

            this.updateCSS();
            this.onSlide(this._position);
        }
    }

    setPositionFromEvent(ev) {
        this._position = getSlideFromCoords(
            { x: ev.clientX, y: ev.clientY },
            this.containerSlider.getBoundingClientRect(),
            this.orientation
        );

        this.updateCSS();
        this.onSlide(this._position);
    }

    updatePositionToMouse(newPoint) {
        this._position = getSlideFromCoords(newPoint, this.containerSlider.getBoundingClientRect(), this.orientation);
    }

    initDrag() {
        this.active = true;
    }

    updateCSS() {
        const isVertical = this.orientation === 'vertical';
        const placement = isVertical ? 'top' : 'left';
        const offset = isVertical ? 'offsetHeight' : 'offsetWidth';
        this.ball.style[placement] = `calc(${this._position}% - ${this.ball[offset] / 2}px)`;
    }
}
