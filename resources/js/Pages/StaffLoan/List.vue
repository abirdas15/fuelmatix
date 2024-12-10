<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Staff Loan List</a></li>
                    <li v-if="CheckPermission(Section.STAFF_LOAN + '-' + Action.VIEW)" style="margin-left: auto;"><router-link :to="{name: 'StaffLoanAdd'}"><i class="fa-solid fa-plus"></i> Add New Staff Loan</router-link></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Staff Loan List</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mt-4">
                                <div class="table-responsive">
                                    <div class="dataTables_wrapper no-footer">
                                        <div class="dataTables_length">
                                            <label class="d-flex align-items-center">Show
                                                <select class="mx-2"  v-model="Param.limit" @change="list">
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
                                                <th class="text-white" @click="sortData('staff_name')" :class="sortClass('staff_name')">Staff Name</th>
                                                <th class="text-white" @click="sortData('loan_amount')" :class="sortClass('loan_amount')">Loan Amount</th>
                                                <th class="text-white" @click="sortData('payment_amount')" :class="sortClass('payment_amount')">Payment Amount</th>
                                                <th class="text-white" @click="sortData('due')" :class="sortClass('due')">Due</th>
                                                <th class="text-white" >Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading === false">
                                            <tr v-for="f in listData">
                                                <td >{{f.staff_name}}</td>
                                                <td >{{f.loan_amount_format}}</td>
                                                <td >{{f.payment_amount_format}}</td>
                                                <td >{{f.due_amount_format}}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a v-if="f.due_amount > 0" class="btn btn-sm btn-primary me-2" @click="paymentModal(f)">
                                                            <i class="fa fa-money"></i>
                                                        </a>
                                                        <router-link :to="{name: 'StaffLoanView', params: {id: f.account_id}}"  class="btn btn-info shadow btn-sm sharp">
                                                            <i class="fa fa-eye"></i>
                                                        </router-link>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <tbody v-if="listData.length === 0 && TableLoading === false">
                                            <tr>
                                                <td colspan="10" class="text-center">No data found</td>
                                            </tr>
                                            </tbody>
                                            <tbody v-if="TableLoading === true">
                                            <tr>
                                                <td colspan="10" class="text-center">Loading....</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div class="dataTables_info" id="example3_info" role="status" aria-live="polite" v-if="paginateData != null">Showing
                                            {{paginateData.from}} to {{ paginateData.to }} of {{ paginateData.total }} entries
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
                                <option v-for="m in categories" :value="m.id">{{m.name}}</option>
                            </select>
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary " v-if="!Loading">Submit</button>
                <button type="button" class="btn btn-primary " disabled v-if="Loading">Submitting...</button>
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
                status: 'end',
            },
            Loading: false,
            TableLoading: false,
            listData: [],
            paymentInfo: {},
            categories: [],
            paymentParam: {
                id: '',
                amount: '',
                payment_id: ''
            },
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        this.list();
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
        payment: function () {
            this.Loading = true
            ApiService.POST(ApiRoutes.StaffLoan + '/payment', this.paymentParam,res => {
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
        getCategory: function () {
            this.categories = []
            ApiService.POST(ApiRoutes.salaryGetCategory, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.categories = res.data;
                }
            });
        },
        paymentModal: function (f) {
            this.paymentInfo = f
            this.paymentParam.id = f.id
            this.paymentParam.amount = f.due_amount;
            $('.invoiceModal').removeClass('d-none');

        },
        openModalDelete(data) {
            Swal.fire({
                title: 'Are you sure you want to delete?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.Delete(data)
                }
            })
        },
        list: function (page) {
            if (page == undefined) {
                page = {
                    page: 1
                };
            }
            this.Param.page = page.page;
            this.TableLoading = true
            ApiService.POST(ApiRoutes.StaffLoan + '/list', this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.paginateData = res.data;
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        Delete: function (data) {
            ApiService.POST(ApiRoutes.ShiftSaleDelete, {id: data.id },res => {
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    this.list()
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
        $('#dashboard_bar').text('Staff Loan')
    }
}
</script>

<style scoped>

</style>
