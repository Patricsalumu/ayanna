import './bootstrap';
import { posApp } from './posApp';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.data('posApp', posApp);

Alpine.start();
