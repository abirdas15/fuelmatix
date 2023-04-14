import Vue from "vue";
import Vuex from "vuex";
import axios from "axios";
import VueAxios from "vue-axios";
import ApiService from "../Services/ApiService";
import ApiRoutes from "../Services/ApiRoutes";

Vue.use(Vuex);
Vue.use(VueAxios, axios);

Vue.use(Vuex);

const store = new Vuex.Store({
    state: {
        Auth: null,
        AccessToken: null,
        CompanyId: null,
    },
    getters: {
        GetAuth: function (state) {
            if (state.Auth == null) {
                return JSON.parse(localStorage.getItem("userInfo"));
            }
            return state.Auth;
        },
        GetCompanyId(state) {
            if (state.CompanyId == null) {
                return localStorage.getItem("company_id");
            }
            return state.CompanyId;
        },
    },
    mutations: {
        PutAuth(state, data) {
            localStorage.setItem("userInfo", JSON.stringify(data));
            state.Auth = data;
        },
        PutCompanyId(state, data) {
            localStorage.setItem("company_id", data);
            state.CompanyId = data;
        },
    },
    actions: {

    },
});
export default store;
