/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.$ = window.jQuery = require('jquery');

import Alpine from 'alpinejs'

window.Alpine = Alpine
Alpine.start();

import BlockNote from './block-note';

window.BlockNote = BlockNote;
