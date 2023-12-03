<template>
    <div class="authincation h-100 align-items-center"
         style="background-image: url('/images/fuelbg.jpg'); background-repeat: no-repeat;width: 100%;background-position: center; background-size: cover;">
        <div class="container h-100">
            <div class="row justify-content-start h-100">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <div class="text-center mb-3">
                                        <a href="javascript:void(0)">
                                            <img src="/images/fuelBL.jpeg"alt="">
                                        </a>
                                    </div>
                                    <h4 class="text-center mb-4">Sign in your account</h4>
                                    <form @submit.prevent="Login">
                                        <div class="mb-3 form-group">
                                            <label class="mb-1"><strong>Email or Phone</strong></label>
                                            <input type="text" placeholder="hello@example.com" name="email" class="form-control" v-model="param.email" autocomplete="new-email">
                                            <small class="error-report text-danger"></small>
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label class="mb-1"><strong>Password</strong></label>
                                            <input type="password" class="form-control" name="password" v-model="param.password" autocomplete="password">
                                            <small class="error-report text-danger"></small>
                                        </div>
                                        <div class="row d-flex justify-content-between mt-4 mb-2">
                                            <div class="mb-3">
                                                <div class="form-check custom-checkbox ms-1">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="basic_checkbox_1" v-model="param.remember">
                                                    <label class="form-check-label" for="basic_checkbox_1">Remember my
                                                        preference</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <router-link :to="{name: 'Forgot'}">Forgot Password?</router-link>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button v-if="!Loading" type="submit" class="btn btn-primary btn-block">Sign Me In</button>
                                            <button v-else type="button" disabled class="btn btn-primary btn-block">Sign Me In...</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
export default {
    data() {
        return {
            Loading: false,
            param: {
                email: '',
                password: '',
                remember: 0
            },

        }
    },
    mounted() {

    },
    methods: {
        Login: function () {
            this.Loading = true;
            ApiService.POST(ApiRoutes.Login, this.param, res => {
                if (parseInt(res.status) === 200) {
                    this.$store.commit('PutAuth', res.data);
                    this.$toast.success(res.msg);
                    window.location.reload();
                } else {
                    this.Loading = false;
                    ApiService.ErrorHandler(res.error);
                }
            });
        }
    }
}
</script>
