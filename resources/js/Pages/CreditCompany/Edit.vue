<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'CreditCompany'}">Credit Company</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Edit</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Credit Company</h4>
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
                                        <label class="form-label">Parent Company:</label>
                                        <select class="form-control" v-model="param.parent_id">
                                            <option value="">Select One</option>
                                            <option v-for="each in companies" :value="each.id" v-text="each.name"></option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Email:</label>
                                        <input type="text" class="form-control" name="email" v-model="param.email">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Contact Person:</label>
                                        <input type="text" class="form-control" name="email" v-model="param.contact_person">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Phone:</label>
                                        <input type="text" class="form-control" name="phone" v-model="param.phone">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Address:</label>
                                        <input type="text" class="form-control" name="phone" v-model="param.address">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Credit Limit:</label>
                                        <input type="text" class="form-control" name="credit_limit" v-model="param.credit_limit">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3 form-group col-md-6">
                                        <label class="form-label">Opening Balance:</label>
                                        <input type="text" class="form-control" name="opening_balance" v-model="param.opening_balance">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Selling Price</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(each,index) in param?.product_price">
                                            <td width="20%">
                                                <select class="form-control" v-model="each.product_id">
                                                    <option>Select Product</option>
                                                    <option v-for="product in products" :value="product.id" v-text="product.name"></option>
                                                </select>
                                            </td>
                                            <td width="20%"><input v-model="each.price" type="text" class="form-control"></td>
                                            <td>
                                                <button @click="addProductPrice" v-if="index == 0" type="button" class="btn btn-primary">+</button>
                                                <button @click="removeProductPrice(index)" v-if="index != 0" type="button" class="btn btn-danger">x</button>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row" style="text-align: right;">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'CreditCompany'}" type="button" class="btn btn-primary">Cancel</router-link>
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
            listData: [],
            products: [],
            companies: []
        }
    },
    methods: {
        fetchCompany: function() {
            ApiService.POST(ApiRoutes.CreditCompanyList, {limit: 500}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.companies = res.data.data;
                }
            });
        },
        fetchProduct: function() {
            ApiService.POST(ApiRoutes.ProductList, {limit: 500}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.products = res.data.data;
                }
            });
        },
        removeProductPrice: function(index) {
            this.param.product_price.splice(index, 1);
        },
        addProductPrice: function() {
            this.param.product_price.push({
                product_id: '',
                price: ''
            })
        },
        getSingle: function () {
            ApiService.POST(ApiRoutes.CreditCompanySingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                    if (res.data.product_price.length == 0) {
                        this.param.product_price.push({
                            product_id: '',
                            price: ''
                        })
                    }
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.CreditCompanyEdit, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'CreditCompany'
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
        this.fetchProduct();
        this.fetchCompany();
    },
    mounted() {
        $('#dashboard_bar').text('Credit Company Edit')
    }
}
</script>

<style scoped>

</style>
