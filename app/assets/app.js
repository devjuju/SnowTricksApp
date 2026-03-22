/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

// Alpine
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Flowbite
import 'flowbite';

import './js/navbar.js';
import './js/scroll.js';
import './js/load-more.js';
import './js/featured-image.js';


console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
