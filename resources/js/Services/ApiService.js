import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import VueAxios from 'vue-axios'
import store from '../Store/store';

Vue.use(Vuex);
Vue.use(VueAxios, axios);
const access_token = store?.getters?.GetAccessToken;
let headers = {
    'Content-Type': 'application/json; charset=utf-8',
    'x-api-key': '_@@jbbrd2023fuelmatix@@_',
    'Authorization':  'Bearer '+ access_token
};
const ApiService = {
    POST: (url, param, callback, auth = false) => {
        const access_token = store?.getters?.GetAccessToken || localStorage.getItem('FuelMatixAccessToken');
        const headers = {
            'Content-Type': 'application/json; charset=utf-8',
            'x-api-key': '_@@jbbrd2023fuelmatix@@_',
            'Authorization': `Bearer ${access_token}`
        };

        axios.post(url, param, { headers }).then(response => {
            if (response.status === 200) {
                callback(response.data);
            }
        }).catch(err => {
            const error_code = parseInt(err.toLocaleString().replace(/\D/g, ""));
            if (error_code === 422) {
                store.dispatch('Logout');
            }
        });
    },
    GET: (url, callback, auth = false) => {
        const access_token = store?.getters?.GetAccessToken || localStorage.getItem('FuelMatixAccessToken');
        const headers = {
            'Content-Type': 'application/json; charset=utf-8',
            'x-api-key': '_@@jbbrd2023fuelmatix@@_',
            'Authorization': `Bearer ${access_token}`
        };

        axios.get(url, { headers }).then(response => {
            if (response.status === 200) {
                callback(response.data);
            }
        }).catch(err => {
            const error_code = parseInt(err.toLocaleString().replace(/\D/g, ""));
            if (error_code === 422) {
                store.dispatch('Logout');
            }
        });
    },
    DOWNLOAD: (url, param, headersAxios, callback, auth = false) => {
        const access_token = store?.getters?.GetAccessToken || localStorage.getItem('FuelMatixAccessToken');
        const headers = {
            'Content-Type': 'application/json; charset=utf-8',
            'x-api-key': '_@@jbbrd2023fuelmatix@@_',
            'Authorization': `Bearer ${access_token}`
        };

        axios.post(url, param, {
            headers,
            responseType: 'blob'
        }).then(response => {
            if (response.status === 200) {
                callback(response.data);
            }
        }).catch(err => {
            const error_code = parseInt(err.toLocaleString().replace(/\D/g, ""));
            if (error_code === 422) {
                store.dispatch('Logout');
            }
        });
    },
    UPLOAD: (url, media, callback, auth = false) => {
        const access_token = store?.getters?.GetAccessToken || localStorage.getItem('FuelMatixAccessToken');
        const headers = {
            "Content-Type": "multipart/form-data",
            'Authorization': `Bearer ${access_token}`
        };

        axios.post(url, media, { headers }).then(response => {
            if (response.status === 200) {
                callback(response.data);
            }
        }).catch(err => {
            const error_code = parseInt(err.toLocaleString().replace(/\D/g, ""));
            if (error_code === 422) {
                store.dispatch('Logout');
            }
        });
    },
    ErrorHandler(errors) {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').html('');
        $('.error-report-g').html('');
        $.each(errors, (i, v) => {
            if (i === 'error') {
                $('.error-report-g').html('<p class="alert alert-danger">' + v + '</p>');
            } else {
                $('[name="' + i + '"]').addClass('is-invalid');
                $('[name="' + i + '"]').closest('.form-group').find('.invalid-feedback').html(v);
            }
        });
    },
    ClearErrorHandler() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').html('');
    }
};
export default ApiService;
