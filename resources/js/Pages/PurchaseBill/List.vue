<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active">
                        <router-link :to="{name: 'Dashboard'}">Home</router-link>
                    </li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Purchase List</a></li>
                    <li style="margin-left: auto;">
                        <router-link :to="{name: 'purchaseAdd'}"><i class="fa-solid fa-plus"></i> Add Purchase Bill
                        </router-link>
                    </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Purchase List</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Select Date</p>
                                        <input class="form-control date-range bg-white" type="text"
                                               name="daterange">
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Vendor</p>
                                        <select class="form-control form-select" name="vendor_id" v-model="Param.vendor_id">
                                            <option v-for="v in vendor" :value="v.id">{{ v.name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">

                                    <button type="button" class="btn btn-rounded btn-white border" @click="list"><span
                                        class="btn-icon-start text-info"><i
                                        class="fa fa-filter color-white"></i>
											</span>Filter
                                    </button>

                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="table-responsive">
                                    <div class="dataTables_wrapper no-footer">
                                        <div class="dataTables_length">
                                            <label class="d-flex align-items-center">Show
                                                <select class="mx-2" v-model="Param.limit" @change="list">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                                entries
                                            </label>
                                        </div>
                                        <div id="example3_filter" class="dataTables_filter">
                                            <label>Search:
                                                <input v-model="Param.keyword" type="search" class="" placeholder="">
                                            </label>
                                        </div>
                                        <table class="display  dataTable no-footer" style="min-width: 845px">
                                            <thead>
                                            <tr class="text-white" style="background-color: #4886EE;color:#ffffff">
                                                <th class="text-white" @click="sortData('date')"
                                                    :class="sortClass('date')">Date</th>
                                                <th class="text-white" @click="sortData('bill_id')"
                                                    :class="sortClass('bill_id')">Bill ID</th>
                                                <th class="text-white" @click="sortData('vendor_id')"
                                                    :class="sortClass('vendor_id')">Vendor</th>
                                                <th class="text-white" @click="sortData('billed')"
                                                    :class="sortClass('billed')">Billed</th>
                                                <th class="text-white" @click="sortData('paid')"
                                                    :class="sortClass('paid')">Paid</th>
                                                <th class="text-white" @click="sortData('due')"
                                                    :class="sortClass('due')">Due</th>
                                                <th class="text-white">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading == false">
                                            <tr v-for="f in listData">
                                                <td>{{ f.date }}</td>
                                                <td>{{ f.bill_id }}</td>
                                                <td>{{ f.vendor_name }}</td>
                                                <td>{{ f.total_amount }}</td>
                                                <td>{{ f.paid }}</td>
                                                <td>{{ f.due }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <button class="btn btn-primary ms-2" @click="paymentModal(f)">
                                                            Pay
                                                        </button>
                                                        <button class="btn btn-primary ms-2" @click="expandModal(f)">
                                                            Expand
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <tbody v-if="listData.length == 0 && TableLoading == false">
                                            <tr>
                                                <td colspan="10" class="text-center">No data found</td>
                                            </tr>
                                            </tbody>
                                            <tbody v-if="TableLoading == true">
                                            <tr>
                                                <td colspan="10" class="text-center">Loading....</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div class="dataTables_info" id="example3_info" role="status" aria-live="polite"
                                             v-if="paginateData != null">Showing
                                            {{ paginateData.from }} to {{ paginateData.to }} of {{ paginateData.total }}
                                            entries
                                        </div>

                                        <div class="dataTables_paginate paging_simple_numbers" id="example3_paginate">
                                            <Pagination :data="paginateData" :onChange="list"></Pagination>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="popup-wrapper-modal invoiceModal d-none">
            <form @submit.prevent="payment" class="popup-box" style="max-width: 800px">
                <button type="button" class=" btn  closeBtn"><i class="fas fa-times"></i></button>
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="amount">Amount</label>
                            <input type="text" class="w-100 form-control bg-white" name="amount" id="amount"
                                   v-model="paymentParam.amount" placeholder="Amount here">
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="description">Payment Method</label>
                            <select class="form-control form-select" v-model="paymentParam.payment_id" name="payment_id">
                                <option value="">Select Method</option>
                                <option v-for="m in allAmountCategory" :value="m.id">{{m.name}}</option>
                            </select>
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary " v-if="!Loading">Submit</button>
                <button type="button" class="btn btn-primary " disabled v-if="Loading">Submitting...</button>
            </form>
        </div>
        <div class="popup-wrapper-modal expandModal d-none">
            <form class="popup-box" style="max-width: 800px">
                <button type="button" class=" btn  closeBtn"><i class="fas fa-times"></i></button>
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="e in expandInfo">
                                <td>{{ e.product_name }}</td>
                                <td>{{ e.unit_price }}</td>
                                <td>{{ e.quantity }}</td>
                                <td>{{ e.total }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2/dist/sweetalert2.js'
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import Pagination from "../../Helpers/Pagination";
import Section from "../../Helpers/Section";
import Action from "../../Helpers/Action";

export default {
    components: {
        Pagination,
    },
    data() {
        return {
            paginateData: {},
            Param: {
                keyword: '',
                limit: 10,
                order_by: 'id',
                order_mode: 'DESC',
                page: 1,
                vendor_id: ''
            },
            Loading: false,
            TableLoading: false,
            listData: [],
            vendor: [],
            allAmountCategory: [],
            paymentParam: {
                purchase_id: '',
                amount: '',
                payment_id: ''
            },
            expandInfo: []
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        this.list();
        this.getVendor();
        this.getCategory();
    },
    computed: {
        Action() {
            return Action
        },
        Section() {
            return Section
        },
        Auth: function () {
            return this.$store.getters.GetAuth;
        },
    },
    methods: {
        getCategory: function () {
            this.categories = []
            ApiService.POST(ApiRoutes.salaryGetCategory, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.allAmountCategory = res.data;
                }
            });
        },
        paymentModal: function (f) {
            ApiService.ClearErrorHandler()
            this.paymentInfo = f
            this.paymentParam.purchase_id = f.id
            this.paymentParam.amount = parseFloat(f.due.replace(',',''))
            this.paymentParam.payment_id = ''
            $('.invoiceModal').removeClass('d-none');

        },
        payment: function () {
            this.Loading = true
            this.paymentParam.amount = parseFloat(this.paymentParam.amount);
            ApiService.POST(ApiRoutes.PurchasePay, this.paymentParam,res => {
                this.Loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    $('.invoiceModal').addClass('d-none');
                    this.list()
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        expandModal: function (f) {
            ApiService.ClearErrorHandler()
            this.expandInfo = f.purchase_item
            $('.expandModal').removeClass('d-none');

        },
        list: function (page) {
            if (page == undefined) {
                page = {
                    page: 1
                };
            }
            this.Param.page = page.page;
            this.TableLoading = true
            ApiService.POST(ApiRoutes.PurchaseList, this.Param, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.paginateData = res.data;
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        getVendor: function () {
            ApiService.POST(ApiRoutes.VendorList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.vendor = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },

        sortClass: function (order_by) {
            let cls;
            if (this.Param.order_by == order_by && this.Param.order_mode == 'DESC') {
                cls = 'sorting_desc'
            } else if (this.Param.order_by == order_by && this.Param.order_mode == 'ASC') {
                cls = 'sorting_asc'
            } else {
                cls = 'sorting'
            }
            return cls;
        },
        sortData: function (sort_name) {
            this.Param.order_by = sort_name;
            this.Param.order_mode = this.Param.order_mode == 'DESC' ? 'ASC' : 'DESC'
            this.list();
        },
    },
    mounted() {
        setTimeout(() => {
            $('.date-range').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                mode: 'range',
                onChange: (date, dateStr) => {
                    let dateArr = dateStr.split('to')
                    if (dateArr.length == 2) {
                        this.Param.start_date = dateArr[0]
                        this.Param.end_date = dateArr[1]
                    }
                }
            })
        }, 1000)
        $('#dashboard_bar').text('Purchase List')
    }

}
</script>

<style scoped>

</style>
