<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'user'}">Company</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Setup</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Company Setup</h4>
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
                                        <label class="form-label">Email:</label>
                                        <input type="text" class="form-control" name="email" v-model="param.email">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Phone:</label>
                                        <input type="text" class="form-control" name="phone_number" v-model="param.phone_number">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Address:</label>
                                        <input type="text" class="form-control" name="address" v-model="param.address">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Sale Mismatch Allow:</label>
                                        <input type="text" class="form-control" name="sale_mismatch_allow" v-model="param.sale_mismatch_allow">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Expense Approve:</label>
                                        <input type="text" class="form-control" name="expense_approve" v-model="param.expense_approve">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <div class="form-check custom-checkbox ms-1" style="margin-top: 3rem;">
                                            <input type="checkbox" class="form-check-input"
                                                   id="header_text" v-model="param.header_text">
                                            <label class="form-check-label" for="header_text">Header Text</label>
                                        </div>
                                        <div class="form-check custom-checkbox ms-1" style="margin-top: 1rem;">
                                            <input type="checkbox" class="form-check-input"
                                                   id="footer_text" v-model="param.footer_text">
                                            <label class="form-check-label" for="footer_text">Footer Text</label>
                                        </div>
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
            param: {},
            loading: false,
        }
    },
    methods: {
        getSingle: function () {
            ApiService.POST(ApiRoutes.companySingle, this.param,res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.companyAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message)
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.getSingle()
    },
    mounted() {
        $('#dashboard_bar').text('Company settings')
    }
}
</script>

<style scoped>

</style>
