import _ from 'lodash';
window._ = _;

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Alpine.js - Vital para que tus botones funcionen
 */
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
