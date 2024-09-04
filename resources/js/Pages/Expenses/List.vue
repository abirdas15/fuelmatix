<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Expense List</a></li>
                    <li v-if="CheckPermission(Section.EXPENSE + '-' + Action.CREATE)" style="margin-left: auto;"><router-link :to="{name: 'ExpenseAdd'}"><i class="fa-solid fa-plus"></i> Add New Expense</router-link></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Expense List</h4>
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
                                <div class="col-xl-3 mb-3" v-if="Param.ids.length > 0">
                                    <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print</button>
                                    <button class="btn btn-primary" v-if="loadingFile"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print...</button>
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
                                                <th class="text-white" @click="sortData('name')" :class="sortClass('expense')">Expense</th>
                                                <th class="text-white" @click="sortData('dispenser_name')" :class="sortClass('amount')">Amount</th>
                                                <th class="text-white" @click="sortData('dispenser_name')" :class="sortClass('payment')">Payment Method</th>
                                                <th class="text-white" @click="sortData('approve_by')" :class="sortClass('approve_by')">Approve By</th>
                                                <th class="text-white">File</th>
                                                <th class="text-white">Status</th>
                                                <th class="text-white" >Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading === false">
                                            <tr v-for="(f,index) in listData">
                                                <td>
                                                    <input type="checkbox" :checked="isExist(f.id)" class="form-check-input" @change="selectIds($event, f.id)">
                                                </td>
                                                <td >{{f.date}}</td>
                                                <td >{{f.expense}}</td>
                                                <td>{{f.amount_format}}</td>
                                                <td>{{f.payment}}</td>
                                                <td>{{f.approve_by}}</td>
                                                <td>
                                                    <a v-if="f.file != null" target="_blank"  :href="f.file_path"><i style="font-size: 30px" class="fa-regular fa-file"></i></a>
                                                </td>
                                                <td>
                                                    <select v-if="f.status == 'pending'" class="form-select" v-model="f.status" @change="approveExpense(f.id)">
                                                        <option value="pending">Pending</option>
                                                        <option value="approve">Approved</option>
                                                    </select>
                                                    <span v-else class="text-success">Approved</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-end">
                                                        <a  href="javascript:void(0)" :class="'expense' + f.id" @click="printPdf(f.id)" class="btn btn-primary shadow btn-xs sharp  me-1">
                                                            <i class="fa fa-print"></i>
                                                        </a>
                                                        <a style="display: none" :class="'expense' + f.id" class="btn btn-primary shadow btn-xs sharp  me-1">
                                                            <i class="fa fa-spinner fa-spin"></i>
                                                        </a>
                                                        <template v-if="f.status === 'pending'">
                                                            <router-link v-if="CheckPermission(Section.EXPENSE + '-' + Action.EDIT)"  :to="{name: 'ExpenseEdit', params: { id: f.id }}" class=" btn btn-primary shadow btn-xs sharp me-1">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </router-link>
                                                            <a  v-if="CheckPermission(Section.EXPENSE + '-' + Action.DELETE)"  href="javascript:void(0)"  @click="openModalDelete(f)" class="btn btn-danger shadow btn-xs sharp">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        </template>
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
                start_date: '',
                end_date: '',
                ids: []
            },
            Loading: false,
            TableLoading: false,
            listData: [],
            loadingFile: false,
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
        downloadPdf: function() {
            this.loadingFile = true
            ApiService.ClearErrorHandler();
            ApiService.DOWNLOAD(ApiRoutes.Expense + '/list/export', this.Param,'',(res) => {
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Expense.pdf';
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
            console.log(id);
            if (e.target.checked) {
                this.Param.ids.push(id)
            } else {
                this.Param.ids.splice(this.Param.ids.indexOf(id), 1)
            }
        },
        printPdf: function(id) {
            $('.expense'+ id).toggle();
            ApiService.DOWNLOAD(ApiRoutes.Expense + '/export/pdf', {id: id},'',(res) => {
                $('.expense'+ id).toggle();
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'Expense.pdf';
                link.click();
            });
        },
        approveExpense: function(id) {
            ApiService.POST(ApiRoutes.ExpenseApprove, {id: id },res => {
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    this.list()
                } else if (parseInt(res.status) === 300) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: res.message
                    });
                }  else {
                    ApiService.ErrorHandler(res.error);
                }
            });
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
            ApiService.POST(ApiRoutes.ExpenseList, this.Param,res => {
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
            ApiService.POST(ApiRoutes.ExpenseDelete, {id: data.id, status: data.status },res => {
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
        $('#dashboard_bar').text('Expense List')
    }
}
</script>

<style scoped>

</style>
