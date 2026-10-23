export type ChartPoint = {
    x: number;
    y: number;
    value: number;
    index: number;
};

export function buildChartPoints(
    values: number[],
    width = 100,
    height = 40,
    padding = 0,
): ChartPoint[] {
    if (values.length === 0) {
        return [];
    }

    const min = Math.min(...values);
    const max = Math.max(...values);
    const range = max - min;

    const usableWidth = Math.max(width - padding * 2, 0.0001);
    const usableHeight = Math.max(height - padding * 2, 0.0001);

    const step = values.length > 1 ? usableWidth / (values.length - 1) : 0;

    return values.map((value, index) => {
        const ratio = range === 0 ? 0.5 : (value - min) / range;

        return {
            x:
                padding +
                step * index +
                (values.length === 1 ? usableWidth / 2 : 0),
            y: padding + (1 - ratio) * usableHeight,
            value,
            index,
        };
    });
}

export function smoothPath(points: ChartPoint[]): string {
    if (points.length === 0) {
        return '';
    }

    if (points.length === 1) {
        return `M ${points[0].x} ${points[0].y}`;
    }

    let path = `M ${points[0].x} ${points[0].y}`;

    for (let index = 0; index < points.length - 1; index += 1) {
        const previous = points[index - 1] ?? points[index];
        const current = points[index];
        const next = points[index + 1];
        const afterNext = points[index + 2] ?? next;

        const controlOneX = current.x + (next.x - previous.x) / 6;
        const controlOneY = current.y + (next.y - previous.y) / 6;
        const controlTwoX = next.x - (afterNext.x - current.x) / 6;
        const controlTwoY = next.y - (afterNext.y - current.y) / 6;

        path += ` C ${controlOneX} ${controlOneY}, ${controlTwoX} ${controlTwoY}, ${next.x} ${next.y}`;
    }

    return path;
}

export function areaPath(points: ChartPoint[], baseline = 100): string {
    if (points.length === 0) {
        return '';
    }

    const line = smoothPath(points);
    const first = points[0];
    const last = points[points.length - 1];

    return `${line} L ${last.x} ${baseline} L ${first.x} ${baseline} Z`;
}
