<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'TankRefill'}">Tank Refill</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Edit</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tank Refill Edit</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
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
                                                        <option value="">Select Pay order</option>
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
                                                    <label class="form-label">Tank Volume:</label>
                                                    <input type="text" disabled class="form-control " name="dip_sale" v-model="param.dip_sale">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card" v-if="param.dispensers.length > 0"
                                             v-for="(d, dIndex) in param.dispensers">
                                            <div class="card-header">
                                                <h5 class="card-title">{{ d.dispenser_name }}</h5>
                                            </div>
                                            <div class="card-body" v-if="d.nozzle.length > 0">
                                                <div class="row align-items-center text-start" v-for="(n, nIndex) in d.nozzle">
                                                    <div class=" col-md-2">
                                                        <label class="form-label">
                                                            <p class="m-0">{{ n.name }}</p>
                                                        </label>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <label>Previous Reading </label>
                                                        <input type="text" class="form-control" disabled
                                                               :id="'prReading'+nIndex+dIndex"
                                                               v-model="n.start_reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <label>End reading </label>
                                                        <input type="text" class="form-control" disabled
                                                               :id="'trReading'+nIndex+dIndex"
                                                               v-model="n.end_reading">
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <label>sale on {{ n.name }} </label>
                                                        <input type="text" class="form-control" disabled
                                                               :id="'sorReading'+nIndex+dIndex"
                                                               v-model="n.sale">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-8"></div>
                                            <div class="col-sm-4">
                                                <div class="text-right mb-4">
                                                    <label>Total refill volume</label>
                                                    <input type="text" class="form-control" disabled v-model="param.total_refill_volume">
                                                </div>
                                                <div class="text-right">
                                                    <label>Loss/Porfit</label>
                                                    <input type="text" class="form-control" disabled v-model="param.net_profit">
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
    </div>
</template>

<script>
import ApiService from "../../../Services/ApiService";
import ApiRoutes from "../../../Services/ApiRoutes";
export default {
    data() {
        return {
            param: {},
            loading: false,
            id: '',
            listDataTank: [],
            listDataPayOrder: [],
            tankDispenserData: [],
            tankReadingData: [],
            singlePayOrder: [],
        }
    },
    watch: {
        'param.pay_order_id': function () {
            this.getPayOderSingle()
            this.getDispenserSingle()
        },
        'param.tank_id': function () {
            this.getDispenserSingle()
            this.getTankReading()
        },
    },
    methods: {
        getSingle: function () {
            ApiService.POST(ApiRoutes.TankRefillSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                    this.param.dispensers = res.dispensers
                    this.getTank()
                    this.getPayOrder()
                }
            });
        },
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.TankRefillEdit, this.param,res => {
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
                    this.param.dip_sale = this.param.end_reading - this.param.start_reading
                    if (this.unit_price > 0) {
                        this.param.buy_price = (this.param.end_reading - this.param.start_reading) * this.unit_price
                    }

                }
            });
        },
        getDispenserSingle: function () {
            this.this.param.total_refill_volume = 0
            ApiService.POST(ApiRoutes.TankGetNozzle, {tank_id: this.param.tank_id},res => {
                this.param.dispensers = res;
                this.param.dispensers.forEach(v => {
                    v.nozzle.forEach(nozzle => {
                        nozzle.sale = nozzle.end_reading - nozzle.start_reading
                        this.param.total_refill_volume += nozzle.sale;
                    })
                })
                this.param.total_refill_volume += this.param.dip_sale
                this.param.net_profit = this.param.total_refill_volume - this.param.quantity
            });
        },
        getPayOrder: function () {
            ApiService.POST(ApiRoutes.PayOrderLatest, {},res => {
                if (parseInt(res.status) === 200) {
                    this.listDataPayOrder = res.data;
                }
            });
        },
        getPayOderSingle: function () {
            ApiService.POST(ApiRoutes.PayOrderSingle, {id: this.param.pay_order_id},res => {
                if (parseInt(res.status) === 200) {
                    this.singlePayOrder = res.data;
                    this.param.quantity = res.data.quantity
                    this.param.amount = res.data.amount
                    this.unit_price = this.param.amount / this.param.quantity
                }
            });
        },
    },
    created() {
        this.id = this.$route.params.id
        this.getSingle()
    },
    mounted() {
        $('#dashboard_bar').text('Tank Refill Edit')
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
