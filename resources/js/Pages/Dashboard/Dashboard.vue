<template>
    <div class="content-body">
        <!-- row -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12 mb-5">
                    <div class="text-center mt-1 mb-1 fs-3">Sale</div>
                    <div style="height: 300px">
                        <canvas id="saleChart"></canvas>
                    </div>
                </div>
                <div class="col-sm-12 mb-5">
                    <div class="text-center mt-1 mb-4 fs-3 ">Tanks</div>
                    <div class="row mt-4">
                        <div class="col-sm-4 mb-5" v-for="(f, i) in listData">
                            <div class="taank">
                                <div class="tank-height">
                                    <div class="height">{{ f.height != null ? f.height : 'N/A' }} mm</div>
                                </div>
                                <div class="water-tank">
                                    <div class="range r-1 position-0"></div>
                                    <div class="range r-3 position-1"></div>
                                    <div class="range r-2 position-2"></div>
                                    <div class="range r-3 position-3"></div>
                                    <div class="range r-1 position-4"></div>
                                    <div class="range r-3 position-5"></div>
                                    <div class="range r-2 position-6"></div>
                                    <div class="range r-3 position-7"></div>
                                    <div class="range r-1 position-8"></div>
                                    <div class="range r-3 position-9"></div>
                                    <div class="range r-2 position-10"></div>
                                    <div class="range r-3 position-11"></div>
                                    <div class="range r-1 position-12"></div>
                                    <div class="range r-3 position-13"></div>
                                    <div class="range r-2 position-14"></div>
                                    <div class="range r-3 position-15"></div>
                                    <div class="range r-1 position-16"></div>
                                    <div class="range r-3 position-17"></div>
                                    <div class="range r-2 position-18"></div>
                                    <div class="range r-3 position-19"></div>
                                    <div class="range r-1 position-20"></div>
                                    <div class="range r-3 position-21"></div>
                                    <div class="range r-2 position-22"></div>
                                    <div class="range r-3 position-23"></div>
                                    <div class="range r-1 position-24"></div>
                                    <div class="tank-capacity">
                                        <div class="capacity">{{f.capacity != null ? f.capacity : 'N/A'}} Liter</div>
                                    </div>
                                    <div class="fuel-height">
                                        <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave"><defs></defs><path :id="'fuel'+i" d=""/></svg>
                                        <svg style="position: absolute; left: 0" width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave"><defs></defs><path :id="'water'+i"d=""/></svg>
                                    </div>
                                    <div class="fuel-vol" :style="{top: calculateTop(f)}">
                                        <div class="vol fw-bold">{{f.last_reading?.volume != null ? f.last_reading?.volume : 'N/A'}} Liter</div>
                                    </div>
                                    <div class="fuel-vol-right" :style="{top: calculateTop(f)}">
                                        <div class="vol fw-bold">{{f.last_reading?.height != null ? f.last_reading?.height : 'N/A'}} mm</div>
                                    </div>
                                </div>

                                <div class="text-center mt-1 fw-bold">
                                    {{f.tank_name}}
                                </div>
                                <div class="text-center">
                                    ({{f.product_name}})
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-sm-12 mb-5">
                    <div class="text-center mt-1 mb-1 fs-3">AC Payable</div>
                    <div style="height: 300px">
                        <canvas id="payChart"></canvas>
                    </div>
                </div>
                <div class="col-sm-12 mb-5">
                    <div class="text-center mt-1 mb-1 fs-3">AC Receivable</div>
                    <div style="height: 300px">
                        <canvas id="invoiceChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    name: "Dashboard",
    data: function () {
        return {
            dashboard: null,
            Param: {
                keyword: '',
                limit: 500,
                order_by: 'id',
                order_mode: 'DESC',
                page: 1,
            },
            listData: []
        }
    },
    mounted() {
        $('#dashboard_bar').text('Daily Sale')
    },
    methods: {
        calculateTop: function (tank) {
            return 200 - (parseInt(tank.fuel_percent) * 2) +27 +'px'
        },
        saleChart: function () {
            if(jQuery('#saleChart').length > 0 ){
                const barChart_1 = document.getElementById("saleChart").getContext('2d');


                new Chart(barChart_1, {
                    type: 'line',
                    data: {
                        labels: this.dashboard.sale.month,
                        datasets: [
                            {
                                label: "Amount",
                                data: this.dashboard.sale.amount,
                                borderColor: 'rgba(136,108,192, 1)',
                                fill: false,
                                tension: 0
                            },
                            {
                                label: "Quantity",
                                data: this.dashboard.sale.quantity,
                                borderColor: 'rgb(13,125,253)',
                                fill: false,
                                tension: 0
                            }
                        ]
                    },
                    options: {
                        legend: false,
                        maintainAspectRatio: false,
                        responsive: true,
                    }
                });
            }
        },
        invoiceChart: function () {
            if(jQuery('#invoiceChart').length > 0 ){
                const barChart_1 = document.getElementById("invoiceChart").getContext('2d');


                new Chart(barChart_1, {
                    type: 'bar',
                    data: {
                        labels: this.dashboard.invoice.label,
                        datasets: [
                            {
                                data: this.dashboard.invoice.data,
                                borderColor: 'rgb(153, 102, 255)',
                                backgroundColor: 'rgba(153, 102, 255, 5)',
                            },
                        ]
                    },
                    options: {
                        legend: false,
                        maintainAspectRatio: false,
                        responsive: true,
                        scales: {
                            xAxes: [{
                                barThickness: 50,  // number (pixels) or 'flex'
                                maxBarThickness: 50 // number (pixels)
                            }]
                        }
                    }
                });
            }
        },
        payableChart: function () {
            if(jQuery('#payChart').length > 0 ){
                const barChart_1 = document.getElementById("payChart").getContext('2d');

                new Chart(barChart_1, {
                    type: 'bar',
                    data: {
                        labels: this.dashboard.payable.label,
                        datasets: [
                            {
                                data: this.dashboard.payable.data,
                                borderColor: 'rgb(56,143,143)',
                                backgroundColor: 'rgb(75, 192, 192)',
                            },
                        ]
                    },
                    options: {
                        legend: false,
                        maintainAspectRatio: false,
                        responsive: true,
                        scales: {
                            xAxes: [{
                                barThickness: 50,  // number (pixels) or 'flex'
                                maxBarThickness: 50 // number (pixels)
                            }]
                        }
                    }
                });
            }
        },
        getChart: function () {
            ApiService.POST(ApiRoutes.getDashboard, { },res => {
                if (parseInt(res.status) === 200) {
                    this.dashboard = res.data
                    this.saleChart()
                    this.payableChart()
                    this.invoiceChart()
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        tankList: function () {
            this.TableLoading = true
            ApiService.POST(ApiRoutes.TankList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                    this.listData.map((tank, index) => {
                        let productColor = this.getProductColor(tank)
                        setTimeout(() => {
                            $('#fuel'+index).wavify({
                                height: tank.fuel_percent == 0 ? 200 : 200 - (parseInt(tank.fuel_percent) * 2),
                                bones: 8,
                                amplitude: 10,
                                color: productColor,
                                speed: .25
                            }, 500);
                            $('#water'+index).wavify({
                                height: tank.water_percent == 0 ? 200 : 200 - (parseInt(tank.water_percent) * 2),
                                bones: 8,
                                amplitude: 10,
                                color: '#00B3FF',
                                speed: .15
                            }, 500);
                        })
                    })
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
        getProductColor: function (tank) {
            if (tank.product_type_name == 'Octane') {
                return '#D85957'
            } else if (tank.product_type_name == 'Diesel') {
                return '#51180E'
            } else if (tank.product_type_name == 'Petrol') {
                return '#E2E2E2'
            } else if (tank.product_type_name == 'LPG') {
                return '#DA251D'
            } else if (tank.product_type_name == 'CNG') {
                return '#858585'
            }
        }
    },
    created() {
        this.getChart()
        this.tankList()
    }
}
</script>

<style lang="scss" scoped>
.taank{
    position: relative;
    width: 350px;
    margin: auto;
    .tank-height{
        position: absolute;
        left: -49px;
        text-align: right;
        top: 0;
        width: 130px;
        .height{
            color: #369D6F;
        }
    }

    .water-tank{
        margin: auto;
        height: 250px;
        width: 160px;
        border-radius : 0;
        border-width: 3px;
        border-top: 0;
        border-color: #a6a6a6;
        border-style: solid;
        position: relative;
        overflow: visible;
        .range{
            position: absolute;
            right: 0;
            background-color: #a6a6a6;
            height: 3px;
            z-index: 2;
            &.r-1{
                width: 30px;
            }
            &.r-2{
                width: 20px;
            }
            &.r-3{
                width: 10px;
            }
            &.r-4{
                width: 5px;
            }
        }
        @for $i from 0 through 30 {
            .position-#{$i} {
                top: $i*10px
            }
        }
        .tank-capacity{
            position: absolute;
            right: -190px;
            text-align: left;
            top: 0rem;
            width: 180px;
            .capacity{
                color: red;
            }
            .tank-attr{
                color: red;
                font-weight: bold;
                position: absolute;
                right: -7rem;
                top: 0.8rem;
            }
        }
        .fuel-vol{
            position: absolute;
            right: -145px;
            text-align: left;
            width: 137px;
            .vol{
                color: #424242;
            }
        }
        .fuel-vol-right{
            position: absolute;
            left: -136px;
            text-align: right;
            width: 130px;
            .vol{
                color: #1a77e1;
            }
        }
        .fuel-height{
            height: 200px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
        }
    }
    .tank-bar{
        height: 250px;
        width: 2px;
        background-color: #a6a6a6;
    }
}
</style>
