<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'user'}">Users</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Users</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Name:</label>
                                        <input type="text" class="form-control" name="name" v-model="param.name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Role:</label>
                                        <select class="form-control" v-model="param.role_id" name="role_id">
                                            <option value="">Choose Role</option>
                                            <option v-for="row in roles" :value="row.id" v-text="row.name"></option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Email:</label>
                                        <input type="text" class="form-control" name="email" v-model="param.email">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Password:</label>
                                        <input type="text" class="form-control" name="password" v-model="param.password">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Phone:</label>
                                        <input type="text" class="form-control" name="phone" v-model="param.phone">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Address:</label>
                                        <input type="text" class="form-control" name="address" v-model="param.address">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <div class="form-check custom-checkbox ms-1">
                                            <input type="checkbox" class="form-check-input"
                                                   id="basic_checkbox_1" v-model="param.cashier_balance">
                                            <label class="form-check-label" for="basic_checkbox_1">Cashier balance</label>
                                        </div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6" v-if="param.cashier_balance">
                                        <label class="form-label">Opening Balance:</label>
                                        <input type="text" class="form-control" name="address" v-model="param.opening_balance">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'user'}" type="button" class="btn btn-primary">Cancel</router-link>
                                    </div>
                                </div>
                            </form>
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
            param: {
                name: '',
                email: '',
                password: '',
                address: '',
                phone: '',
                cashier_balance: '',
                role_id: '',
                opening_balance: ''
            },
            loading: false,
            roles: []
        }
    },
    methods: {
        fetchRole: function() {
            ApiService.POST(ApiRoutes.RoleList, {limit: 100},res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.roles = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.userAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'user'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.fetchRole();
    },
    mounted() {
        $('#dashboard_bar').text('User Add')
    }
}
</script>

<style scoped>

</style>
