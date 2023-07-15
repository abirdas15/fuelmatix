<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'PayOrder'}">Pay Order</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Edit</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Pay Order</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Bank:</label>
                                        <select class="form-control" name="bank_id" id="bank_id"  v-model="param.bank_id">
                                            <option value="">Select Bank</option>
                                            <option v-for="d in listDataBank" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Vendor:</label>
                                        <select class="form-control" name="vendor_id" id="vendor_id"  v-model="param.vendor_id">
                                            <option value="">Select Bank</option>
                                            <option v-for="d in listDataVendor" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Amount:</label>
                                        <input type="text" class="form-control" name="amount" v-model="param.amount">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Unit Quantity:</label>
                                        <input type="text" class="form-control" name="quantity" v-model="param.quantity">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Pay Order Number:</label>
                                        <input type="text" class="form-control" name="name" v-model="param.number">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="col-sm-6"></div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'PayOrder'}" type="button" class="btn btn-primary">Cancel</router-link>
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
            id: '',
            listDataBank: [],
            listDataVendor: [],
        }
    },
    methods: {
        getBank: function () {
            ApiService.POST(ApiRoutes.BankList, {limit: 5000, page: 1},res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listDataBank = res.data.data;
                }
            });
        },
        getVendor: function () {
            ApiService.POST(ApiRoutes.VendorList, {limit: 5000, page: 1},res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listDataVendor = res.data.data;
                }
            });
        },
        getSingle: function () {
            ApiService.POST(ApiRoutes.PayOrderSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.PayOrderEdit, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'PayOrder'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.id = this.$route.params.id
        this.getSingle()
        this.getBank()
        this.getVendor()
    },
    mounted() {
        $('#dashboard_bar').text('Pay Order Edit')
    }
}
</script>

<style scoped>

</style>
