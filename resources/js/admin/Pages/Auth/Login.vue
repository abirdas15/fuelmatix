
<template>
    <div class="wrapper">
        <!--start content-->
        <main class="authentication-content">
            <div class="container-fluid">
                <div class="authentication-card">
                    <div class="card shadow rounded-0 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-lg-6 bg-login d-flex align-items-center justify-content-center">
                                <img src="/images/login-img.jpg" class="img-fluid" alt="">
                            </div>
                            <div class="col-lg-6">
                                <div class="card-body p-4 p-sm-5">
                                    <h5 class="card-title">Sign In</h5>
                                    <p class="card-text mb-5">See your growth and get consulting support!</p>
                                    <form class="form-body" @submit.prevent="login">
                                        <div class="row g-3">
                                            <div class="col-12 form-group">
                                                <label for="inputEmailAddress" class="form-label">Email Address</label>
                                                <input type="text" v-model="params.email" name="email" class="form-control" id="inputEmailAddress" placeholder="Email Address">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-12 form-group">
                                                <label for="inputChoosePassword" class="form-label">Enter Password</label>
                                                <input type="password" name="password" v-model="params.password" class="form-control" id="inputChoosePassword" placeholder="Enter Password">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked="">
                                                    <label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
                                                </div>
                                            </div>
                                            <div class="col-6 text-end">
                                                <a href="javascript:void(0)">Forgot Password ?
                                                </a>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" v-if="!loading" class="btn btn-primary">Sign In</button>
                                                    <Button v-if="loading" class="w-100 btn btn-primary" type="button">
                                                        <div class="spinner-border spinner-border-sm"></div>
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!--end page main-->
    </div>
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data() {
        return {
            params: {
                email: '',
                password: ''
            },
            loading: false,
        }
    },
    methods: {
        login: function() {
            this.loading = true;
            ApiService.POST(ApiRoutes.Login, this.params, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 200) {
                    window.location.reload();
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        }
    }
}
</script>
<style scoped>

</style>
