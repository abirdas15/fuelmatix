<template>
    <div class="content-body">
        <div class="container-fluid" style="background-color: #6666ff">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Dummy Sale</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Dummy Sale</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
<!--                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Product type </p>
                                        <select class="me-sm-2 form-control wide" id="inlineFormCustomSelect" v-model="Param.type_id">
                                            <option value="">Select Type</option>
                                            <option v-for="t of productType" :value="t.id">{{t.name}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <button type="button" class="btn btn-rounded btn-white border" @click="list"><span
                                        class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i>
											</span>Filter</button>

                                </div>-->
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
                                                        <router-link  :to="{name: 'DummySaleView', params: { id: f.id }}" class=" btn btn-primary shadow btn-xs sharp me-1">
                                                            <i class="fas fa-eye"></i>
                                                        </router-link>
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
        <div id="print" v-if="singleSaleData">
            <header class="text-center">
                <strong>{{ singleSaleData.company?.name }}</strong>
                <br>
                <small>
                    {{ singleSaleData.company?.address }}
                </small>
                <br>
                <small>
                    Phone: {{ singleSaleData.company?.phone_number }}
                </small>
            </header>
            <p>Invoice Number : {{ singleSaleData.invoice_number }}</p>
            <table class="bill-details">
                <tbody>
                <tr>
                    <td>Date : <span>{{ singleSaleData.date }}</span></td>
                </tr>
                <tr>
                    <td><strong>Vehicle No: {{ singleSaleData.customer_name }}</strong></td>
                </tr>
                </tbody>
            </table>

            <table class="items">
                <thead>
                <tr>
                    <th class="heading name">Item</th>
                    <th class="heading qty">Qty</th>
                    <th class="heading rate">Price</th>
                    <th class="heading amount">Subtotal</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="p in singleSaleData.products">
                    <td>{{ p?.product_name }}</td>
                    <td>{{ p.quantity }}</td>
                    <td class="price">{{ p.price }}</td>
                    <td class="price">{{ p.subtotal }}</td>
                </tr>
                <tr>
                    <th colspan="3" class="total text">Total</th>
                    <th class="total price">{{singleSaleData.total_amount}}</th>
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
<!--            <section style="margin-top: 10px; text-align: center">-->
<!--                <qrcode-vue :value="value" :size="100" level="H" render-as="svg"></qrcode-vue>-->
<!--            </section>-->
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
            paginateData: {},
            Param: {
                keyword: '',
                limit: 10,
                order_by: 'id',
                order_mode: 'DESC',
                page: 1,
                type_id: '',
            },
            Loading: false,
            TableLoading: false,
            listData: [],
            printD: null,
            singleSaleData: null,
            cssText: `
                 @page {
                    size: 2.8in 11in;
                    margin-top: 0cm;
                    margin-left: 0cm;
                    margin-right: 0cm;
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
                    -webkit-align-content: center;
                    align-content: center;
                    padding: 5px;
                    margin: 2px;
                    display: block;
                    margin: 0 auto;
                }

                header {
                    width: 100%;
                    text-align: center;
                    -webkit-align-content: center;
                    align-content: center;
                    vertical-align: middle;
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
                    border-top:1px solid black;
                    margin-bottom: 4px;
                    border-bottom: 1px solid black;
                    vertical-align: middle;
                }

                .items thead tr th:first-child,
                .items tbody tr td:first-child {
                    width: 47%;
                    min-width: 47%;
                    max-width: 47%;
                    word-break: break-all;
                    text-align: left;
                }

                .items td {
                    font-size: 12px;
                    text-align: right;
                    vertical-align: bottom;
                }

                .price::before {
                    content: "৳";
                    font-family: Arial;
                    text-align: right;
                }

                .sum-up {
                    text-align: right !important;
                }
                .total {
                    font-size: 13px;
                    border-top:1px dashed black !important;
                    border-bottom:1px dashed black !important;
                }
                .total.text, .total.price {
                    text-align: right;
                }
                .total.price::before {
                    content: "৳";
                }
                .line {
                    border-top:1px solid black !important;
                }
                .heading.rate {
                    width: 20%;
                }
                .heading.amount {
                    width: 25%;
                }
                .heading.qty {
                    width: 5%
                }
                p {
                    padding: 1px;
                    margin: 0;
                }
                section, footer {
                    font-size: 12px;
                }
            `,
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
    },
    methods: {
        printInvoice: function (id) {
            $('.invoice'+ id).toggle();
            ApiService.POST(ApiRoutes.DummySaleSingle, {id: id}, res => {
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
            this.printD.print(document.getElementById('print'), [this.cssText])
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
            if (page === undefined) {
                page = {
                    page: 1
                };
            }
            this.Param.page = page.page;
            this.TableLoading = true
            ApiService.POST(ApiRoutes.DummySaleList, this.Param,res => {
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
        $('#dashboard_bar').text('Dummy Sale')
    }
}
</script>

<style scoped>

</style>
