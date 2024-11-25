import Vue from "vue";
import Vuex from "vuex";
import axios from "axios";
import VueAxios from "vue-axios";

Vue.use(Vuex);
Vue.use(VueAxios, axios);

const store = new Vuex.Store({
    state: {
        Auth: null,
        AccessToken: null,
        parentId: null,
    },
    getters: {
        GetAuth(state) {
            if (state.Auth == null) {
                return JSON.parse(localStorage.getItem("userInfo")) || null;
            }
            return state.Auth;
        },
        GetParentId(state) {
            return state.parentId;
        },
        GetAccessToken(state) {
            if (state.AccessToken == null) {
                return localStorage.getItem("FuelMatixAccessToken") || '';
            }
            return state.AccessToken;
        },
    },
    mutations: {
        PutAuth(state, data) {
            localStorage.setItem("userInfo", JSON.stringify(data));
            state.Auth = data;
        },
        PutParentCategory(state, data) {
            state.parentId = data;
        },
        PutAccessToken(state, data) {
            state.AccessToken = data;
            localStorage.setItem("FuelMatixAccessToken", data);
        },
    },
    actions: {
        Logout({ commit }) {
            commit('PutAuth', null);
            commit('PutAccessToken', null);
            localStorage.removeItem("userInfo");
            localStorage.removeItem("FuelMatixAccessToken");
            location.href = '/auth/login';
        },
    },
});

export default store;
