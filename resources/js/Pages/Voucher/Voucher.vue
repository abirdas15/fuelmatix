<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Voucher List</a></li>
                    <li v-if="CheckPermission(Section.VOUCHER + '-' + Action.CREATE)" style="margin-left: auto;"><a href="javascript:void(0)" @click="openVoucherModal"><i class="fa-solid fa-plus"></i> Add Voucher</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Voucher List</h4>
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
                                                <th class="text-white" @click="sortData('company_name')" :class="sortClass('company_name')">Company Name</th>
                                                <th class="text-white" @click="sortData('voucher_number')" :class="sortClass('voucher_number')">Voucher Number</th>
                                                <th class="text-white" @click="sortData('validity')" :class="sortClass('validity')">Validity</th>
                                                <th class="text-white" @click="sortData('status')" :class="sortClass('status')">Status</th>
                                                <th class="text-white" >Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading == false">
                                            <tr v-for="f in listData">
                                                <td >{{f.company_name}}</td>
                                                <td >{{f.voucher_number}}</td>
                                                <td >{{f.validity}}</td>
                                                <td >{{f.status}}</td>
                                                <td>
                                                    <div v-if="CheckPermission(Section.VOUCHER + '-' + Action.DELETE)"  class="d-flex justify-content-end">
                                                        <a  href="javascript:void(0)"  @click="openModalDelete(f)" class="btn btn-danger shadow btn-xs sharp">
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
        <div class="popup-wrapper-modal voucherModal d-none">
            <form @submit.prevent="saveVoucher" class="popup-box" style="max-width: 800px">
                <button type="button" class=" btn  closeBtn"><i class="fas fa-times"></i></button>
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="company_id">Company</label>
                            <v-select
                                class="form-control form-control-sm"
                                :options="allCompany"
                                placeholder="Choose Company"
                                label="name"
                                v-model="voucherParam.company_id"
                                :reduce="(option) => option.id"
                                :searchable="true"
                            ></v-select>
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="from_number">From Number</label>
                            <input type="number" class="w-100 form-control bg-white" name="from_number" id="from_number"
                                   v-model="voucherParam.from_number" placeholder="From Number">
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="to_number">To Number</label>
                            <input type="number" class="w-100 form-control bg-white" name="to_number" id="to_number"
                                   v-model="voucherParam.to_number" placeholder="To Number">
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="validity">Validity</label>
                            <input type="text" class="form-control date bg-white" name="date" v-model="voucherParam.validity">
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
import Pagination from "../../Helpers/Pagination.vue";
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
            },
            Loading: false,
            TableLoading: false,
            listData: [],
            allCompany: [],
            voucherParam: {
                company_id: '',
                from_number: '',
                to_number: '',
                validity: ''
            }
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        this.list();
        this.getCompany();
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
        saveVoucher: function() {
            this.Loading = true;
            ApiService.POST(ApiRoutes.VoucherSave, this.voucherParam, (res) => {
                this.Loading = false;
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    $('.voucherModal').addClass('d-none');
                    this.list();
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        openVoucherModal: function (f) {
            $('.voucherModal').removeClass('d-none');
        },
        getCompany: function () {
            this.categories = []
            ApiService.POST(ApiRoutes.CreditCompanyList, {page:1, limit: 5000}, res => {
                if (parseInt(res.status) === 200) {
                    this.allCompany = res.data.data;
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
            ApiService.POST(ApiRoutes.VoucherList, this.Param,res => {
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
            ApiService.POST(ApiRoutes.TankDelete, {id: data.id },res => {
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
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                onChange: (dateStr, date) => {
                    this.voucherParam.validity = date
                }
            })
        }, 1000)
        $('#dashboard_bar').text('Voucher')
    }
}
</script>

<style scoped>

</style>
