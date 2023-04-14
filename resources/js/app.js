global.jQuery = require("jquery");
const $ = global.jQuery;
window.$ = $;

import 'sweetalert2/src/sweetalert2.scss'

import Vue from "vue";
import VueRouter from "vue-router";
import axios from "axios";
import Vuex from "vuex";
import VuePageTransition from "vue-page-transition";
import VueToast from "vue-toast-notification";
import "vue-toast-notification/dist/theme-default.css";


import App from "./App.vue";
import router from "./Router/router";
import store from "./Store/store";


Vue.use(VueRouter, axios, Vuex);
Vue.use(VuePageTransition);
Vue.use(VueToast, { position: "top-right" });
const app = new Vue({
    el: "#app",
    components: { App },
    router,
    store,
});
