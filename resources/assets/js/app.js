/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import BootstrapVue from 'bootstrap-vue';
import App from './App.vue';
import spinner from 'vue-spinner/src/PulseLoader.vue';

window.Vue = require('vue');
Vue.use(BootstrapVue);
Vue.component('spinner', spinner);

import 'vue-toast/dist/vue-toast.min.css';

import axios from 'axios';
axios.defaults.headers.common.Accept = 'application/json';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
  template: '<app />',
  components: { App },
  el: '#app',
});
