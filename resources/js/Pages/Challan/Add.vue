<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Challan</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Challan</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-label">Date:</label>
                                        <input type="text" class="form-control date bg-white" name="date" v-model="param.date">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-label">Challan No:</label>
                                        <input type="text" class="form-control" name="challan_no" v-model="param.challan_no">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" class="form-control" name="company_name" v-model="param.company_name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-label">Company Address</label>
                                        <input type="text" class="form-control" name="company_address" v-model="param.company_address">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%">Product</th>
                                            <th style="width: 25%">Quantity</th>
                                            <th style="width: 25%">Unit Price</th>
                                            <th style="width: 20%">Total</th>
                                            <th style="width: 5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(each,index) in param.items">
                                            <td>
                                                <div class="form-group">
                                                    <select class="form-control" v-model="each.product_id" :name="'items.' + index + '.product_id'">
                                                        <option value="">---Product----</option>
                                                        <option v-for="each in products" v-text="each.name" :value="each.id"></option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" @input="calculateTotal(index)" v-model="each.quantity" :name="'items.' + index + '.quantity'">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" @input="calculateTotal(index)" v-model="each.unit_price" :name="'items.' + index + '.unit_price'">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" disabled class="form-control" v-model="each.total" :name="'items.' + index + '.total'">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" @click="addMoreItem" v-if="index === param.items.length - 1" class="btn btn-primary">+</button>
                                                <button type="button" @click="removeItem(index)" v-else class="btn btn-danger">x</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
import moment from "moment/moment";
import Swal from 'sweetalert2/dist/sweetalert2.js'
import Table from "../../admin/Pages/Common/Table.vue";
export default {
    components: {Table},
    data() {
        return {
            param: {
                date: moment().format('YYYY-MM-DD'),
                challan_no: '',
                comapny_name: '',
                address: '',
                items: [
                    {
                        product_id: '',
                        quantity: '',
                        unit_price: '',
                        total: ''
                    }
                ]
            },
            loading: false,
            products: []
        }
    },
    methods: {
        calculateTotal(index) {
            this.param.items[index]['total'] = parseFloat(this.param.items[index]['quantity']) * parseFloat(this.param.items[index]['unit_price']);
        },
        removeItem(index) {
            this.param.items.splice(index,1);
        },
        addMoreItem() {
            this.param.items.push({
                product_id: '',
                quantity: '',
                unit_price: '',
                total: ''
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.Challan + '/save', this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    this.$router.push({
                        name: 'challanList'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        fetchProduct() {
            ApiService.POST(ApiRoutes.ProductList, {limit: 100}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.products = res.data.data;
                }
            });
        }
    },
    created() {
        this.fetchProduct();
    },
    mounted() {
        $('#dashboard_bar').text('Challan');
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                onChange: (date, dateStr) => {
                    this.param.date = dateStr
                }
            })
        }, 1000)
    }
}
</script>

<style scoped>

</style>
