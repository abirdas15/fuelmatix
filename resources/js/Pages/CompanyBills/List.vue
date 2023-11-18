<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Company Bills</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Company Bills</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mt-4">
                                <div class="col-12 d-flex mb-3 align-items-center">
                                    <div class="form-group position-relative me-3 w-25">
                                        <label for="year" class="form-label">Select Year<span class="text-danger">*</span></label>
                                        <select v-model="Param.year" name="year" class="form-control form-select" id="year">
                                            <option v-for="year in years(new Date().getFullYear()-5)" :value="year.id">{{ year.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="form-group me-3 w-25">
                                        <label for="month" class="form-label">Select Month<span class="text-danger">*</span></label>
                                        <select v-model="Param.month" name="month" class="form-control form-select" id="month">
                                            <option v-for="month in months()" :value="month.id">{{ month.name}}</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="form-group mt-4">
                                        <button @click="list" v-if="!TableLoading" class="btn btn-primary">Filter</button>
                                        <button v-if="TableLoading" class="btn btn-primary">Filtering...</button>
                                    </div>
                                </div>
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
                                                <th class="text-white" @click="sortData('name')" :class="sortClass('name')">Name</th>
                                                <th class="text-white" @click="sortData('amount')" :class="sortClass('amount')">Amount</th>
                                                <th class="text-white" style="width: 375px">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading == false">
                                            <tr v-for="(f, i) in listData">
                                                <td><a href="javascript:void(0);">{{f.name}}</a></td>
                                                <td><a href="javascript:void(0);">{{f?.amount}}</a></td>
                                                <td>
                                                    <template>
                                                        <button class="btn btn-sm btn-primary"  @click="tableAction('download', f, i)">
                                                            <i class="fa-solid fa-file-pdf" :class="'genloading'+i" style="display: block"></i>
                                                            <i class="fa fa-spinner fa-spin" :class="'genloading'+i" style="display: none"></i>
                                                        </button>
                                                    </template>
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
                month: '',
                year: '',
            },
            Loading: false,
            generateLoading: false,
            TableLoading: false,
            listData: [],
            selectedData: null,
            expandParam: {
                id: '',
                data: []
            },
            selectedIDs: []
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        this.list()
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
        tableAction: function (e, data, i = null) {
            if (e == 'download') {
                this.Param.company_id = data.id
                $('.genloading'+ i).toggle()
                ApiService.DOWNLOAD(ApiRoutes.CompanyBillDownload, this.Param,'',res => {
                    $('.genloading'+ i).toggle()
                    let blob = new Blob([res], {type: 'pdf'});
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'Company Bills.pdf';
                    link.click();
                });
            }
        },
        list: function (page) {
            if (page == undefined) {
                page = {
                    page: 1
                };
            }
            this.Param.page = page.page;
            if (this.Param.month == '') {
                this.Param.month = new Date().getMonth()+1
            }
            if (this.Param.year == '') {
                this.Param.year = new Date().getFullYear()
            }
            this.TableLoading = true
            ApiService.POST(ApiRoutes.CompanyBillList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.paginateData = res.data;
                    this.listData = res.data.data;
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
        $('#dashboard_bar').text('Company Bills')
    }
}
</script>

<style scoped>

</style>
