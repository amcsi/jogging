/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import 'babel-polyfill';
import axios from 'axios';
import BootstrapVue from 'bootstrap-vue';
import VCalendar from 'v-calendar';
import 'v-calendar/lib/v-calendar.min.css';
import VModal from 'vue-js-modal';
import VueRouter from 'vue-router';
import spinner from 'vue-spinner/src/PulseLoader.vue';
import 'vue-toast/dist/vue-toast.min.css';
import App from './App.vue';
import JoggingList from './components/jogging/JoggingList';
import JoggingWeekly from './components/jogging/JoggingWeekly';
import UserContext from './components/user/UserContext';
import UserList from './components/user/UserList';
import Datepicker from './globalComponents/Datepicker';
import Day from './globalComponents/Day';
import FormFieldErrors from './globalComponents/FormFieldErrors';

window.Vue = require('vue');
Vue.use(BootstrapVue);
Vue.component('datepicker', Datepicker);
Vue.component('day', Day);
Vue.component('spinner', spinner);
Vue.component('form-field-errors', FormFieldErrors);
Vue.use(VCalendar);
Vue.use(VModal);
Vue.use(VueRouter);

const router = new VueRouter({
  routes: [
    { path: '/', component: JoggingList },
    { path: '/jogging-weekly', component: JoggingWeekly },
    { path: '/users', component: UserList },
    {
      path: '/users/:userId', component: UserContext, children: [
        {
          path: 'jogging-times',
          component: JoggingList,
        },
      ],
    },
  ],
});

axios.defaults.headers.common.Accept = 'application/json';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

new Vue({
  router,
  template: '<app />',
  components: { App },
  el: '#app',
});
