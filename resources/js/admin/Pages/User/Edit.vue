<template>
    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Home</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page"><router-link :to="{name: 'User'}">User</router-link></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <form @submit.prevent="updateUser">
                    <div class="card">
                        <div class="card-header">
                            <h5>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Name</label>
                                <div class="col-md-8">
                                    <input type="text" v-model="params.name" name="company.name" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Email</label>
                                <div class="col-md-8">
                                    <input type="text" v-model="params.email" name="company.email" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Phone Number</label>
                                <div class="col-md-8">
                                    <input type="text" v-model="params.phone" name="company.phone" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Address</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" v-model="params.address" name="company.address" rows="5"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button v-if="!loading.profile" type="submit" class="btn btn-primary w-100">Save</button>
                        <Button v-if="loading.profile" class="w-100 btn btn-primary" type="button">
                            <div class="spinner-border spinner-border-sm"></div>
                        </Button>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <form @submit.prevent="changePassword">
                    <div class="card">
                        <div class="card-header">
                            <h5>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row mb-3">
                                <label class="col-md-4">New Password</label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" v-model="passwordParam.password" name="password">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-4">Confirm Password</label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" v-model="passwordParam.password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button v-if="!loading.password" type="submit" class="btn btn-primary w-100">Change Password</button>
                        <Button v-if="loading.password" class="w-100 btn btn-primary" type="button">
                            <div class="spinner-border spinner-border-sm"></div>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data() {
        return {
            loading: {
                profile: false,
                password: false,
            },
            params: {

            },
            passwordParam: {
                password: '',
                password_confirmation: ''
            }
        }
    },
    created() {
        this.fetchSingleUser();
    },
    methods: {
        changePassword: function() {
            this.loading.password = true;
            let data = {
                id: this.$route.params.id,
                password: this.passwordParam.password,
                password_confirmation: this.passwordParam.password_confirmation
            };
            ApiService.POST(ApiRoutes.User + '/change/password', data, (res) => {
                this.loading.password = false;
                if (parseInt(res.status) === 200) {
                    this.passwordParam.password = '';
                    this.passwordParam.password_confirmation = '';
                    this.$toast.success(res.message);
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        fetchSingleUser: function() {
            ApiService.POST(ApiRoutes.User + '/single', {id: this.$route.params.id}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.params = res.user;
                }
            });
        },
        updateUser: function() {
            this.loading.profile = true;
            ApiService.POST(ApiRoutes.User + '/update', this.params, (res) => {
                this.loading.profile = false;
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
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
