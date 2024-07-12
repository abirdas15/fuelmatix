<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active">
                        <router-link :to="{name: 'Dashboard'}">Home</router-link>
                    </li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Shift Sale</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Shift Sale Start</h4>
                        </div>
                        <form @submit.prevent="submit">
                            <div class="card-body">
                                <div class="process-wrapper">
                                    <div id="progress-bar-container" v-if="listData.length > 0">
                                        <ul>
                                            <li class="step step01" :class="{'active': p.id == product_id}"
                                                v-for="(p, pIndex) in listData" @click="product_id = p.id; productIndex = pIndex; getProductDispenser()">
                                                <div class="step-inner">{{ p.name }}</div>
                                            </li>
                                        </ul>

                                        <div id="line">
                                            <div id="line-progress" :style="{'width': calculateLineProgress() + '%'}"></div>
                                        </div>
                                    </div>
                                    <div class="text-center" v-else>No Product Found</div>

                                    <div id="progress-content-section" v-if="listDispenser">
                                        <div class="row">
                                            <div class="col-sm-12 text-end">
                                                <div class="form-check d-flex justify-content-end">
                                                    <input class="form-check-input" type="checkbox" v-model="noDIPShow" value="" id="flexCheckChecked" :checked="noDIPShow">
                                                    <label class="form-check-label" for="flexCheckChecked">
                                                        No DIP Reading
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="section-content discovery active">
                                            <template v-if="listDispenser.tank == 1 && !noDIPShow">
                                                <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">
                                                        {{ listDispenser?.product_name }}</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row align-items-center text-start">
                                                        <div class="col-md-2">
                                                            <label class="form-label">
                                                                <p class="m-0">OIL Stock </p>
                                                            </label>

                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label>Start Reading </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label>Tank Refill </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label>End Reading </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label>Adjustment </label>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label>Consumption </label>
                                                        </div>
                                                        <div class="col-md-2 offset-2 mb-3">
                                                            <div class="input-group">
                                                                <input disabled id="prReading"
                                                                       type="text" class="form-control"
                                                                       v-model="listDispenser.start_reading_mm">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >mm</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 col-md-2"></div>
                                                        <div class="mb-3 col-md-2" v-if="listDispenser.status == 'end'">
                                                            <div class="input-group">
                                                                <input id="prReading"
                                                                       type="text" class="form-control text-end"
                                                                       v-model="listDispenser.end_reading_mm">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >mm</span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="mb-3 col-md-2"></div>
                                                        <div class="mb-3 col-md-2"></div>
                                                        <div class="mb-3 col-md-2 offset-2">
                                                            <div class="input-group">
                                                                <input disabled id="prReading"
                                                                       type="text" class="form-control"
                                                                       v-model="listDispenser.start_reading">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 col-md-2" v-if="listDispenser.status == 'end'">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control"  disabled
                                                                       v-model="listDispenser.tank_refill">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="mb-3 col-md-2" v-if="listDispenser.status == 'end'">
                                                            <div class="input-group">
                                                                <input id="frReading" disabled
                                                                       type="text" class="form-control"
                                                                       v-model="listDispenser.end_reading">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >Liter</span>
                                                                </div>
                                                            </div>

<!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                        </div>
                                                        <div class="mb-3 col-md-2" v-if="listDispenser.status == 'end'">
                                                            <div class="input-group">
                                                                <input id="frReading" @blur="disableInput('frReading')" v-if="listDispenser.status == 'end'"
                                                                       type="text" class="form-control" disabled
                                                                       v-model="listDispenser.adjustment"
                                                                       @input="calculateAmount">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                </div>
                                                            </div>

<!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                        </div>

                                                        <div class="mb-3 col-md-2" v-if="listDispenser.status == 'end'">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="consumption" disabled  v-if="listDispenser.status == 'end'"
                                                                       v-model="listDispenser.consumption">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >Liter</span>
                                                                </div>
                                                            </div>
<!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            </template>
                                            <div class="card" v-if="listDispenser.dispensers.length > 0"
                                                 v-for="(d, dIndex) in listDispenser.dispensers">
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
                                                        <div class="mb-3 col-md-2">
                                                            <label>Start Reading </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" disabled
                                                                       v-model="n.start_reading">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="mb-3 col-md-2"  v-if="listDispenser.status == 'end'">
                                                            <label>End Reading </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control text-end" @blur="disableInput('frReading'+nIndex+dIndex)"
                                                                       v-if="listDispenser.status == 'end'"
                                                                       v-model="n.end_reading" @click="enableInput('frReading'+nIndex+dIndex)"
                                                                       @input="calculateAmountNozzle(dIndex, nIndex) ">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                </div>
                                                            </div>

<!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                        </div>
                                                        <div class="mb-3 col-md-2" v-if="listDispenser.status == 'end'">
                                                            <label>Adjustment </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control text-end" @blur="disableInput('frReading'+nIndex+dIndex)"
                                                                       v-if="listDispenser.status == 'end'"
                                                                       v-model="n.adjustment"
                                                                       @input="calculateAmountNozzle(dIndex, nIndex) " disabled>
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                </div>
                                                            </div>
<!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                        </div>

                                                        <div class="mb-3 col-md-2"  v-if="listDispenser.status == 'end' && n.pf != null && n.pf != ''">
                                                            <label class="text-center">PF </label>
                                                            <input type="text" disabled class="form-control" v-model="n.pf">
                                                        </div>
                                                        <div class="col-md-2 mb-3" v-else></div>
                                                        <div class="mb-3 col-md-2"  v-if="listDispenser.status == 'end'">
                                                            <label>Consumption </label>
                                                            <div class="input-group">
                                                                <input type="text" disabled class="form-control"
                                                                       v-model="n.consumption">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                </div>
                                                            </div>

<!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <template v-if="listDispenser.status != 'start'">
                                                <div class="row">
                                                    <div class="col-sm-7"></div>
                                                    <div class="col-sm-5 text-end mb-2">
                                                        <table class="table">
                                                            <tr>
                                                                <td style="font-size: 18px;padding: 0px;" class="">Total sale:</td>
                                                                <td style="font-size: 18px;padding: 0px;" class="text-end ">{{totalSale.toFixed(2)}} {{ listDispenser.unit }}</td>
                                                            </tr>
<!--                                                            <tr>-->
<!--                                                                <td style="font-size: 18px;padding: 0px;" class="">Total amount:</td>-->
<!--                                                                <td style="font-size: 18px;padding: 0px;" class="text-end ">{{totalAmount}} Tk</td>-->
<!--                                                            </tr>-->
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="row" v-if="listDispenser.pos_sale.length > 0">
                                                    <div class="col-sm-6">
                                                    </div>
                                                    <div class="col-sm-6 text-end">
                                                        <h4 style="text-align: left;margin-left: 5rem;">POS Sale</h4>
                                                        <div class="d-flex mb-3 justify-content-end"  v-for="pos in listDispenser.pos_sale">
                                                            <select class="form-control me-3" style="max-width: 210px" v-model="pos.category_id" disabled>
                                                                <option v-for="c in allAmountCategory" :value="c.id">{{c.name}}</option>
                                                            </select>
                                                            <div class="form-group">
                                                                <input class="form-control me-3 text-end"  style="max-width: 210px" type="number" v-model="pos.amount"
                                                                      disabled>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8"></div>
                                                    <div class="col-sm-4">
                                                        <table class="table">
                                                            <tr>
                                                                <td style="font-size: 18px;padding: 0px;" class="">Total POS sale:</td>
                                                                <td style="font-size: 18px;padding: 0px;" class="text-end ">{{totalPosSale()}} Tk</td>
                                                            </tr>
                                                            <tr v-if="totalAmount > 0">
                                                                <td style="font-size: 18px;padding: 0px;" class="">Remaining Balance: </td>
                                                                <td style="font-size: 18px;padding: 0px;" class="text-end ">{{totalAmount - totalPosSale()}} Tk</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-4"></div>
                                                    <div class="col-sm-8 text-end">
                                                        <div class="row"  v-for="(category,index) in categories">
                                                            <div class="col-sm-4 mb-3">
                                                                <select class="form-control me-3" style="max-width: 210px" v-model="category.category_id"
                                                                        @change="isDataExist(category.category_id, 'category_id', index, categories); filterProductPrice(index)" >
                                                                    <option v-for="c in allAmountCategory" :value="c.id">{{c.name}}</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-sm-3 mb-3">
                                                                <div class="input-group" style="width: 95%">
                                                                    <input type="text" class="form-control text-end" v-model="category.liter" disabled>
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-sm-4 mb-3">
                                                                <input class="form-control me-3 text-end"  style="width: 95%" type="number" step="any" v-model="category.amount" :id="'categories.'+index+'.amount'"
                                                                       @input="calculateValue(category.amount, index); filterProductPrice(index)"
                                                                       :name="'categories.'+index+'.amount'" placeholder="Amount here">
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                            <div class="col-sm-1 mb-3">
                                                                <button class="btn btn-primary"  style="height: 54px" v-if="index == 0" type="button" @click="addCategory">+</button>
                                                                <button class="btn btn-danger"  style="height: 54px"   v-else  type="button" @click="removeCategory(index)">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-4"></div>
                                                            <div class="col-sm-3 text-center">
                                                                <span><strong>Total: {{ totalLiter }} {{ listDispenser.unit }}</strong></span>
                                                            </div>
                                                            <div class="col-sm-4 text-center">
                                                                <span><strong>Amount: {{isNaN(totalPaid) ? 0 : totalPaid}} Tk</strong></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="text-center" v-else>Please Select any product</div>
                                </div>
                                <div class="row" style="text-align: right;" v-if="product_id">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading && listDispenser?.status == 'start'">Start</button>
                                        <button type="submit" class="btn btn-primary" v-if="!loading && listDispenser?.status == 'end'">End</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link :to="{name: 'ShiftSaleList'}" type="button" class="btn btn-danger">Cancel</router-link>
                                    </div>
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
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import Swal from 'sweetalert2/dist/sweetalert2.js'

export default {
    data() {
        return {
            loading: false,
            listData: [],
            listDispenser: null,
            product_id: '',
            productIndex: 0,
            totalSale: 0,
            totalAmount: 0,
            allAmountCategory: null,
            categories: [],
            totalPaid: 0,
            oilStock: false,
            noDIPShow: true,
            mismatchAllow: null,
            bstiChart: []
        }
    },
    computed: {
        totalLiter: function() {
            let total = 0;
            this.categories.map((v) => {
                if (v.liter != '') {
                    total += parseFloat(v.liter);
                }
            });
            return total;
        }
    },
    watch: {
        'listDispenser.end_reading_mm': function() {
            if (parseFloat(this.listDispenser.end_reading_mm) > parseFloat(this.listDispenser.tank_height)) {
                this.listDispenser.end_reading_mm = '';
                this.listDispenser.end_reading = 0;
            } else {
                this.getBstiChart(this.listDispenser.end_reading_mm);
            }
        },
        'listDispenser.end_reading': function() {
            this.calculateAmount();
        }
    },
    methods: {
        getBstiChart: function(height) {
            ApiService.POST(ApiRoutes.TankGetVolume, {product_id: this.product_id, height: height}, res => {
                if (parseInt(res.status) === 200) {
                    this.listDispenser.end_reading =  res.data;
                }
            });
        },
        updateOilStock: function() {
            if (this.listData.length > 0) {
               if ( this.listData[this.productIndex].tank == 1) {
                   return true;
               } else {
                   return false;
               }
            }
        },
        totalPosSale: function () {
            let total = 0
            this.listDispenser.pos_sale.map((v) => {
                total += parseFloat(v.amount)
            })
            return total
        },
        filterProductPrice: function(index) {
            this.categories[index]['liter'] = '';
            let category_id = this.categories[index]['category_id'];
            let product_price = [];
            this.allAmountCategory.map((v) => {
                if (v.id == category_id) {
                    product_price = v.product_price;
                }
            });
            let selling_price = this.listDispenser.selling_price;
            if (product_price.length > 0) {
                product_price.map((v) => {
                    if (v.product_id == this.product_id) {
                        selling_price = v.price;
                    }
                });
            }
            console.log(this.totalSale / selling_price);
            console.log(this.totalSale / selling_price);
            this.categories[index]['liter'] = parseFloat(this.categories[index]['amount'] / selling_price).toFixed(2);
        },
        calculateValue: function (amount) {
            this.totalPaid = 0
            this.categories.map(v => {
                this.totalPaid += parseFloat(v.amount)
            });
        },
        removeCategory: function(index) {
            this.categories.splice(index, 1);
        },
        addCategory: function() {
            this.categories.push({
                amount: '',
                category_id: '',
                liter: ''
            });
        },
        getTotalSale: function () {
            this.totalSale = 0
            this.totalAmount = 0
            this.listDispenser.dispensers.map((dispenser) => {
                dispenser.nozzle.map((nozzle) => {
                    this.totalSale += nozzle.consumption
                    if (nozzle.end_reading > 0) {
                        this.totalAmount += nozzle.amount
                    }
                })
            })
            this.totalSale < 0 ? this.totalSale = 0 : this.totalSale;
            this.totalSale < 0 ? this.totalAmount = 0 : this.totalAmount;
        },
        disableInput: function (id) {
            $('#'+id).prop('readonly', true);
        },
        enableInput: function (id) {
            $('#'+id).prop('readonly', false);
        },
        calculateLineProgress: function () {
            let progress = 100
            let eachProgress = Math.round(progress / (this.listData?.length - 1))
            return (eachProgress * this.productIndex)
        },
        calculateAmount: function () {
            this.listDispenser.consumption = parseFloat(this.listDispenser.start_reading) + parseFloat(this.listDispenser.tank_refill) - parseFloat(this.listDispenser.end_reading) + parseFloat(this.listDispenser.adjustment)
            this.listDispenser.amount = parseFloat(this.listDispenser.consumption ) * parseFloat(this.listDispenser.selling_price)
        },
        calculateAmountNozzle: function (dIndex, nIndex) {
            if (this.isNumeric(this.listDispenser.dispensers[dIndex].nozzle[nIndex].end_reading)) {
                let pf = 1;
                if (this.listDispenser.dispensers[dIndex].nozzle[nIndex].pf != null && this.listDispenser.dispensers[dIndex].nozzle[nIndex].pf != '') {
                    pf = this.listDispenser.dispensers[dIndex].nozzle[nIndex].pf;
                }
                if (parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].end_reading) < parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].start_reading)) {
                    this.listDispenser.dispensers[dIndex].nozzle[nIndex].consumption = (parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].max_value) - parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].start_reading) + parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].end_reading) - parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].adjustment)) * pf;
                } else {
                    this.listDispenser.dispensers[dIndex].nozzle[nIndex].consumption = (parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].end_reading) - parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].start_reading)  - parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].adjustment)) * parseFloat(pf)
                }
                this.listDispenser.dispensers[dIndex].nozzle[nIndex].amount = parseFloat(this.listDispenser.dispensers[dIndex].nozzle[nIndex].consumption) * parseFloat(this.listDispenser.selling_price)
            } else {
                this.listDispenser.dispensers[dIndex].nozzle[nIndex].consumption = 0
                this.listDispenser.dispensers[dIndex].nozzle[nIndex].amount = 0
            }
            this.getTotalSale()
        },
        getProduct: function () {
            ApiService.POST(ApiRoutes.ProductList, {limit: 5000, page: 1, order_mode: 'ASC', shift_sale: 1}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                }
            });
        },
        getCategory: function () {
            this.categories = []
            ApiService.POST(ApiRoutes.ShiftSaleGetCategory, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.allAmountCategory = res.data;
                    let category_id = '';
                    res.data.map((v) => {
                        if (v.selected == true) {
                            category_id = v.id;
                        }
                    });
                    this.categories.push({
                        amount: '',
                        category_id: category_id,
                        liter: ''
                    });
                }
            });
        },
        getProductDispenser: function () {
            this.totalSale = 0
            this.totalAmount = 0
            ApiService.POST(ApiRoutes.ProductDispenser, {product_id: this.product_id}, res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listDispenser = res.data;
                    this.getCategory()
                    this.updateOilStock();
                }
                this.getTotalSale()
            });
        },
        totalShiftParcent: function (totalNozzleConsumption) {
           return ((totalNozzleConsumption - this.listDispenser.consumption) /this.listDispenser.consumption) * 100
        },
        submit: function () {
            ApiService.ClearErrorHandler();
            this.listDispenser.categories = this.categories;
            let flag = false;
            if (this.listDispenser.status === 'end') {
                let totalCategoryAmount = 0
                let totalConsumption = 0
                this.listDispenser.categories.map(v => {
                    totalCategoryAmount += parseFloat(v.amount)
                });
                // if ((this.totalAmount - this.totalPosSale()) != totalCategoryAmount) {
                this.listDispenser.dispensers.map(dispenser => {
                    dispenser.nozzle.map(nozzle => {
                        totalConsumption += parseFloat(nozzle.consumption)
                        if (nozzle.end_reading < nozzle.start_reading) {
                            flag = true;
                        }
                    })
                })

                let totalSale = parseFloat(this.listDispenser.total_pos_sale_liter) + parseFloat(this.totalLiter);
                if (parseFloat(this.totalSale).toFixed(2) !== parseFloat(totalSale).toFixed(2)) {
                    this.$toast.error('Total sale and total liter does not match');
                    return;
                }

                // check if mismatch allow
                // if (this.mismatchAllow != null && this.listDispenser.tank == 1) {
                //     if (this.totalShiftParcent(totalConsumption) > this.mismatchAllow) {
                //         this.loading = false
                //         this.$toast.error('The mismatch is grater than allowed consumption')
                //         return
                //     }
                // }
                if (this.noDIPShow) {
                    this.listDispenser.end_reading = this.listDispenser.end_reading === 0 ? parseFloat(this.listDispenser.consumption) - parseFloat(this.totalSale) : this.listDispenser.end_reading;
                }
                this.listDispenser.amount = totalCategoryAmount;
                if (totalConsumption === 0) {
                    this.$toast.error('The consumption amount is 0');
                    return;
                }
            }
            if (flag === true) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Your nozzle end reading is correct",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.save();
                    }
                });
            } else {
                this.save();
            }
        },
        save: function () {
            this.loading = true
            ApiService.POST(ApiRoutes.ShiftSaleAdd, this.listDispenser, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                    if (this.listDispenser.status == 'start') {
                        this.$router.push({
                            name: 'ShiftSaleListStart'
                        })
                    } else {
                        this.$router.push({
                            name: 'ShiftSaleView',
                            params: {
                                id: res.shift_sale_id
                            }
                        })
                    }
                } else if (parseInt(res.status) === 200) {
                    this.$toast.warning(res.message);
                } else if (parseInt(res.status) === 400) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: res.message,
                    });
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getSingleMitchMatch: function () {
            ApiService.POST(ApiRoutes.companySingle, this.param,res => {
                if (parseInt(res.status) === 200) {
                    if (res.data.sale_mismatch_allow != null) {
                        this.mismatchAllow = res.data.sale_mismatch_allow
                    }
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
    },
    created() {
        this.getProduct()
        this.getSingleMitchMatch()
    },
    mounted() {
        if (this.$route.query.product_id != undefined) {
            this.product_id = this.$route.query.product_id
            this.getProduct()
            this.getProductDispenser()
           // this.getBstiChart();
        }
        $('#dashboard_bar').text('Shift Sale Start')
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
.input-group-append {
    width: 25%;
}
</style>
