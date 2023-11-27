<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Car</a></li>
                    <li v-if="CheckPermission(Section.DRIVER + '-' + Action.CREATE)"  style="margin-left: auto;"><a href="javascript:void(0)" @click="openCarModal"><i class="fa-solid fa-plus"></i> Add Car</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Car List</h4>
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
                                                <th class="text-white" @click="sortData('driver_name')" :class="sortClass('car_name')">Car Name</th>
                                                <th class="text-white" @click="sortData('company_name')" :class="sortClass('company_name')">Company Name</th>
                                                <th class="text-white">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading == false">
                                            <tr v-for="f in listData">
                                                <td >{{f.car_name}}</td>
                                                <td >{{f.company_name}}</td>
                                                <td>
                                                    <a href="javascript:void(0)" @click="openCarEditModal(f.id)" class=" btn btn-primary shadow btn-xs sharp me-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <a href="javascript:void(0)"  @click="openModalDelete(f.id)" class="btn btn-danger shadow btn-xs sharp">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
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
        <div class="popup-wrapper-modal driverModal d-none">
            <form @submit.prevent="saveCar" class="popup-box" style="max-width: 800px">
                <button type="button" class=" btn  closeBtn"><i class="fas fa-times"></i></button>
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="company_id">Company</label>
                            <select class="form-control form-select" v-model="carParam.company_id" name="company_id">
                                <option value="">Select Company</option>
                                <option v-for="m in allCompany" :value="m.id">{{m.name}}</option>
                            </select>
                            <small class="invalid-feedback"></small>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-wrapper form-group mb-3">
                            <label for="name">Name</label>
                            <input type="text" class="w-100 form-control bg-white" name="name" id="name"
                                   v-model="carParam.car_number" placeholder="Name">
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
            carParam: {
                id: '',
                company_id: '',
                car_number: '',
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
        openCarEditModal: function(id) {
            $('.driverModal').removeClass('d-none');
            ApiService.POST(ApiRoutes.CarSingle, {id: id}, (res) => {
                this.Loading = false;
                if (parseInt(res.status) === 200) {
                    this.carParam = res.data;
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        saveCar: function() {
            this.Loading = true;
            let route = this.carParam.id == '' ? ApiRoutes.CarSave : ApiRoutes.CarUpdate;
            ApiService.POST(route, this.carParam, (res) => {
                this.Loading = false;
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    $('.driverModal').addClass('d-none');
                    this.list();
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        openCarModal: function (f) {
            this.carParam = {
                id: '',
                company_id: '',
                car_number: '',
            }
            $('.driverModal').removeClass('d-none');
        },
        getCompany: function () {
            this.categories = []
            ApiService.POST(ApiRoutes.CreditCompanyList, {page:1, limit: 5000}, res => {
                if (parseInt(res.status) === 200) {
                    this.allCompany = res.data.data;
                }
            });
        },
        openModalDelete(id) {
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
                    this.Delete(id)
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
            ApiService.POST(ApiRoutes.CarList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.paginateData = res.data;
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        Delete: function (id) {
            ApiService.POST(ApiRoutes.CarDelete, {id: id },res => {
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
        $('#dashboard_bar').text('Car')
    }
}
</script>

<style scoped>

</style>
