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
                                        <button type="button" class="btn btn-rounded btn-white border" @click="getSalesReport"><span
                                            class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i>
											</span>Filter</button>

                                    </div>
                                    <div class="col-xl-3 mb-3">
                                        <button class="btn btn-primary" v-if="!loadingFile" @click="downloadPdf"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print</button>
                                        <button class="btn btn-primary" v-if="loadingFile"><i class="fa fa-print" aria-hidden="true"></i>&nbsp;Print...</button>
                                    </div>
                                </div>
                            </div>
                            <div class=" mt-4">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Tank</th>
                                            <th>Opening Balance (Tank)</th>
                                            <th>Stock In</th>
                                            <th>Nozzle</th>
                                            <th>Opening Meter</th>
                                            <th>Closing Meter</th>
                                            <th>Sale</th>
                                            <th>Total Sale</th>
                                            <th>Rate</th>
                                            <th>Amount</th>
                                            <th>Total Amount</th>
                                            <th>Closing Balance (Tank)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Loop through each sale by date -->
                                        <template v-for="(sale, dateIndex) in sales">
                                            <!-- Apply the striping based on dateIndex -->
                                            <template v-for="(tank, tankIndex) in sale.tanks">
                                                <template v-for="(dispenser, dispenserIndex) in tank.dispensers">
                                                    <template v-for="(nozzle, nozzleIndex) in dispenser.nozzle">
                                                        <tr :class="{ 'table-striped-row': dateIndex % 2 === 0 }">
                                                            <!-- Only show date for the first tank and dispenser of the sale -->
                                                            <td v-if="tankIndex === 0 && dispenserIndex === 0 && nozzleIndex === 0" :rowspan="calculateRowSpan(sale.tanks)">{{ sale.date }}</td>
                                                            <td v-if="dispenserIndex === 0 && nozzleIndex === 0" :rowspan="calculateDispenserRowSpan(tank.dispensers)">{{ tank.tank_name }}</td>
                                                            <td v-if="nozzleIndex === 0">{{ tank.start_reading_format }}</td>
                                                            <td v-if="nozzleIndex === 0">{{ tank.refill_format }}</td>
                                                            <td>{{ nozzle.name }}</td>
                                                            <td>{{ nozzle.start_reading_format }}</td>
                                                            <td>{{ nozzle.end_reading_format }}</td>
                                                            <td>{{ nozzle.sale_format }}</td>
                                                            <td v-if="dispenserIndex === 0 && nozzleIndex === 0" :rowspan="calculateDispenserRowSpan(tank.dispensers)">{{ tank.total_sale_format }}</td>
                                                            <td v-if="dispenserIndex === 0 && nozzleIndex === 0" :rowspan="calculateDispenserRowSpan(tank.dispensers)">{{ tank.selling_price_format }}</td>
                                                            <td>{{ nozzle.amount_format }}</td>
                                                            <td v-if="dispenserIndex === 0 && nozzleIndex === 0" :rowspan="calculateDispenserRowSpan(tank.dispensers)">{{ tank.total_amount_format }}</td>
                                                            <td v-if="dispenserIndex === 0 && nozzleIndex === 0" :rowspan="calculateDispenserRowSpan(tank.dispensers)">{{ tank.end_reading_format }}</td>
                                                        </tr>
                                                    </template>
                                                </template>
                                            </template>
                                        </template>
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
</template>

<script>
import Swal from 'sweetalert2/dist/sweetalert2.js'
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import Pagination from "../../Helpers/Pagination";
import Section from "../../Helpers/Section";
import Action from "../../Helpers/Action";
import Table from "../../admin/Pages/Common/Table.vue";
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
                start_date: '',
                end_date: '',
                product_id: '',
                dispenser_id: '',
                nozzle_id: '',
            },
            Loading: false,
            TableLoading: false,
            sales: [],
            products: [],
            dispensers: [],
            nozzles: [],
            loadingFile: false,
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
            this.sales.map((v) => {
                total += parseFloat(v.amount);
            });
            return total;
        },
    },
    methods: {
        downloadPdf: function() {
            this.loadingFile = true
            ApiService.ClearErrorHandler();
            ApiService.DOWNLOAD(ApiRoutes.SalesReport + '/export/pdf', this.Param,'',(res) => {
                this.loadingFile = false
                let blob = new Blob([res], {type: 'pdf'});
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'SalesReport.pdf';
                link.click();
            });
        },
        calculateDispenserRowSpan(dispensers) {
            let total = 0;
            dispensers.forEach((tank) => {
                total += tank.nozzle.length;
            });
            return total;
        },
        calculateRowSpan(tanks) {
            let total = 0;
            tanks.forEach((tank) => {
                total += tank.dispensers.length;
            });
            return total;
        },
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
                    this.sales = res.data;
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

<style lang="scss">
.table-striped-row {
    background-color: #f3f5ef;
}
</style>
