/**
 * Wheel geometry. Angles are in degrees, measured clockwise from 3 o'clock, where the pointer sits.
 * Segment 0 is centred on the pointer when the wheel is at rest, the rest follow clockwise
 * (matching the design: 1 on the right, 3 at the bottom, 5 on the left, 7 at the top).
 */

/** Full turns the wheel makes before settling, so every spin feels like a real spin. */
export const SPIN_TURNS = 6;

export const SPIN_DURATION_MS = 5200;

/** Fast start, long gentle stop. */
export const SPIN_EASING = 'cubic-bezier(0.15, 0.6, 0.1, 1)';

export function segmentAngle(count: number): number {
    return 360 / Math.max(count, 1);
}

/** Centre angle of a segment while the wheel is at rest. */
export function segmentCentre(index: number, count: number): number {
    return index * segmentAngle(count);
}

function point(cx: number, cy: number, radius: number, degrees: number) {
    const radians = (degrees * Math.PI) / 180;

    return {
        x: cx + radius * Math.cos(radians),
        y: cy + radius * Math.sin(radians),
    };
}

/** SVG path for one pie slice. */
export function segmentPath(
    index: number,
    count: number,
    radius: number,
    cx = radius,
    cy = radius,
): string {
    const size = segmentAngle(count);
    const centre = segmentCentre(index, count);
    const start = point(cx, cy, radius, centre - size / 2);
    const end = point(cx, cy, radius, centre + size / 2);
    const largeArc = size > 180 ? 1 : 0;

    return `M ${cx} ${cy} L ${start.x} ${start.y} A ${radius} ${radius} 0 ${largeArc} 1 ${end.x} ${end.y} Z`;
}

/** Where a segment's number sits, and how much to turn it so it reads along the radius. */
export function labelPlacement(
    index: number,
    count: number,
    radius: number,
    cx = radius,
    cy = radius,
) {
    const angle = segmentCentre(index, count);

    return { ...point(cx, cy, radius, angle), angle };
}

/**
 * The rotation that brings `index` under the pointer, always moving forward from `current`
 * by a few full turns. `jitter` (-0.5..0.5) nudges the stop inside the segment so it doesn't
 * always land dead centre.
 */
export function rotationFor(
    index: number,
    count: number,
    current: number,
    jitter = 0,
): number {
    const size = segmentAngle(count);
    const target = -segmentCentre(index, count) + jitter * size * 0.6;
    const delta = (((target - current) % 360) + 360) % 360;

    return current + SPIN_TURNS * 360 + delta;
}

/** Alternate maroon and yellow like the design; an odd count gets a rose slice so no two neighbours match. */
export function segmentColour(index: number, count: number): string {
    if (count % 2 === 1 && count > 1 && index === count - 1) {
        return 'var(--color-wheel-rose)';
    }

    return index % 2 === 0
        ? 'var(--color-wheel-maroon)'
        : 'var(--color-wheel-sun)';
}
