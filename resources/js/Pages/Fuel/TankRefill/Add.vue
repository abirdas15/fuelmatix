<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'TankRefill'}">Tank</router-link></li>
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
                                    <h4 class="card-title">Pay Order</h4>
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
                                                <label class="form-label">Select Product:</label>
                                                <select class="form-control" name="product_id" id="product_id"  v-model="param.product_id">
                                                    <option value="">Select Tank</option>
                                                    <option v-for="d in products" :value="d.id">{{d.name}}</option>
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

                                        </div>
                                    </div>
                                    <template v-for="(tank,tankIndex) in param.tanks">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Tank: {{ tank.tank_name }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="mb-3 form-group col-md-3"></div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <label class="form-label">Start Reading:</label>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <label class="form-label">End Reading:</label>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <label class="form-label">Tank Volume:</label>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3"></div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" @blur="getReading($event, 'start_reading', tankIndex, tank.id, 'mm')" :name="'tanks.' + tankIndex + '.start_reading_mm'" v-model="tank.start_reading_mm">
                                                            <span class="input-group-text" style="padding: 6px 15px">
                                                                <button class="btn btn-primary btn-sm" type="button">mm</button>
                                                            </span>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control " @blur="getReading($event, 'end_reading', tankIndex, tank.id, 'mm')" :name="'tanks.' + tankIndex + '.end_reading_mm'" v-model="tank.end_reading_mm">
                                                            <span class="input-group-text" style="padding: 6px 15px">
                                                                <button class="btn btn-primary btn-sm" type="button">mm</button>
                                                            </span>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control " disabled :name="'tanks.' + tankIndex + '.dip_sale_mm'" v-model="tank.dip_sale_mm">
                                                            <span class="input-group-text" >
                                                                mm
                                                            </span>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3"></div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" v-model="tank.start_reading">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">Liter</span>
                                                            </div>
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" disabled name="end_reading" v-model="tank.end_reading">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">Liter</span>
                                                            </div>
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                    <div class="mb-3 form-group col-md-3">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"  @blur="getReading($event, 'dip_sale_mm', tankIndex, tank.id, 'liter')" :name="'tanks.' + tankIndex + '.dip_sale'" v-model="tank.dip_sale">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text" style="padding: 6px 15px">
                                                                    <button class="btn btn-primary btn-sm" type="button">Liter</button>
                                                                </span>
                                                            </div>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card" v-if="tank.dispensers.length > 0"
                                                 v-for="(d, dIndex) in tank.dispensers">
                                                <div class="custom-bg">
                                                    <h5 class="card-title">Dispenser: {{ d.dispenser_name }}</h5>
                                                </div>
                                                <div class="card-body" v-if="d.nozzle.length > 0">
                                                    <div class="row align-items-center text-start" v-for="(n, nIndex) in d.nozzle">
                                                        <div class=" col-md-3">
                                                            <label class="form-label">
                                                                <p class="m-0">{{ n.name }}</p>
                                                            </label>
                                                        </div>
                                                        <div class="mb-3 col-md-3 form-group">
                                                            <label>Start Reading </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" :name="'tanks.' + tankIndex + '.dispensers.' + dIndex + '.nozzle.' + nIndex + '.start_reading'" @input="nozzleTotalSale(n)" :id="'prReading'+nIndex+dIndex" v-model="n.start_reading">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">Liter</span>
                                                                </div>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 col-md-3 form-group">
                                                            <label>End reading </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" @input="nozzleTotalSale(n)" :id="'trReading'+nIndex+dIndex" v-model="n.end_reading" :name="'tanks.' + tankIndex + '.dispensers.' + dIndex + '.nozzle.' + nIndex + '.end_reading'">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">Liter</span>
                                                                </div>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 col-md-3 form-group">
                                                            <label>sale on {{ n.name }} </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" disabled :id="'sorReading'+nIndex+dIndex"  v-model="n.sale" :name="'tanks.' + tankIndex + '.dispensers.' + dIndex + '.nozzle.' + nIndex + '.sale'">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">Liter</span>
                                                                </div>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="row">
                                        <div class="col-sm-8"></div>
                                        <div class="col-sm-4">
                                            <div class="text-right mb-4 form-group">
                                                <label>Total refill volume</label>
                                                <input type="text" class="form-control" name="total_refill_volume" disabled v-model="param.total_refill_volume">
                                                <div class="invalid-feedback"></div>
                                            </div>
                                            <div class="text-right" v-if="this.param.net_profit">
                                                <label>{{ this.param.net_profit > 0 ? 'Net Profit' : 'Net Loss' }}</label>
                                                <input type="text" class="form-control" disabled :value="Math.abs(param.net_profit)">
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
                                    <router-link :to="{name: 'TankRefill'}" type="button" class="btn btn-danger">Cancel</router-link>
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
                pay_order_id: '',
                quantity: '',
                amount: 0,
                net_profit: 0,
                total_refill_volume: 0,
                product_id: '',
                tanks: []
            },
            unit_price: 0,
            listDataTank: [],
            listDataPayOrder: [],
            tankDispenserData: [],
            tankReadingData: [],
            singlePayOrder: [],
            loading: false,
            bstiChart: [],
            products: []
        }
    },
    watch: {
        'param.pay_order_id': function () {
            this.getPayOrderQuantity()
        },
        'param.product_id': function () {
            this.fetchTankWithLatestReading();
            this.getPayOrder();
        },
        'param.total_refill_volume': function() {
            this.param.net_profit = this.param.total_refill_volume - this.param.quantity;
        },
        'param.quantity': function() {
            this.param.net_profit = this.param.total_refill_volume - this.param.quantity;
        }
    },
    methods: {
        getReading: function(event, field, index, tank_id, type) {
            this.getValue(event.target.value, field, index, tank_id, type);
        },
        getValue: function(value, field, index, tank_id, type) {
            this.getBstiChart(value, field, index, tank_id, type);
        },
        getBstiChart: function(value, field, index, tank_id, type) {
            let data = null;
            if (type === 'mm') {
                data = {
                    tank_id: tank_id,
                    height: value,
                };
            } else if (type === 'liter') {
                data = {
                    tank_id: tank_id,
                    volume: value,
                };
            }
            ApiService.POST(ApiRoutes.TankGetVolume, data, res => {
                if (parseInt(res.status) === 200) {
                    this.param.tanks[index][field] =  res.data;
                    if (type === 'mm') {
                        if (field === 'dip_sale') {
                            this.param.tanks[index]['end_reading_mm'] = parseFloat(this.param.tanks[index]['start_reading_mm']) + parseFloat(this.param.tanks[index]['dip_sale_mm']);
                            setTimeout(() => {
                                this.getValue(this.param.tanks[index]['end_reading_mm'], 'end_reading', index, tank_id, type);
                            }, 500);
                        }  else {
                            this.param.tanks[index]['dip_sale'] = this.param.tanks[index]['end_reading'] - this.param.tanks[index]['start_reading'];
                            this.getTotalRefillVolume();
                        }
                    } else if (type === 'liter') {
                        if (field === 'dip_sale_mm') {
                            this.param.tanks[index]['end_reading'] = parseFloat(this.param.tanks[index]['start_reading']) + parseFloat(this.param.tanks[index]['dip_sale']);
                            setTimeout(() => {
                                this.getValue(this.param.tanks[index]['end_reading'], 'end_reading_mm', index, tank_id, type);
                            }, 500);
                        } else {
                            this.param.tanks[index]['dip_sale_mm'] = this.param.tanks[index]['end_reading_mm'] - this.param.tanks[index]['start_reading_mm'];
                            this.getTotalRefillVolume();
                        }
                    }
                }
            });
        },
        getTotalRefillVolume: function () {
            let nozzleAmount = 0;
            let dipSale = 0;
            this.param.tanks.map((tank) => {
                dipSale += parseFloat(tank.dip_sale);
                tank.dispensers.map(d => {
                    d.nozzle.map(n => {
                        nozzleAmount += parseFloat(n.sale)
                    })
                })
            });
            this.param.total_refill_volume = nozzleAmount + dipSale;
        },
        nozzleTotalSale: function (nozzle) {
            nozzle.sale = parseFloat(nozzle.end_reading) - parseFloat(nozzle.start_reading)
            this.getTotalRefillVolume();
        },
        fetchProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, {limit: 5000, page: 1},res => {
                if (parseInt(res.status) === 200) {
                    this.products = res.data.data;
                }
            });
        },
        fetchTankWithLatestReading: function () {
            ApiService.POST(ApiRoutes.TankWithLatestReading, {type: 'tank refill', product_id: this.param.product_id},res => {
                if (parseInt(res.status) === 200) {
                    this.param.tanks = res.data;
                }
            });
        },
        getPayOrder: function () {
            ApiService.POST(ApiRoutes.PayOrderLatest, {product_id: this.param.product_id},res => {
                if (parseInt(res.status) === 200) {
                    this.listDataPayOrder = res.data;
                }
            });
        },

        getPayOrderQuantity: function () {
            if (this.param.product_id === '' && this.param.pay_order_id === '') {
                return;
            }
            ApiService.POST(ApiRoutes.PayOrderQuantity, {product_id: this.param.product_id, pay_order_id: this.param.pay_order_id},res => {
                if (parseInt(res.status) === 200) {
                    this.param.quantity = 0;
                    if (res.data != null) {
                        this.param.quantity = res.data.quantity;
                        this.param.amount = res.data.total
                        this.unit_price = res.data.unit_price;
                    }
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

        save: function () {
            this.loading = true
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
                } else if (parseInt(res.status) === 400) {
                    this.$toast.warning(res.message);
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.fetchProduct()
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
.input-group-text{
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border: 1px solid #c3bfbf;
    padding: 16.5px 15px;
}
.input-group-append {
    width: 25%;
}
@media only screen and (max-width: 1366px) {
    .input-group-text{
        padding: 10.5px 15px;
    }
}
</style>
