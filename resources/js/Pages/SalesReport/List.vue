<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Sales Report</a></li>
                </ol>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card ">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Sales Report</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">

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
                                            <select class="me-sm-2 form-control wide" id="inlineFormCustomSelect" v-model="Param.product_id">
                                                <option value="">Select Product</option>
                                                <option v-for="t of products" :value="t.id">{{t.name}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-2 mb-3">
                                        <div class="example">
                                            <p class="mb-1">Select Dispenser </p>
                                            <select class="me-sm-2 form-control wide" id="inlineFormCustomSelect" v-model="Param.dispenser_id">
                                                <option value="">Select Dispenser</option>
                                                <option v-for="t of dispensers" :value="t.id">{{t.dispenser_name}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-2 mb-3">
                                        <div class="example">
                                            <p class="mb-1">Select Nozzle </p>
                                            <select class="me-sm-2 form-control wide" id="inlineFormCustomSelect" v-model="Param.nozzle_id">
                                                <option value="">Select Type</option>
                                                <option v-for="t of nozzles" :value="t.id">{{t.name}}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 mb-3">
                                        <button type="button" class="btn btn-rounded btn-white border" @click="getSalesReport"><span
                                            class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i>
											</span>Filter</button>

                                    </div>
                                </div>
                            </div>
                            <div class=" mt-4">
                                <div class="table-responsive">
                                    <table class="table table-striped table-responsive-sm">
                                        <thead>
                                        <tr class="text-white" style="background-color: #20c997;color:#ffffff">

                                            <th class="text-white">Date </th>
                                            <th class="text-white">Product</th>
                                            <th class="text-white">Quantity</th>
                                            <th class="text-white">Amount</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="each in listData">
                                            <td v-text="each.date"></td>

                                            <td v-text="each.product_name">Octane</td>
                                            <td v-text="each.quantity"></td>
                                            <td v-text="formatPrice(each.amount)"></td>
                                        </tr>
                                        </tbody>
                                        <tfoot v-if="total > 0">
                                            <tr>
                                                <th colspan="3" class="text-end">Total</th>
                                                <th>{{ formatPrice(total) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
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
                product_id: '',
                dispenser_id: '',
                nozzle_id: '',
            },
            Loading: false,
            TableLoading: false,
            listData: [],
            products: [],
            dispensers: [],
            nozzles: []
        };
    },
    watch: {
        'Param.keyword': function () {
            this.list()
        },
        'Param.product_id': function () {
            this.fetchDispenser();
        },
        'Param.dispenser_id': function () {
            this.fetchNozzle();
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
        total: function() {
            let total = 0;
            this.listData.map((v) => {
                total += parseFloat(v.amount);
            });
            return total;
        },
    },
    methods: {
        fetchNozzle: function() {
            ApiService.POST(ApiRoutes.NozzleList, {dispenser_id: this.Param.dispenser_id}, res => {
                if (parseInt(res.status) === 200) {
                    this.nozzles = res.data.data
                }
            })
        },
        fetchDispenser: function() {
            ApiService.POST(ApiRoutes.DispenserList, {product_id: this.Param.product_id}, res => {
                if (parseInt(res.status) === 200) {
                    this.dispensers = res.data.data
                }
            })
        },
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
        $('#dashboard_bar').text('Sales Report')
    }
}
</script>

<style scoped>

</style>
