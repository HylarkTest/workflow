export function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16),
    };
}

export function rgbToHsl(rgb) {
    const r = rgb.r / 255;
    const g = rgb.g / 255;
    const b = rgb.b / 255;

    let hue;
    let saturation;

    const minValue = Math.min(r, g, b);
    const maxValue = Math.max(r, g, b);
    const maxDelta = maxValue - minValue;

    const lightness = (maxValue + minValue) / 2;

    if (maxDelta === 0) {
        hue = 0;
        saturation = 0;
    } else {
        if (lightness < 0.5) {
            saturation = maxDelta / (maxValue + minValue);
        } else {
            saturation = maxDelta / (2 - maxValue - minValue);
        }

        const deltaRed = (((maxValue - r) / 6) + (maxDelta / 2)) / maxDelta;
        const deltaGreen = (((maxValue - g) / 6) + (maxDelta / 2)) / maxDelta;
        const deltaBlue = (((maxValue - b) / 6) + (maxDelta / 2)) / maxDelta;

        if (r === maxValue) {
            hue = deltaBlue - deltaGreen;
        } else if (g === maxValue) {
            hue = (1 / 3) + deltaRed - deltaBlue;
        } else if (b === maxValue) {
            hue = (2 / 3) + deltaGreen - deltaRed;
        }

        if (hue < 0) {
            hue += 1;
        }

        if (hue > 1) {
            hue -= 1;
        }
    }

    return { h: Math.round(hue * 360), s: Math.round(saturation * 100), l: Math.round(lightness * 100) };
}

function hue2Rgb(v1, v2, vh) {
    if (vh < 0) {
        // eslint-disable-next-line no-param-reassign
        vh += 1;
    }

    if (vh > 1) {
        // eslint-disable-next-line no-param-reassign
        vh -= 1;
    }

    if ((6 * vh) < 1) {
        return v1 + (v2 - v1) * 6 * vh;
    }

    if ((2 * vh) < 1) {
        return v2;
    }

    if ((3 * vh) < 2) {
        return v1 + (v2 - v1) * ((2 / 3 - vh) * 6);
    }

    return v1;
}

export function hslToRgb(hsl) {
    const h = hsl.h / 360;
    const s = hsl.s / 100;
    const l = hsl.l / 100;

    if (s === 0.0) {
        const color = Math.round(l * 255);
        return { r: color, g: color, b: color };
    }

    let var2;
    if (l < 0.5) {
        var2 = l * (1 + s);
    } else {
        var2 = (l + s) - (s * l);
    }

    const var1 = 2 * l - var2;
    const r = hue2Rgb(var1, var2, h + (1 / 3));
    const g = hue2Rgb(var1, var2, h);
    const b = hue2Rgb(var1, var2, h - (1 / 3));

    return { r: Math.round(r * 255), g: Math.round(g * 255), b: Math.round(b * 255) };
}

function componentToHex(c) {
    const hex = c.toString(16);
    return hex.length === 1 ? `0${ hex}` : hex;
}

export function rgbToHex({ r, g, b }) {
    return `#${componentToHex(r)}${componentToHex(g)}${componentToHex(b)}`;
}

export function hexToHsl(hex) {
    return rgbToHsl(hexToRgb(hex));
}

export function hslToHex(hsl) {
    return rgbToHex(hslToRgb(hsl));
}
