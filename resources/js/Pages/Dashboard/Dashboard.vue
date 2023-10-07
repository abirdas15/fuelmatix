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
                    <div class="text-center mt-1 mb-3 fs-3 ">Tank</div>
                    <div class="row mt-4">
                        <div class="col-sm-4 mb-5" v-for="f in listData">
                            <div class="taank">

                                <div class="tank-height">
                                    <div class="height">{{ f.height != null ? f.height : 'N/A' }}</div>
                                    <div class="height-line"></div>
                                </div>
                                <div id="waterLevelDiags" class="water-tank">
                                    <div class="tank-capacity">
                                        <div class="capacity">{{f.capacity != null ? f.capacity : 'N/A'}}</div>
                                        <div class="capacity-line"></div>
                                    </div>
                                    <div class="fuel-height">
                                        <svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave"><defs></defs><path id="feel-the-wave" d=""/></svg>
                                        <svg style="position: absolute; left: 0" width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave"><defs></defs><path id="feel-the-wave2" d=""/></svg>
<!--                                        <div class="fuel-capacity" :style="{bottom: f.water_percent+'%', height: f.fuel_percent+'%'}">-->
<!--                                            <div class="fuel-attr" v-if="f.fuel_percent > 0">{{f.fuel_percent}}%</div>-->
<!--                                            <div class="fuel-line" v-if="f.fuel_percent > 0"></div>-->
<!--                                        </div>-->
<!--                                        <div class="water-capacity" :style="{bottom: 0, height: f.water_percent+'%'}">-->
<!--                                            <div class="water-attr" v-if="f.water_percent > 0">{{f.water_percent}}%</div>-->
<!--                                            <div class="water-line" v-if="f.water_percent > 0"></div>-->
<!--                                        </div>-->
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
        $('#dashboard_bar').text('Dashboard')
    },
    methods: {
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
                    setTimeout(() => {
                        $('#feel-the-wave').wavify({
                            height: 80,
                            bones: 8,
                            amplitude: 10,
                            color: '#bf9201',
                            speed: .25
                        }, 500);
                        $('#feel-the-wave2').wavify({
                            height: 160,
                            bones: 8,
                            amplitude: 10,
                            color: '#00B3FF',
                            speed: .15
                        }, 500);
                    })
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
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
    .tank-height{
        position: absolute;
        right: 40%;
        text-align: center;
        top: -24px;
        .height-line{
            height: 3px;
            width: 94px;
            background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #369D6F, #369D6F, #369D6F, #369D6F, #369D6F);
            background-size: 12px 3px, 100% 3px;
            border: none;
        }
        .height{
            color: #369D6F;
        }
        .tank-attr{
            color: #369D6F;
            font-weight: bold;
            position: absolute;
            left: -6rem;
            top: 0.8rem;
        }
    }
    .water-tank{
        margin: auto;
        height: 250px;
        width: 300px;
        border-radius : 190px 190px 133px 147px;
        border-width: 3px;
        border-color: #a6a6a6;
        border-style: solid;
        position: relative;
        overflow: hidden;
        .tank-capacity{
            position: absolute;
            left: 41px;
            text-align: center;
            top: 1rem;
            .capacity-line{
                height: 3px;
                width: 209px;
                background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, red, red, red, red, red);
                background-size: 12px 3px, 100% 3px;
                border: none;
            }
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
        .fuel-height{
            height: 200px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            .fuel-capacity{
                width: 100%;
                background-color: #FFC301;
                position: absolute;
                left: 0;
                right: 0;
                text-align: center;
                //animation: wave 5s infinite;
                transition: 800ms;
                .fuel-line{
                    height: 2px;
                    width: 300px;
                    background-size: 14px 3px, 100% 3px;
                    border: none;
                    position: absolute;
                    top: -2px;
                }
                .fuel-attr{
                    color: #FFC301;
                    font-weight: bold;
                    position: absolute;
                    top: -19px;
                    right: 2.5rem;
                }
            }
            .water-capacity{
                width: 100%;
                background-color: #1fafed8c;
                position: absolute;
                left: 0;
                right: 0;
                border-bottom-left-radius: 8px;
                border-bottom-right-radius: 8px;
                //animation: wave 5s infinite;
                transition: 800ms;
                .water-line{
                    height: 2px;
                    width: 300px;
                    background-size: 14px 3px, 100% 3px;
                    border: none;
                    position: absolute;
                    top: -2px;
                }
                .water-attr{
                    color: #00B3FF;
                    font-weight: bold;
                    position: absolute;
                    top: -20px;
                    left: 3.5rem;
                }
            }
        }
    }
}
.tt{
    width: 70px;
    &.text-height{
        color: #369D6F;
    }
    &.text-capacity{
        color: red;
    }
    &.text-fuel{
        color: #bf9201;
    }
    &.text-water{
        color: #00B3FF;
    }
}

.line{
    height: 2px;
    width: 80px;
    background-size: 14px 3px, 100% 3px;
    border: none;
    margin-left: 20px;
    &.height{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #369D6F, #369D6F, #369D6F, #369D6F, #369D6F);
    }
    &.capacity{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, red, red, red, red, red);
    }
    &.fuel{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #bf9201, #bf9201, #bf9201, #bf9201, #bf9201);
    }
    &.water{
        background-image: linear-gradient(90deg, transparent, transparent 50%, #fff 50%, #fff 100%), linear-gradient(90deg, #00B3FF, #00B3FF, #00B3FF, #00B3FF, #00B3FF);
    }
}
</style>
