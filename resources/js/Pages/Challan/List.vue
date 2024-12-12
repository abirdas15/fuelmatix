<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Challan</a></li>
                    <li v-if="CheckPermission(Section.CHALLAN + '-' + Action.CREATE)" style="margin-left: auto;"><router-link :to="{name: 'challanAdd'}"><i class="fa-solid fa-plus"></i> Add Challan</router-link></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Challan</h4>
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
                                                <input  type="search" class="" placeholder="">
                                            </label>
                                        </div>
                                        <table class="display  dataTable no-footer" style="min-width: 845px">
                                            <thead>
                                            <tr class="text-white" style="background-color: #4886EE;color:#ffffff">
                                                <th class="text-white">Date</th>
                                                <th class="text-white">Challan No</th>
                                                <th class="text-white">Company Name</th>
                                                <th class="text-white">Company Address</th>
                                                <th class="text-white">Total</th>
                                                <th class="text-white" >Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading === false">
                                            <tr v-for="f in listData">
                                                <td >{{f.date}}</td>
                                                <td >{{f.challan_no}}</td>
                                                <td >{{f.company_name}}</td>
                                                <td >{{f.company_address}}</td>
                                                <td >{{f.total_format}}</td>
                                                <td>
                                                    <a  href="javascript:void(0)" :class="'challan' + f.id" @click="printPdf(f.id)" class="btn btn-primary shadow btn-xs sharp  me-1">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    <a style="display: none" :class="'challan' + f.id" class="btn btn-primary shadow btn-xs sharp  me-1">
                                                        <i class="fa fa-spinner fa-spin"></i>
                                                    </a>
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
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import Pagination from "../../Helpers/Pagination";
import Table from "../../admin/Pages/Common/Table.vue";
import Section from "../../Helpers/Section";
import Action from "../../Helpers/Action";
export default {
    components: {
        Table,
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
        };
    },
    watch: {

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
        printPdf: function(id) {
            $('.challan'+ id).toggle();
            ApiService.DOWNLOAD(ApiRoutes.Challan + '/export/pdf', {id: id},'',(res) => {
                $('.challan'+ id).toggle();
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'challan.pdf';
                link.click();
            });
        },
        list: function (page) {
            if (page === undefined) {
                page = {
                    page: 1
                };
            }
            this.Param.page = page.page;
            this.TableLoading = true
            ApiService.POST(ApiRoutes.Challan + '/list', this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.paginateData = res.data;
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
    },
    mounted() {
        $('#dashboard_bar').text('Challan')
    }
}
</script>

<style scoped>

</style>
