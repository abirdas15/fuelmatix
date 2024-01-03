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
                                                <th class="text-white" @click="sortData('payment_method')" :class="sortClass('payment_method')">Payment Method</th>
                                                <th class="text-white" @click="sortData('total_amount')" :class="sortClass('total_amount')">Total </th>
                                                <th class="text-white" @click="sortData('user_name')" :class="sortClass('user_name')" >User</th>
                                                <th class="text-white text-end" >Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading == false">
                                            <tr v-for="f in listData">
                                                <td >{{f.date}}</td>
                                                <td><a href="javascript:void(0);">{{f.invoice_number}}</a></td>
                                                <td><a href="javascript:void(0);">{{f?.payment_method}}</a></td>
                                                <td><a href="javascript:void(0);">{{f.total_amount}}</a></td>
                                                <td><a href="javascript:void(0);">{{f?.user_name}}</a></td>
                                                <td>
                                                    <div class="d-flex justify-content-end">
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
    name: "Agent",
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
                type_id: '',
            },
            Loading: false,
            TableLoading: false,
            listData: [],
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
        $('#dashboard_bar').text('Product List')
    }
}
</script>

<style scoped>

</style>
