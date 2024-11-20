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
import "vue-select/dist/vue-select.css";
import vSelect from "vue-select";
import PrimeVue from 'primevue/config';

import 'primevue/resources/themes/bootstrap4-light-blue/theme.css';
import App from "./App.vue";
import router from "./Router/router";
import store from "./Store/store";
import InputNumber from 'primevue/inputnumber';

// import VueMqtt from 'vue-mqtt';
// Vue.use(VueMqtt, 'mqtt://magic.infrmtx.com:1883', {
//     clientId: '1b3089ee-92f5-495d-9bdb-b8405ab5ba5b', // Replace with your client ID
//     connectTimeout: 3000, // Timeout for connection in milliseconds
//     reconnectPeriod: 3000, // Reconnect period in milliseconds
// });


Vue.use(VueRouter, axios, Vuex);
Vue.use(VuePageTransition);
Vue.component("v-select", vSelect);
Vue.use(VueToast, { position: "top-right" });
Vue.component("InputNumber", InputNumber)
Vue.use(PrimeVue);
Vue.mixin({
        computed: {
            numberFractionDigit() {
                let auth = store.getters.GetAuth ?? [];
                let value = auth['currency_precision'] ?? 2;
                return parseInt(value);
            },
            quantityFractionDigit() {
                let auth = store.getters.GetAuth ?? [];
                let value = auth['quantity_precision'] ?? 2;
                return parseInt(value);
            },
            Auth: function () {
                return this.$store.getters.GetAuth;
            },
            routeMatch: function () {
                return this.$route.name;
            },
        },
        methods: {
            format_number(amount) {
                if (amount === '' || amount === 0) {
                    return 0.00;
                }
                let auth = store.getters.GetAuth ?? [];
                return `${parseFloat(amount).toFixed(parseInt(auth.currency_precision)).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
            },
            format_quantity(quantity) {
                let auth = store.getters.GetAuth ?? [];
                return `${parseFloat(quantity).toFixed(parseInt(auth.quantity_precision)).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
            },
            CheckPermission:function(sectionName) {
                let permission = this.Auth.permission ?? [];
                return permission.includes(sectionName);
            },
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
            filterBstiChart: function(data, value, matchField, findField) {
                let returnValue = 0;
                value = Math.floor(value);
                data.map((v) => {
                    if (v[matchField] == value) {
                        returnValue =  v[findField];
                    }
                });
                return parseFloat(returnValue);
            }
        },
        mounted() {
            $('.closeBtn').click(() => {
                $('.popup-wrapper-modal').addClass('d-none')
            })
        }
    });
const app = new Vue({
    el: "#app",
    components: { App },
    router,
    store,
});
