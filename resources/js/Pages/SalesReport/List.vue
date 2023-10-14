<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Sales Report</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Fuel Adjustment List</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Select Date Range </p>
                                        <input type="text" class="date form-control bg-white">
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-3">
                                    <div class="example">
                                        <p class="mb-1">Select Product </p>
                                        <select class="me-sm-2 form-control wide" id="inlineFormCustomSelect" v-model="Param.type_id">
                                            <option value="">Select Type</option>
                                            <option v-for="t of products" :value="t.id">{{t.name}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-3 mb-3">
                                    <button type="button" class="btn btn-rounded btn-white border" @click="getSalesReport"><span
                                        class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i>
											</span>Filter</button>

                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="table-responsive">
                                    <div class="dataTables_wrapper no-footer">
                                        <div class="dataTables_length">
                                            <label class="d-flex align-items-center">Show
                                                <select class="mx-2"  v-model="Param.limit" @change="getSalesReport">
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
                                                <th class="text-white" @click="sortData('name')" :class="sortClass('name')">Date</th>
                                                <th class="text-white" @click="sortData('product_name')" :class="sortClass('product_name')">Product Name</th>
                                                <th class="text-white" @click="sortData('quantity')" :class="sortClass('quantity')">Quantity</th>
                                            </tr>
                                            </thead>
                                            <tbody v-if="listData.length > 0 && TableLoading == false">
                                            <tr v-for="f in listData">
                                                <td >{{f.date}}</td>
                                                <td >{{f.product_name}}</td>
                                                <td >{{f.quantity}}</td>
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
                product_id: ''
            },
            Loading: false,
            TableLoading: false,
            listData: [],
            products: [],
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
    },
    created() {
        this.getProduct();
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
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, this.listParam, res => {
                if (parseInt(res.status) === 200) {
                    this.products = res.data.data
                }
            })
        },
        getSalesReport: function () {
            this.TableLoading = true
            ApiService.POST(ApiRoutes.SalesReport, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data;
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
        },

    },
    mounted() {
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                mode: 'range',
                onChange: (date, dateStr) => {
                    let dateArr = dateStr.split('to')
                    if (dateArr.length == 2) {
                        this.Param.start_date = dateArr[0]
                        this.Param.end_date = dateArr[1]
                    }
                }
            })
        }, 1000)
        $('#dashboard_bar').text('Bank List')
    }
}
</script>

<style scoped>

</style>
