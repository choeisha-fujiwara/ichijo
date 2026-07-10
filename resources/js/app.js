import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

import './dashboard';

import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
window.Chart = Chart;