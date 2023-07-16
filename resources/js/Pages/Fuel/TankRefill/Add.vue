<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Tank'}">Tank</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Refill</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tank Refill Entry</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="save">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">DIP</h4>
                                </div>
                                <div class="card-body">
                                    <div class="basic-form">
                                        <div class="row align-items-center">
                                            <div class="mb-3 form-group col-md-3">
                                                <label class="form-label">Date:</label>
                                                <input type="text" class="form-control date bg-white" name="date">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="mb-3 form-group col-md-3">
                                                <label class="form-label">Select Tank:</label>
                                                <select class="form-control" name="tank_id" id="tank_id"  v-model="param.tank_id">
                                                    <option value="">Select Tank</option>
                                                    <option v-for="d in listDataTank" :value="d.id">{{d.tank_name}}</option>
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="mb-3 form-group col-md-3">
                                                <label class="form-label">Select Pay order:</label>
                                                <select class="form-control" name="pay_order_id" id="pay_order_id"  v-model="param.pay_order_id">
                                                    <option value="">Select Tank</option>
                                                    <option v-for="d in listDataPayOrder" :value="d.id">{{d.number}}</option>
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="mb-3 form-group col-md-3">
                                                <label class="form-label">Paid for litter:</label>
                                                <input type="text" disabled class="form-control " name="quantity" v-model="param.quantity">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="mb-3 form-group col-md-3">
                                                <div class="fs-3 m-0">DIP</div>
                                            </div>
                                            <div class="mb-3 form-group col-md-3">
                                                <label class="form-label">Reading before refill:</label>
                                                <input type="text" disabled class="form-control " name="start_reading" v-model="param.start_reading">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="mb-3 form-group col-md-3">
                                                <label class="form-label">Reading after refill:</label>
                                                <input type="text" disabled class="form-control " name="end_reading" v-model="param.end_reading">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="mb-3 form-group col-md-3">
                                                <label class="form-label">DLP Sale:</label>
                                                <input type="text" disabled class="form-control " name="end_reading" v-model="param.net_profit">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="text-align: right;">
                                <div class="mb-3 col-md-6">

                                </div>
                                <div class="mb-3 col-md-6">
                                    <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                    <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                    <router-link :to="{name: 'TankRefill'}" type="button" class="btn btn-primary">Cancel</router-link>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";
export default {
    data() {
        return {
            param: {
                date: '',
                tank_id: '',
                pay_order_id: '',
                quantity: '',
                start_reading: '',
                end_reading: '',
                buy_price: '',
                net_profit: '',
            },
            listDataTank: [],
            listDataPayOrder: [],
            tankDispenserData: [],
            tankReadingData: [],
            singlePayOrder: [],
            loading: false,
        }
    },
    watch: {
        'param.pay_order_id': function () {
            this.getPayOderSingle()
        },
        'param.tank_id': function () {
            this.getDispenserSingle()
            this.getTankReading()
        }
    },
    methods: {
        getTank: function () {
            ApiService.POST(ApiRoutes.TankList, {limit: 5000, page: 1},res => {
                if (parseInt(res.status) === 200) {
                    this.listDataTank = res.data.data;
                }
            });
        },
        getTankReading: function () {
            ApiService.POST(ApiRoutes.TankReadingLatest, {type: 'tank refill', tank_id: this.param.tank_id},res => {
                if (parseInt(res.status) === 200) {
                    this.tankDispenserData = res.data;
                    this.param.start_reading = res.data.start_reading
                    this.param.end_reading = res.data.end_reading
                }
            });
        },
        getDispenserSingle: function () {
            ApiService.POST(ApiRoutes.TankGetNozzle, {tank_id: this.param.tank_id},res => {
                if (parseInt(res.status) === 200) {
                    this.tankReadingData = res.data;
                }
            });
        },
        getPayOrder: function () {
            ApiService.POST(ApiRoutes.PayOrderLatest, {},res => {
                if (parseInt(res.status) === 200) {
                    this.listDataPayOrder = res.data;
                    this.param.quantity = res.data.quantity
                    this.param.buy_price = res.data.amount
                    this.param.net_profit = (this.param.start_reading - this.param.end_reading) * (this.param.buy_price / this.param.quantity )
                }
            });
        },
        getPayOderSingle: function () {
            ApiService.POST(ApiRoutes.PayOrderSingle, {id: this.param.pay_order_id},res => {
                if (parseInt(res.status) === 200) {
                    this.singlePayOrder = res.data;
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            if (this.param.date == '') {
                this.param.date = moment().format('YYYY-MM-DD')
            }
            ApiService.POST(ApiRoutes.TankRefillAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'TankRefill'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.getTank()
        this.getPayOrder()
    },
    mounted() {
        $('#dashboard_bar').text('Tank Refill')
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                onChange: (date, dateStr) => {
                    this.param.date = dateStr
                }
            })
        }, 1000)
    }
}
</script>

<style scoped>

</style>
