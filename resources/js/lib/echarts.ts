import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';

import { LineChart, BarChart } from 'echarts/charts';

import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
} from 'echarts/components';

import VChart from 'vue-echarts';

use([
    CanvasRenderer,
    LineChart,
    BarChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
]);

export default VChart;
