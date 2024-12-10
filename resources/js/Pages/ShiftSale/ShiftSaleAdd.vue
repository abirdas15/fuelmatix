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
                                <div class="">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-3 form-group">
                                            <label>Select Date</label>
                                            <input type="text" class="form-control date bg-white" name="date" v-model="date">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Select Product</label>
                                            <select class="form-control" v-model="product_id" name="product_id">
                                                <option :value="p.id" v-for="(p, pIndex) in listData">{{ p.name }}</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Select Shift Name</label>
                                            <select class="form-control" v-model="shift_name_id" name="shift_name_id">
                                                <option :value="p.id" v-for="(p, pIndex) in shiftNames">{{ p.name }}</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-primary mt-4" type="button" @click="searchDispenser()">Search</button>
                                        </div>
                                    </div>
                                    <template v-if="apiLoading">
                                        <div :style="{ width: '100%', height: '100px' }" class="mb-2">
                                            <SkeletonLoaderVue animation="fade" />
                                        </div>
                                        <div :style="{ width: '100%', height: '100px' }" class="mb-2">
                                            <SkeletonLoaderVue animation="fade" />
                                        </div>
                                        <div :style="{ width: '100%', height: '100px' }" class="mb-2">
                                            <SkeletonLoaderVue animation="fade" />
                                        </div>
                                        <div :style="{ width: '100%', height: '100px' }" class="mb-2">
                                            <SkeletonLoaderVue animation="fade" />
                                        </div>
                                        <div :style="{ width: '100%', height: '100px' }" class="mb-2">
                                            <SkeletonLoaderVue animation="fade" />
                                        </div>
                                    </template>
                                    <template v-if="listDispenser && !apiLoading">
                                        <div class="card" v-for="(tank,tankIndex) in listDispenser.tanks">
                                            <div class="card-header">
                                                <h5 class="card-title w-100">
                                                    <div class="row">
                                                        <div class="col-md-6"> Tank: {{ tank.tank_name }}</div>
                                                        <div class="col-md-6 float-end">
                                                            <div class="form-check d-flex justify-content-end">
                                                                <input class="form-check-input" type="checkbox" v-model="tank.noDIPShow" value="" :id="tank.id">
                                                                <label class="form-check-label" :for="tank.id">
                                                                    No DIP Reading
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="section-content discovery active">
                                                    <template v-if="listDispenser.tank === '1' && !tank.noDIPShow">
                                                        <div class="card">
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
                                                                        <div class="p-inputgroup">
                                                                            <InputNumber disabled="disabled" v-model="tank.start_reading_mm" inputId="locale-user" :minFractionDigits="quantityFractionDigit" :maxFractionDigits="quantityFractionDigit"/>
                                                                            <span class="p-inputgroup-addon">mm</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 col-md-2"></div>
                                                                    <div class="mb-3 col-md-2" v-if="listDispenser.status === 'end'">
                                                                        <div class="input-group">
<!--                                                                            <input id="prReading"-->
<!--                                                                                   type="text" class="form-control text-end"-->
<!--                                                                                   v-model="tank.end_reading_mm" @input="getReading($event, 'end_reading', tankIndex, tank.id)">-->

                                                                            <InputNumber
                                                                                v-model="tank.end_reading_mm"
                                                                                @input="onTankInput($event, 'end_reading', tankIndex, tank.id)"
                                                                                inputId="locale-user"
                                                                                :minFractionDigits="quantityFractionDigit"
                                                                                :maxFractionDigits="quantityFractionDigit"/>

                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text" >mm</span>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div class="mb-3 col-md-2"></div>
                                                                    <div class="mb-3 col-md-2"></div>
                                                                    <div class="mb-3 col-md-2 offset-2">
                                                                        <div class="input-group">
                                                                            <InputNumber disabled id="prReading"  v-model="tank.start_reading" inputId="locale-user" :minFractionDigits="quantityFractionDigit" :maxFractionDigits="quantityFractionDigit"/>
<!--                                                                            <input disabled id="prReading"-->
<!--                                                                                   type="text" class="form-control"-->
<!--                                                                                   v-model="tank.start_reading">-->
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 col-md-2" v-if="listDispenser.status === 'end'">
                                                                        <div class="input-group">
                                                                            <InputNumber disabled v-model="tank.tank_refill" inputId="locale-user" :minFractionDigits="quantityFractionDigit" :maxFractionDigits="quantityFractionDigit"/>
<!--                                                                            <input type="text" class="form-control"  disabled-->
<!--                                                                                   v-model="tank.tank_refill">-->
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div class="mb-3 col-md-2" v-if="listDispenser.status === 'end'">
                                                                        <div class="input-group">
                                                                            <InputNumber id="frReading" disabled v-model="tank.end_reading" inputId="locale-user" :minFractionDigits="quantityFractionDigit" :maxFractionDigits="quantityFractionDigit"/>
<!--                                                                            <input id="frReading" disabled-->
<!--                                                                                   type="text" class="form-control"-->
<!--                                                                                   v-model="tank.end_reading">-->
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text" >Liter</span>
                                                                            </div>
                                                                        </div>

                                                                        <!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                                    </div>
                                                                    <div class="mb-3 col-md-2" v-if="listDispenser.status === 'end'">
                                                                        <div class="input-group">
                                                                            <InputNumber v-if="listDispenser.status === 'end'" id="frReading" @blur="disableInput('frReading')" @input="calculateAmount(tankIndex)"  v-model="tank.adjustment" inputId="locale-user" :minFractionDigits="quantityFractionDigit" :maxFractionDigits="quantityFractionDigit"/>
<!--                                                                            <input id="frReading" @blur="disableInput('frReading')" v-if="listDispenser.status === 'end'"-->
<!--                                                                                   type="text" class="form-control" disabled-->
<!--                                                                                   v-model="tank.adjustment"-->
<!--                                                                                   @input="calculateAmount(tankIndex)">-->
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                                    </div>

                                                                    <div class="mb-3 col-md-2" v-if="listDispenser.status === 'end'">
                                                                        <div class="input-group">
                                                                            <InputNumber v-if="listDispenser.status === 'end'"
                                                                                         disabled="disabled"
                                                                                         v-model="tank.consumption"
                                                                                         inputId="locale-user"
                                                                                         :minFractionDigits="quantityFractionDigit"
                                                                                         :maxFractionDigits="quantityFractionDigit"/>
<!--                                                                            <input type="text" class="form-control" id="consumption" disabled  v-if="listDispenser.status === 'end'"-->
<!--                                                                                   v-model="tank.consumption">-->
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
                                                    <div v-if="tank.dispensers.length > 0" v-for="(d, dIndex) in tank.dispensers">
                                                        <div class="custom-bg">
                                                            <h5 class="card-title">Dispenser: {{ d.dispenser_name }}</h5>
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
                                                                    <div class="form-group">
                                                                        <div class="input-group">
                                                                            <InputNumber
                                                                                @input="(value) => n.start_reading = Number(value)"
                                                                                disabled="disabled"
                                                                                v-model="n.start_reading"
                                                                                inputId="locale-user"
                                                                                :minFractionDigits="quantityFractionDigit"
                                                                                :maxFractionDigits="quantityFractionDigit"/>
                                                                            <!--                                                                        <input type="text" class="form-control" disabled-->
                                                                            <!--                                                                               v-model="n.start_reading">-->
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="mb-3 col-md-3"  v-if="listDispenser.status === 'end'">
                                                                    <label>End Reading </label>
                                                                    <div class="input-group">

                                                                        <InputNumber
                                                                            @blur="disableInput('frReading'+nIndex+dIndex)"
                                                                            v-if="listDispenser.status === 'end'"
                                                                            :value="n.end_reading === 0 ? '' : n.end_reading"
                                                                            @click="enableInput('frReading'+nIndex+dIndex)"
                                                                            @input="updateEndReading($event, tankIndex, dIndex, nIndex)"
                                                                            @keyup="preventArrowKeyIncrement"
                                                                            :maxFractionDigits="quantityFractionDigit"
                                                                        />
<!--                                                                        <input-->
<!--                                                                            type="text"-->
<!--                                                                            class="form-control text-end"-->
<!--                                                                            @blur="disableInput('frReading'+nIndex+dIndex)"-->
<!--                                                                            v-if="listDispenser.status === 'end'"-->
<!--                                                                            v-model="n.end_reading"-->
<!--                                                                            @click="enableInput('frReading'+nIndex+dIndex)"-->
<!--                                                                            @input="calculateAmountNozzle(dIndex, nIndex, tankIndex)">-->
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text">
                                                                                <template v-if="n.mac !== null">
                                                                                    <span class="cursor-pointer" :class="'nozzle' + n.id" @click="getNozzleLatestReading(tankIndex, dIndex, nIndex, n.id)">
                                                                                        Get
                                                                                    </span>
                                                                                    <span style="display: none" :class="'nozzle' + n.id">
                                                                                        <i class="fa fa-spinner fa-spin"></i>
                                                                                    </span>
                                                                                </template>
                                                                                <template v-else>
                                                                                    {{ listDispenser.unit }}
                                                                                </template>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                                </div>
                                                                <div class="mb-3 col-md-2" v-if="listDispenser.status === 'end'">
                                                                    <label>Adjustment </label>
                                                                    <div class="input-group">
                                                                        <InputNumber
                                                                            @input="calculateAmountNozzle(dIndex, nIndex, tankIndex);"
                                                                            v-if="listDispenser.status === 'end'"
                                                                            v-model="n.adjustment"
                                                                            disabled="disabled"
                                                                            @click="enableInput('frReading'+nIndex+dIndex)"
                                                                            :minFractionDigits="quantityFractionDigit"
                                                                            :maxFractionDigits="quantityFractionDigit"
                                                                        />
<!--                                                                        <input type="text" class="form-control text-end" @blur="disableInput('frReading'+nIndex+dIndex)"-->
<!--                                                                               v-if="listDispenser.status === 'end'"-->
<!--                                                                               v-model="n.adjustment"-->
<!--                                                                               @input="calculateAmountNozzle(dIndex, nIndex, tankIndex) " disabled>-->
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                                </div>

                                                                <div class="mb-3 col-md-2"  v-if="listDispenser.status === 'end' && n.pf != null && n.pf != ''">
                                                                    <label class="text-center">PF </label>
                                                                    <input type="text" disabled class="form-control" v-model="n.pf">
                                                                </div>
                                                                <div class="mb-3 col-md-2"  v-if="listDispenser.status === 'end'">
                                                                    <label>Consumption </label>
                                                                    <div class="input-group">
                                                                        <InputNumber
                                                                            v-model="n.consumption"
                                                                            disabled="disabled"
                                                                            :minFractionDigits="quantityFractionDigit"
                                                                            :maxFractionDigits="quantityFractionDigit"
                                                                        />
<!--                                                                        <input type="text" disabled class="form-control"-->
<!--                                                                               v-model="n.consumption">-->
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <!--                                                            <input class="form-control" value="0" v-if="listDispenser.status == 'start'" disabled>-->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <template v-if="listDispenser && listDispenser.status !== 'start'">
                                    <div class="">
                                        <div class="row">
                                            <div class="col-sm-7"></div>
                                            <div class="col-sm-5 text-end mb-2">
                                                <table class="table">
                                                    <tr>
                                                        <td style="font-size: 18px;padding: 0px;" class="">Total sale:</td>
                                                        <td style="font-size: 18px;padding: 0px;" class="text-end ">{{ format_number(totalSale) }} {{ listDispenser.unit }}</td>
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
                                                <div class="d-flex mb-3 justify-content-end">
                                                    <table class="table table-bordered" v-if="listDispenser.pos_sale.length > 0">
                                                        <thead>
                                                            <tr class="bg-custom">
                                                                <th colspan="3" class="text-center">POS Sale</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <th class="text-center">Payment Method</th>
                                                                <th class="text-center">Quantity</th>
                                                                <th class="text-end">Amount</th>
                                                            </tr>
                                                                <tr  v-for="pos in listDispenser.pos_sale">
                                                                    <td>
                                                                        {{pos.category_name}}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ pos.quantity_format }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ pos.amount_format }}
                                                                    </td>
                                                                </tr>
                                                            <tr>
                                                                <th colspan="1">Total POS Sale:</th>
                                                                <th>{{ listDispenser.total_pos_sale_liter_format }}</th>
                                                                <th>{{ format_number(totalPosSale()) }} Tk</th>
                                                            </tr>
                                                            <tr v-if="totalAmount > 0">
                                                                <th colspan="2" class="">Remaining Balance: </th>
                                                                <th class="text-end ">{{ format_number(totalAmount - totalPosSale()) }} Tk</th>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-4"></div>
                                            <div class="col-sm-8 text-end">
                                                <div class="row"  v-for="(category,index) in categories">
                                                    <div class="col-sm-4 mb-3">
                                                        <select class="form-control me-3" v-model="category.category_id"
                                                                @change="isDataExist(category.category_id, 'category_id', index, categories); filterProductPrice(index)" >
                                                            <option v-for="c in allAmountCategory" :value="c.id">{{c.name}}</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-sm-3 mb-3">
                                                        <div class="input-group">
                                                            <InputNumber
                                                                v-model="category.liter"
                                                                disabled="disabled"
                                                                :minFractionDigits="quantityFractionDigit"
                                                                :maxFractionDigits="quantityFractionDigit"
                                                                class="w-100"
                                                            />
                                                            <div class="input-group-append">
                                                                <span class="input-group-text" >{{ listDispenser.unit }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-sm-4 mb-3">
                                                        <InputNumber
                                                            v-model="category.amount"
                                                            :id="'categories.'+index+'.amount'"
                                                            @input="categoryInput($event, index);"
                                                            :maxFractionDigits="numberFractionDigit"
                                                            :name="'categories.'+index+'.amount'"
                                                            placeholder="Amount here"
                                                            class="border-right w-100"
                                                        />
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                    <div class="col-sm-1 mb-3">
                                                        <button class="btn btn-primary" style="height: 54px" v-if="index === categories.length - 1" type="button" @click="addCategory">+</button>
                                                        <button class="btn btn-danger"  style="height: 54px"   v-else  type="button" @click="removeCategory(index)">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-4"></div>
                                                    <div class="col-sm-3 text-center">
                                                        <span><strong>Total: {{ format_quantity(totalLiter) }} {{ listDispenser.unit }}</strong></span>
                                                    </div>
                                                    <div class="col-sm-4 text-center">
                                                        <span><strong>Amount: {{ format_number(totalPaid) }} Tk</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div class="row" style="text-align: right;" v-if="product_id">
                                    <div class="mb-3 col-md-6">

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <button type="submit" class="btn btn-primary" v-if="!loading && listDispenser?.status === 'start'">Start</button>
                                        <button type="submit" class="btn btn-primary" v-if="!loading && listDispenser?.status === 'end'">End</button>
                                        <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                        <router-link v-if="!loading && listDispenser?.status === 'start' || !loading && listDispenser?.status === 'end'" :to="{name: 'ShiftSaleList'}" type="button" class="btn btn-danger">Cancel</router-link>
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
import moment from "moment";
import SkeletonLoaderVue  from 'skeleton-loader-vue';
import Table from "../../admin/Pages/Common/Table.vue";

export default {
    components: {Table, SkeletonLoaderVue  },
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
            categories: [
                {
                    category_id: '',
                    liter: 0,
                    amount: null,
                },
            ],
            totalPaid: 0,
            oilStock: false,
            noDIPShow: true,
            mismatchAllow: null,
            bstiChart: [],
            date: moment().format('YYYY-MM-DD'),
            apiLoading: false,
            shiftNames: [],
            shift_name_id: ''
        }
    },
    computed: {
        formattedEndReading: {
            get() {
                return this.n.end_reading === 0 ? '' : this.n.end_reading;
            },
            set(value) {
                this.n.end_reading = value === '' ? 0 : value;
            },
        },
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
        updateEndReading(value, tankIndex, dIndex, nIndex) {
            this.onNozzleInput(value, tankIndex, dIndex, nIndex);
        },
        categoryInput(value, index) {
            this.categories[index]['amount'] = Number(value);
            setTimeout(() => {
                this.calculateValue(value);
                this.filterProductPrice(index);
            }, 200);
        },
        onTankInput(value, end_reading, tankIndex, id) {
            this.listDispenser.tanks[tankIndex]['end_reading_mm'] = Number(value);
            this.getReading(value, end_reading, tankIndex, id)
        },
        onNozzleInput(value, tankIndex, dIndex, nIndex, ) {
            this.listDispenser.tanks[tankIndex]['dispensers'][dIndex]['nozzle'][nIndex]['end_reading'] = value === null ? null : Number(value);
            this.calculateAmountNozzle(dIndex, nIndex, tankIndex);
        },
        fetchShiftName() {
            ApiService.POST(ApiRoutes.ShiftName, {},(res) => {
                if (parseInt(res.status) === 200) {
                    this.shiftNames = res.data;
                }
            });
        },
        getNozzleLatestReading(tankIndex, dispenserIndex, nozzleIndex, nozzleId) {
            $('.nozzle'+ nozzleId).toggle();
            ApiService.POST(ApiRoutes.ShiftSale + '/getNozzleLatestReading', {nozzle_id: nozzleId}, (res) => {
                $('.nozzle'+ nozzleId).toggle();
                if (parseInt(res.status) === 200) {
                    this.listDispenser.tanks[tankIndex]['dispensers'][dispenserIndex]['nozzle'][nozzleIndex]['end_reading'] =  res.data.liters;
                    setTimeout(() => {
                        this.calculateAmountNozzle(dispenserIndex, nozzleIndex, tankIndex);
                    }, 200);
                }
            });
        },
        getReading: function(event, field, index, tank_id) {
            this.getBstiChart(event, field, index, tank_id);
        },
        getBstiChart: function(height, field, index, tank_id) {
            ApiService.POST(ApiRoutes.TankGetVolume, {tank_id: tank_id, height: height}, res => {
                if (parseInt(res.status) === 200) {
                    this.listDispenser.tanks[index][field] =  res.data;
                    this.calculateAmount(index);
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
            this.categories[index]['liter'] = 0;
            let category_id = this.categories[index]['category_id'];
            let product_price = [];
            this.allAmountCategory.map((v) => {
                if (v.id === category_id) {
                    product_price = v.product_price;
                }
            });
            let selling_price = this.listDispenser.selling_price;
            if (product_price.length > 0) {
                product_price.map((v) => {
                    if (v.product_id === this.product_id) {
                        selling_price = v.price;
                    }
                });
            }
            let liter = parseFloat(this.categories[index]['amount'])/ parseFloat(selling_price);
            this.categories[index]['liter'] = Number(liter);
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
                amount: null,
                category_id: '',
                liter: 0
            });
        },
        getTotalSale: function () {
            this.totalSale = 0
            this.totalAmount = 0
            this.listDispenser.tanks.map((tank) => {
                tank.dispensers.map((dispenser) => {
                    dispenser.nozzle.map((nozzle) => {
                        this.totalSale += nozzle.consumption
                        if (nozzle.end_reading > 0) {
                            this.totalAmount += nozzle.amount
                        }
                    })
                })
            });
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
        calculateAmount: function (tankIndex) {
            this.listDispenser.tanks[tankIndex]['consumption'] = parseFloat(this.listDispenser.tanks[tankIndex]['start_reading']) + parseFloat(this.listDispenser.tanks[tankIndex]['tank_refill']) - parseFloat(this.listDispenser.tanks[tankIndex]['end_reading']) + parseFloat(this.listDispenser.tanks[tankIndex]['adjustment'])
            this.listDispenser.amount = parseFloat(this.listDispenser.tanks[tankIndex].consumption ) * parseFloat(this.listDispenser.selling_price)
        },
        calculateAmountNozzle: function (dIndex, nIndex, tankIndex) {
            let pf = 1;
            if (this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].pf != null && this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].pf !== '') {
                pf = this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].pf;
            }
            if (parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].end_reading) < parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].start_reading)) {
                this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].consumption = (parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].max_value) - parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].start_reading) + parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].end_reading) - parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].adjustment)) * pf;
            } else {
                this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].consumption = (parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].end_reading) - parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].start_reading)  - parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].adjustment)) * parseFloat(pf)
            }
            this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].amount = parseFloat(this.listDispenser.tanks[tankIndex].dispensers[dIndex].nozzle[nIndex].consumption) * parseFloat(this.listDispenser.selling_price)
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
                        if (v.selected === true) {
                            category_id = v.id;
                        }
                    });
                    this.categories.push({
                        amount: null,
                        category_id: category_id,
                        liter: 0
                    });
                }
            });
        },
        searchDispenser: function() {
            const currentQuery = this.$route.query;

            // Check if the current route and query are the same
            if (currentQuery.product_id !== this.product_id || currentQuery.date !== this.date) {
                this.$router.replace({
                    name: 'ShiftSaleAdd',
                    query: { product_id: this.product_id, date: this.date }
                });
            }

            // Call your method to fetch the product dispenser data
            this.getProductDispenser();
        },
        getProductDispenser: function () {
            this.totalSale = 0
            this.totalAmount = 0
            ApiService.ClearErrorHandler();
            this.apiLoading = true;
            ApiService.POST(ApiRoutes.ProductDispenser, {product_id: this.product_id, date: this.date, shift_name_id: this.shift_name_id}, res => {
                this.apiLoading = false;
                if (parseInt(res.status) === 200) {
                    this.listDispenser = res.data;
                    this.getCategory()
                    this.updateOilStock();
                    this.shift_name_id = res.data.shift_name_id;
                } else {
                    ApiService.ErrorHandler(res.errors);
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
                this.listDispenser.tanks.map((tank) => {
                    tank.dispensers.map(dispenser => {
                        dispenser.nozzle.map(nozzle => {
                            totalConsumption += parseFloat(nozzle.consumption)
                            if (nozzle.end_reading < nozzle.start_reading) {
                                flag = true;
                            }
                        })
                    })
                });
                let totalSale = parseFloat(this.listDispenser.total_pos_sale_liter) + parseFloat(this.totalLiter);
                let difference = parseInt(this.totalSale) - parseInt(totalSale);
                if (Math.abs(difference) > 1) {
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
                    if (this.listDispenser.status === 'start') {
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
        this.getProduct();
        this.getSingleMitchMatch();
        this.fetchShiftName();
    },
    mounted() {
        if (this.$route.query.product_id !== undefined && this.$route.query.date !== undefined) {
            this.product_id = this.$route.query.product_id;
            this.date = this.$route.query.date;
            this.getProductDispenser();
        }
        this.getProduct()
        $('#dashboard_bar').text('Shift Sale Start')
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                onChange: (date, dateStr) => {
                    this.date = dateStr
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
.input-group-append {
    width: 25%;
}
.card-header {
    padding: 10px !important;
}
.animation--fade {
    width: 100% !important;
}
.bg-custom {
    background-color: #d7d2d2;
}
table{
    tbody{
        tr{
            border-color: #000000 !important;
            th, td {
                border-color: #000000 !important;
            }
        }
    }
}
</style>
