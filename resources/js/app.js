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
    Vue.mixin({
        data() {
            return {
            }
        },
        methods: {
            formatPrice(value) {
                let formatter = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2,
                    currencySign: 'accounting'
                });
                let str =  formatter.format(value);
                let replace = str.replace('$', ' ');
                return replace;
            },
            isDataExist: function (modelValue, matchKey, index, data) {
                let found = 0;
                data.map(v => {
                    if (v[matchKey] ===  modelValue) {
                        found++;
                    }
                })
                if (found > 1) {
                    data[index][matchKey] = ''
                    this.$toast.error('You already select this item')
                }
            },
            isNumeric(str) {
                if (typeof str != "string") return false // we only process strings!
                return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
                    !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
            },
            years: function (startYear) {
                let years = [];
                for (let i = startYear; i < new Date().getFullYear()+5; i++) {
                    years.push({ id:i, name: i})
                }
                return years
            },
            months: function () {
                return [
                    {id: 1, name: 'January'},
                    {id: 2, name: 'February'},
                    {id: 3, name: 'March'},
                    {id: 4, name: 'April'},
                    {id: 5, name: 'May'},
                    {id: 6, name: 'June'},
                    {id: 7, name: 'July'},
                    {id: 8, name: 'August'},
                    {id: 9, name: 'September'},
                    {id: 10, name: 'October'},
                    {id: 11, name: 'November'},
                    {id: 12, name: 'December'},
                ]
            },
        }
    });
const app = new Vue({
    el: "#app",
    components: { App },
    router,
    store,
});
