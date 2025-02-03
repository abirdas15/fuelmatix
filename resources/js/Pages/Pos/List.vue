<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Sale History List</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Sale History List</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <input class="form-control input-daterange-datepicker date" type="text" placeholder="Select Date">
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <button type="button" class="btn btn-rounded btn-white border" @click="list">
                                        <span class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i></span>Filter
                                    </button>
                                </div>
                                <div class="col-xl-3 mb-3 d-flex" v-if="Param.ids.length > 0">
                                    <div class="me-2">
                                        <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf">
                                            <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                        </button>
                                        <button class="btn btn-primary" v-if="loadingFile">
                                            <i class="fa fa-file-pdf-o" aria-hidden="true">...</i>
                                        </button>
                                    </div>
                                    <div class="me-2">
                                        <button class="btn btn-primary" v-if="!excelLoading" @click="downloadExcel">
                                            <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                                        </button>
                                        <button class="btn btn-primary" v-if="excelLoading">
                                            <i class="fa fa-file-excel-o" aria-hidden="true">...</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                                                <th class="text-white" @click="sortData('date')" :class="sortClass('date')">Date</th>
                                                <th class="text-white" @click="sortData('invoice_number')" :class="sortClass('invoice_number')">Invoice Number</th>
                                                <th class="text-white" @click="sortData('company_name')" :class="sortClass('company_name')">Company Name</th>
                                                <th class="text-white" @click="sortData('payment_method')" :class="sortClass('payment_method')">Payment Method</th>
                                                <th class="text-white" @click="sortData('voucher_number')" :class="sortClass('voucher_number')">Voucher Number</th>
                                                <th class="text-white" @click="sortData('Car Number')" :class="sortClass('voucher_number')">Car Number</th>
                                                <th class="text-white">Product Name</th>
                                                <th class="text-white">Quantity</th>
                                                <th class="text-white" @click="sortData('total_amount')" :class="sortClass('total_amount')">Total </th>
                                                <th class="text-white" @click="sortData('user_name')" :class="sortClass('user_name')" >User</th>
                                                <th class="text-white text-end" >Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading === false">
                                            <tr v-for="f in listData">
                                                <td>
                                                    <input type="checkbox" :checked="isExist(f.id)" class="form-check-input" @change="selectIds($event, f.id)">
                                                </td>
                                                <td >{{f.date}}</td>
                                                <td><a href="javascript:void(0);">{{f.invoice_number}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.company_name}}</a></td>
                                                <td><a href="javascript:void(0);">{{f?.payment_method}}</a></td>
                                                <td><a href="javascript:void(0);">{{f?.voucher_number}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.car_number}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.product_name}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.quantity}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.total_amount}}</a></td>
                                                <td><a href="javascript:void(0);">{{f?.user_name}}</a></td>
                                                <td>
                                                    <div class="d-flex justify-content-end">
                                                        <a  href="javascript:void(0)" :class="'invoice' + f.id" @click="printInvoice(f.id)" class="btn btn-success shadow btn-xs sharp  me-1">
                                                            <i class="fa fa-print"></i>
                                                        </a>
                                                        <a style="display: none" :class="'invoice' + f.id" class="btn btn-primary shadow btn-xs sharp  me-1">
                                                            <i class="fa fa-spinner fa-spin"></i>
                                                        </a>
                                                        <router-link  :to="{name: 'PosEdit', params: { id: f.id }}" class=" btn btn-primary shadow btn-xs sharp me-1">
                                                            <i class="fas fa-pencil"></i>
                                                        </router-link>
                                                        <router-link  :to="{name: 'PosView', params: { id: f.id }}" class=" btn btn-primary shadow btn-xs sharp me-1">
                                                            <i class="fas fa-eye"></i>
                                                        </router-link>
                                                        <a  v-if="CheckPermission(Section.POS + '-' + Action.DELETE)" href="javascript:void(0)"  @click="openModalDelete(f)" class="btn btn-danger shadow btn-xs sharp">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
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
        <div id="print" v-if="singleSaleData" style="margin: 0; padding: 0;">
            <header class="text-center">
                <strong>{{ singleSaleData.company?.name }}</strong>
                <br>
                <small>
                    {{ singleSaleData.company?.address }}
                </small>
                <br>
                <small>
                    Phone: {{ singleSaleData?.company?.phone_number }}
                </small>
            </header>
            <p>Invoice Number : {{ singleSaleData?.invoice_number }}</p>
            <table class="bill-details">
                <tbody>
                <tr>
                    <td>Date : <span>{{ singleSaleData?.date_format }}</span></td>
                </tr>
                <tr>
                    <td><strong>Vehicle No: {{ singleSaleData?.customer_name }}</strong></td>
                </tr>
                </tbody>
            </table>

            <table class="items">
                <thead>
                <tr>
                    <th style="width: 30%" class="heading name">Item</th>
                    <th style="width: 25%" class="heading qty">Qty</th>
                    <th style="width: 25%" class="heading rate">Price</th>
                    <th style="width: 30%" class="heading amount">Subtotal</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="p in singleSaleData.products">
                    <td>{{ p?.product_name }}</td>
                    <td style="text-align: center">{{ p.quantity_format }}</td>
                    <td class="price" style="text-align: center">{{ p.price }}</td>
                    <td class="price" style="text-align: center">{{ p.subtotal_format }}</td>
                </tr>
                <tr>
                    <th colspan="3" class="total text">Total</th>
                    <th class="total price" style="text-align: center">{{singleSaleData.total_amount_format}}</th>
                </tr>
                </tbody>
            </table>
            <section>
                <p>
                    Paid by : <span>{{ singleSaleData.payment_method }}</span>
                </p>
                <p style="text-align:center">
                    Thank you for your visit!
                </p>
            </section>
            <section style="margin-top: 10px; text-align: center" v-if="Auth.invoice_qr_code === 1">
                <qrcode-vue :value="value" :size="50" level="H" render-as="svg"></qrcode-vue>
            </section>
            <section style="text-align: center">
                <sub>
                    Powered By : <span>Fuel Matix</span>
                </sub>
            </section>
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
import {Printd} from "printd";
import QrcodeVue from "qrcode.vue";
export default {
    name: "Agent",
    components: {
        QrcodeVue,
        Pagination,
    },
    data() {
        return {
            loadingFile: false,
            paginateData: {},
            Param: {
                keyword: '',
                limit: 10,
                order_by: 'id',
                order_mode: 'DESC',
                page: 1,
                type_id: '',
                ids: [],
                start_date: '',
                end_date: ''
            },
            excelLoading: false,
            Loading: false,
            TableLoading: false,
            listData: [],
            printD: null,
            singleSaleData: null,
            cssText: `
    @page {
        size: 2.8in 11in;
        margin: 0 !important; /* Set all margins to zero */
    }

    body {
        margin: 0; /* Reset body margin */
        padding: 0; /* Reset body padding */
    }

    * {
        margin: 0; /* Reset margin for all elements */
        padding: 0; /* Reset padding for all elements */
        box-sizing: border-box; /* Include padding and border in element's total width and height */
    }

    table {
        width: 100%;
    }

    tr {
        width: 100%;
    }

    h1 {
        text-align: center;
        vertical-align: middle;
    }

    #logo {
        width: 60%;
        text-align: center;
        display: block;
        margin: 0 auto;
    }

    header {
        width: 100%;
        text-align: center;
    }

    .items thead {
        text-align: center;
    }

    .center-align {
        text-align: center;
    }

    .bill-details td {
        font-size: 12px;
    }

    .receipt {
        font-size: medium;
    }

    .items .heading {
        font-size: 12.5px;
        text-transform: uppercase;
        border-top: 1px solid black;
        border-bottom: 1px solid black;
    }

    .items thead tr th:first-child,
    .items tbody tr td:first-child {
        width: 47%;
        word-break: break-all;
        text-align: left;
    }

    .items td {
        font-size: 12px;
        text-align: right;
    }

    .price::before {
        content: "৳";
        font-family: Arial;
    }

    .sum-up {
        text-align: right !important;
    }

    .total {
        font-size: 13px;
        border-top: 1px dashed black !important;
        border-bottom: 1px dashed black !important;
    }

    .total.text, .total.price {
        text-align: right;
    }

    .total.price::before {
        content: "৳";
    }

    .line {
        border-top: 1px solid black !important;
    }

    .heading.rate {
        width: 20%;
    }

    .heading.amount {
        width: 25%;
    }

    .heading.qty {
        width: 5%;
    }

    p {
        padding: 1px;
        margin: 0;
    }

    section, footer {
        font-size: 12px;
    }
`,
            value: ''
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                mode: 'range',
                onChange: (date, dateStr) => {
                    let dateArr = dateStr.split('to')
                    if (dateArr.length === 2) {
                        this.Param.start_date = dateArr[0]
                        this.Param.end_date = dateArr[1]
                    }
                    if (dateArr.length === 1) {
                        this.Param.start_date = dateArr[0];
                        this.Param.end_date = dateArr[0];
                    }
                }
            })
        }, 1000);
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
    },
    methods: {
        downloadExcel() {
            this.excelLoading = true;
            ApiService.ClearErrorHandler();
            ApiService.DOWNLOAD(ApiRoutes.SaleList + '/export/excel', this.Param,'',res => {
                this.excelLoading = false
                let blob = new Blob([res], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Sale List.xlsx';
                link.click();
            });
        },
        downloadPdf: function() {
            this.loadingFile = true
            ApiService.ClearErrorHandler();
            ApiService.DOWNLOAD(ApiRoutes.SaleList + '/export/pdf', this.Param,'',(res) => {
                this.Param.ids = [];
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Sale List.pdf';
                link.click();
            });
        },
        isExist: function (id) {
            let index = this.Param.ids.indexOf(id)
            return index > -1;
        },
        selectAll: function (e) {
            if (e.target.checked) {
                this.listData.map(v => {
                    this.Param.ids.push(v.id);
                });
            } else {
                this.Param.ids = []
            }
        },
        selectIds: function (e, id) {
            if (e.target.checked) {
                this.Param.ids.push(id)
            } else {
                this.Param.ids.splice(this.Param.ids.indexOf(id), 1)
            }
        },
        printInvoice: function (id) {
            $('.invoice'+ id).toggle();
            ApiService.POST(ApiRoutes.SaleSingle, {id: id}, res => {
                $('.invoice'+ id).toggle();
                if (parseInt(res.status) === 200) {
                    this.singleSaleData = res.data;
                    this.printD = new Printd();
                    setTimeout(() => {
                        this.loading = false
                        this.print()
                    }, 1000)
                }
            });
        },
        print () {
            const printElement = document.getElementById('print');

            // Add inline styles directly for print
            printElement.style.margin = '0';
            printElement.style.padding = '0';

            document.body.style.margin = '0';
            document.body.style.padding = '0';

            // Adjust the print logic to ensure styles are applied
            this.printD.print(printElement, [this.cssText]);
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
            ApiService.POST(ApiRoutes.SaleList, this.Param,res => {
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
            ApiService.POST(ApiRoutes.SaleDelete, {id: data.id },res => {
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
        $('#dashboard_bar').text('Sales List')
    }
}
</script>

<style scoped>

</style>
