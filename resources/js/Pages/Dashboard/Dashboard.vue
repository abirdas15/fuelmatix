<template>
    <div class="content-body">
        <!-- row -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12 mb-5">
                    <div class="text-center mt-1 mb-1">Sale</div>
                    <div style="height: 300px">
                        <canvas id="saleChart"></canvas>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="text-center mt-1 mb-1">Payable</div>
                    <div style="height: 300px">
                        <canvas id="payChart"></canvas>
                    </div>

                </div>
                <div class="col-sm-6">
                    <div class="text-center mt-1 mb-1">Invoice</div>
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
            dashboard: null
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
    },
    created() {
        this.getChart()
    }
}
</script>

<style scoped>

</style>
