<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'BulkSale'}">Bulk Sale</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Bulk Sale</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Pay Order:</label>
                                        <select class="form-control" name="pay_order_id" id="pay_order_id"  v-model="param.pay_order_id">
                                            <option value="">Select PayOrder</option>
                                            <option v-for="d in payOrders" :value="d.id">{{d.number}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Company:</label>
                                        <select class="form-control" name="company_id" id="company_id"  v-model="param.company_id">
                                            <option value="">Select Company</option>
                                            <option v-for="d in companies" :value="d.id">{{d.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th style="width: 20%">Product Name</th>
                                            <th style="width: 20%">Order Quantity</th>
                                            <th style="width: 20%">Selling Price</th>
                                            <th style="width: 20%">Sale Quantity</th>
                                            <th style="width: 20%">Total</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(each,index) in param.products">
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" v-model="each.product_name" disabled>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" v-model="each.order_quantity" disabled>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" v-model="each.selling_price" disabled>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" :name="'products.' + index + '.sale_quantity'" @input="calculatePrice(index)" v-model="each.sale_quantity" class="form-control">
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
                                                <button @click="removeProduct(index)" type="button" class="btn btn-danger">x</button>
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">Total</th>
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
            param: {
                pay_order_id: '',
                company_id: '',
                products: [

                ]
            },
            loading: false,
            companies: [],
            payOrders: []
        }
    },
    watch: {
        'param.pay_order_id': function() {
            this.fetchPayOrderProduct();
        }
    },
    computed: {
        total() {
            let total = 0;
            this.param.products.map((v) => {
                if (v.total !== '') {
                    total += parseFloat(v.total);
                }
            });
            return total.toFixed(2);
        }
    },
    methods: {
        fetchPayOrderProduct: function() {
            this.param.products = [];
            ApiService.POST(ApiRoutes.PayOrder + '/product', {pay_order_id: this.param.pay_order_id}, (res) => {
                if (parseInt(res.status) === 200) {
                    res.data.map((data) => {
                        this.param.products.push({
                            id: data.id,
                            product_id: data.product_id,
                            product_name: data.product_name,
                            selling_price: data.selling_price,
                            order_quantity: data.order_quantity,
                            sale_quantity: '',
                            total: ''
                        });
                    });
                }
            });
        },
        calculatePrice: function (index) {
            let total = '';
            if (this.param.products[index].selling_price !== '' && this.param.products[index].sale_quantity !== '') {
                total = this.param.products[index].selling_price * this.param.products[index].sale_quantity;
            }
            this.param.products[index].total = total;
        },
        removeProduct: function (index) {
            this.param.products.splice(index, 1);
        },
        fetchPayOrder: function () {
            ApiService.POST(ApiRoutes.PayOrderLatest, {limit: 5000, page: 1}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.payOrders = res.data;
                }
            });
        },
        fetchCompany: function () {
            ApiService.POST(ApiRoutes.CreditCompanyList, {limit: 5000, page: 1}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.companies = res.data.data;
                }
            });
        },
        save: function () {
            let count = 0;
            this.param.products.map((product) => {
                if (product.sale_quantity === '') {
                    count++;
                }
            });
            if (this.param.products.length === count) {
                this.$toast.warning('At least one quantity is required.');
                return;
            }
            ApiService.ClearErrorHandler();
            this.loading = true;
            ApiService.POST(ApiRoutes.BulkSale + '/save', this.param, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'BulkSale'
                    });
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.fetchPayOrder();
        this.fetchCompany();
    },
    mounted() {
        $('#dashboard_bar').text('Bulk Sale')
    }
}
</script>

<style scoped>

</style>
