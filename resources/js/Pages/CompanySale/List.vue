<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Company Sale</a></li>
                    <li style="margin-left: auto;" v-if="CheckPermission(Section.COMPANY_SALE + '-' + Action.CREATE)">
                        <a class="btn btn-success text-white" style="padding: 8px 20px" v-if="selectedIDs.length > 0 && !generateLoading" @click="generateInvoice()" href="javascript:void(0)">Generate Invoice</a>
                        <a class="btn btn-success text-white" style="padding: 8px 20px" v-if="selectedIDs.length > 0 && generateLoading" href="javascript:void(0)">Generating....</a>
                    </li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Company Sale</h4>
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
                                                <th>
                                                    <input type="checkbox" class="form-check-input" @change="selectAll($event)">
                                                </th>
                                                <th class="text-white" @click="sortData('created_at')" :class="sortClass('created_at')">Date</th>
                                                <th class="text-white" @click="sortData('name')" :class="sortClass('name')">Company</th>
                                                <th class="text-white" @click="sortData('car_number')" :class="sortClass('car_number')">Car Number</th>
                                                <th class="text-white" @click="sortData('voucher_no')" :class="sortClass('voucher_no')">Voucher No</th>
                                                <th class="text-white" @click="sortData('amount')" :class="sortClass('amount')">Amount</th>
                                                <th class="text-white" style="width: 375px">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading == false">
                                            <tr v-for="(f, i) in listData">
                                                <td>
                                                    <input v-if="!f.invoice_id" type="checkbox" :checked="isExist(f.id)" class="form-check-input" @change="selectIds($event, f.id)">
                                                </td>
                                                <td >{{f.created_at}}</td>
                                                <td><a href="javascript:void(0);">{{f.name}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.car_number}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.voucher_no}}</a></td>
                                                <td><a href="javascript:void(0);">{{f?.amount_format}}</a></td>
                                                <td>
                                                    <template v-if="f.module == 'shift sale' && CheckPermission(Section.COMPANY_SALE + '-' + Action.CREATE)">
                                                        <button class="btn btn-sm btn-primary" v-if="!f.invoice_id"  @click="tableAction('expand', f)">Expand</button>
                                                    </template>
                                                    <router-link v-if="CheckPermission(Section.INVOICE + '-' + Action.VIEW) && f.invoice_id" :to="{name: 'InvoicesView', params: { id: f.invoice_id }}" class="btn btn-sm btn-info" @click="tableAction('view', f)">View Invoices</router-link>
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
        <div class="popup-wrapper-modal createExpand d-none">
            <form @submit.prevent="expand" class="popup-box" style="max-width: 800px">
                <button type="button" class=" btn  closeBtn"><i class="fas fa-times"></i></button>
                <div class="row align-items-center">
                    <div class="col-sm-3">
                        <div class="input-wrapper form-group">
                            <label for="description"><strong>Car Number</strong></label>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-wrapper form-group">
                            <label for="description"><strong>Voucher Number</strong></label>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-wrapper form-group">
                            <label for="description"><strong>Amount</strong></label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="input-wrapper form-group">
                            <label for="description"><strong>Action</strong></label>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center" v-for="(e, i) in expandParam.data">
                    <div class="col-sm-3">
                        <div class="input-wrapper form-group mb-3">
                            <select class="form-control" v-model="e.description" :name="'description.' + i">
                                <option value="">Select Car</option>
                                <option v-for="each in cars" :value="each.car_number" v-text="each.car_number"></option>
                            </select>
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-wrapper form-group mb-3">
                            <input type="text" class="w-100 form-control" :name="'data.' + i + '.voucher_number'" id="description"
                                   v-model="e.voucher_number" placeholder="Voucher Number">
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-wrapper form-group mb-3">
                            <input type="text" class="w-100 form-control" :name="'data.' + i + '.amount'" id="amount"
                                   v-model="e.amount" placeholder="Amount here">
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" v-if="i == 0" class="btn btn-primary" @click="addMore">+</button>
                        <button v-else class="btn btn-danger"  style="height: 54px" type="button" @click="spliceData(i)">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div class="align-items-center offset-6 col-sm-3">
                    <label class="mb-0"><strong>Total: {{ totalAmount.toLocaleString() }}</strong></label>
                </div>
                <div class="align-items-center offset-6 col-sm-3">
                    <label class="text-danger"><strong>Missing: {{ missingAmount.toLocaleString() }}</strong></label>
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
import _ from "lodash";
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
                order_by: 'transactions.id',
                order_mode: 'DESC',
                page: 1,
            },
            Loading: false,
            generateLoading: false,
            TableLoading: false,
            listData: [],
            selectedData: null,
            expandParam: {
                id: '',
                data: [
                    {
                        description: '',
                        voucher_number: '',
                        amount: ''
                    }
                ]
            },
            selectedIDs: [],
            cars: []

        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        this.list();
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
        missingAmount: function() {
            if (this.selectedData != null) {
                return parseFloat(this.selectedData.amount) - parseFloat(this.totalAmount)
            }
            return 0;
        },
        totalAmount: function() {
            let total = 0;
            this.expandParam.data.map((v) => {
                if (v.amount != '') {
                    total += parseFloat(v.amount);
                }
            });
            return total;
        },
    },
    methods: {
        fetchCar: function() {
            ApiService.POST(ApiRoutes.CarList, {company_id:  this.selectedData.category_id, limit: 500}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.cars = res.data.data;
                }
            });
        },
        generateInvoice: function () {
            this.generateLoading = true
            ApiService.POST(ApiRoutes.invoiceGenerate, {ids: this.selectedIDs},res => {
                this.generateLoading = false
                if (parseInt(res.status) === 200) {
                    this.selectedIDs = []
                    this.$toast.success(res.message);
                    this.list();
                } else {
                    this.$toast.error(res.error)
                }
            });
        },
        isExist: function (id) {
            let index = this.selectedIDs.indexOf(id)
            return index > -1;
        },
        selectIds: function (e, id) {
            if (e.target.checked) {
                this.selectedIDs.push(id)
            } else {
                this.selectedIDs.splice(this.selectedIDs.indexOf(id), 1)
            }
        },
        selectAll: function (e) {
            if (e.target.checked) {
                this.listData.map(v => {
                    if (!v.invoice_id) {
                        this.selectedIDs.push(v.id)
                    }
                })
            } else {
                this.selectedIDs = []
            }
        },
        expand: function () {
            ApiService.POST(ApiRoutes.TransactionSplit, this.expandParam,res => {
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    $('.createExpand').addClass('d-none')
                    this.list()
                } else if (parseInt(res.status) === 300) {
                    this.$toast.error(res.message)
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        tableAction: function (e, data, i = null) {
            if (e == 'expand') {
                this.selectedData = data
                this.expandParam.id = data.id
                this.expandParam.data = []
                this.expandParam.data.push({amount: '', description: '', voucher_number: ''})
                $('.createExpand').removeClass('d-none')
                this.fetchCar();
            } else if (e == 'generate') {
                if (data.is_invoice) {
                    this.$toast.success('Invoice already Created');
                    return;
                }
                $('.genloading'+ i).toggle()
                ApiService.POST(ApiRoutes.invoiceGenerate, {id: data.id},res => {
                    $('.genloading'+ i).toggle()
                    if (parseInt(res.status) === 200) {
                        this.$toast.success(res.message);
                        this.list()
                    } else {
                        ApiService.ErrorHandler(res.errors);
                    }
                });

            }
        },
        addMore: function () {
            this.expandParam.data.push({amount: '', description: '', voucher_number: ''})
        },
        spliceData: function (i) {
            this.expandParam.data.splice(i, 1)
        },
        list: function (page) {
            if (page == undefined) {
                page = {
                    page: 1
                };
            }
            this.Param.page = page.page;
            this.TableLoading = true
            ApiService.POST(ApiRoutes.companySaleList, this.Param,res => {
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
            ApiService.POST(ApiRoutes.companySaleDelete, {id: data.id },res => {
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
        $('#dashboard_bar').text('Company Sale')
    }
}
</script>

<style scoped>

</style>
