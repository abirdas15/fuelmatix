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
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Bank:</label>
                                        <select class="form-control" name="bank_id" id="bank_id"  v-model="param.bank_id">
                                            <option value="">Select Bank</option>
                                            <option v-for="d in listDataBank" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Vendor:</label>
                                        <select class="form-control" name="vendor_id" id="vendor_id"  v-model="param.vendor_id">
                                            <option value="">Select Vendor</option>
                                            <option v-for="d in listDataVendor" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Amount:</label>
                                        <input type="text" class="form-control" name="amount" v-model="param.amount">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th style="width: 20%">Product Name</th>
                                            <th style="width: 20%">Unit Price</th>
                                            <th style="width: 20%">Quantity</th>
                                            <th style="width: 20%">Total</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(each,index) in param.products">
                                            <td>
                                                <div class="form-group">
                                                    <select class="form-control" v-model="each.product_id" :name="'products.' + index + '.product_id'" @change="changeProduct($event, index)">
                                                        <option value="">Choose Product</option>
                                                        <option v-for="row in products" :value="row.id" v-text="row.name"></option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" :name="'products.' + index + '.unit_price'" v-model="each.unit_price" class="form-control" disabled>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" :name="'products.' + index + '.quantity'" @input="calculatePrice(index)" v-model="each.quantity" class="form-control">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" :name="'products.' + index + '.total'" v-model="each.total" class="form-control" disabled>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <button @click="addProduct" v-if="index == 0" type="button" class="btn btn-info">+</button>
                                                <button @click="removeProduct(index)" v-if="index != 0" type="button" class="btn btn-danger">x</button>
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total</th>
                                            <th>{{ total }}</th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="col-sm-6"></div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'PayOrder'}" type="button" class="btn btn-danger">Cancel</router-link>
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
            products: []
        }
    },
    computed: {
        total() {
            let total = 0;
            this.param.products.map((v) => {
                if (v.total != '') {
                    total += parseFloat(v.total);
                }
            });
            return total.toFixed(2);
        }
    },
    methods: {
        calculatePrice: function(index) {
            let total = '';
            if (this.param.products[index].unit_price != '' && this.param.products[index].quantity != '') {
                total = this.param.products[index].unit_price * this.param.products[index].quantity;
            }
            this.param.products[index].total = total;
        },
        changeProduct: function(event, index) {
            let id = event.target.value;
            let selectedProductIndex;
            this.products.map((v, i) => {
                if (v.id == id) {
                    selectedProductIndex = i;
                }
            });
            this.param.products[index].unit_price = this.products[selectedProductIndex].buying_price;
            this.param.products[index].expense_category_id = this.products[selectedProductIndex].expense_category_id;
            this.calculatePrice(index);
        },
        removeProduct: function(index) {
            this.param.products.splice(index, 1);
        },
        addProduct: function() {
            this.param.products.push({
                product_id: '',
                unit_price: '',
                quantity: '',
                total: ''
            });
        },
        fetchProducts: function () {
            ApiService.POST(ApiRoutes.ProductList, {limit: 5000, page: 1},res => {
                if (parseInt(res.status) === 200) {
                    this.products = res.data.data;
                }
            });
        },
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
        this.fetchProducts();
    },
    mounted() {
        $('#dashboard_bar').text('Pay Order Edit')
    }
}
</script>

<style scoped>

</style>
