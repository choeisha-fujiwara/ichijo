import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import './dashboard';

import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
window.Chart = Chart;