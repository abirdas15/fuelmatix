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
        parentId: null,
    },
    getters: {
        GetAuth: function (state) {
            if (state.Auth == null) {
                return JSON.parse(localStorage.getItem("userInfo"));
            }
            return state.Auth;
        },
        GetParentId(state) {
            return state.parentId;
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
    },
    actions: {
        Logout: function () {
            ApiService.POST(ApiRoutes.Logout, {}, res => {
                this.Loading = false;
                if (parseInt(res.status) === 200) {
                    localStorage.removeItem("userInfo");
                    window.location.reload();
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
    },
});
export default store;
