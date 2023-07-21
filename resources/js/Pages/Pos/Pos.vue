<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active">
                        <router-link :to="{name: 'Dashboard'}">Home</router-link>
                    </li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Pos</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="user-search">
                                        <div class="input-group mb-3">
                                                <span class="input-group-text">
                                                    <i class="fa-regular fa-user"></i>
                                                </span>
                                            <input type="text" class="form-control">
                                            <span class="input-group-text">
                                                    <i class="fa-solid fa-user-plus"></i>
                                                </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="blank-white">

                                    </div>
                                </div>
                            </div>
                            <div class="default-cart">
                                <div class="t-section">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Subtotal</th>
                                            <th class="text-end"></th>
                                        </tr>
                                        </thead>
                                        <tbody v-if="sale.length > 0">
                                        <tr v-for="(s, i) in sale">
                                            <td>
                                                <div class="fw-bold">{{ s.name }}</div>
                                                <div>
                                                    <span class="badge badge-primary">{{ s.type }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="btn-cart-plus cursor-pointer"
                                                         @click="updateProduct('minus', i)">-
                                                    </div>
                                                    <input class="form-control control-sm" step='0.01' type="number"
                                                           v-model="s.quantity" @input="updateSubtotal(i)">
                                                    <div class="btn-cart-plus cursor-pointer"
                                                         @click="updateProduct('plus', i)">+
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end" style="width: 130px;">
                                                ৳ {{ s.price }}
                                            </td>
                                            <td class="text-end">
                                                <input class="form-control w-100 control-sm text-end" step="any"
                                                       type="number" v-model="s.subtotal" @input="updateQuantity(i)">
                                            </td>
                                            <td class="text-end">
                                                <i class="fa-regular text-danger fa-trash-can cursor-pointer"
                                                   @click="removeProduct(i)"></i>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-end">Total: ৳ <strong>{{ getProductTotalPrice() }}</strong>
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tbody v-if="sale.length === 0">
                                        <tr class="text-center">
                                            <td colspan="20">Please add product</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="btn-section text-center mt-3">
                                    <button class="btn btn-warning me-2 width-fixed">Hold <i
                                        class="fa-regular fa-hand"></i></button>
                                    <button class="btn btn-danger me-2 width-fixed" @click="this.sale = []">Reset <i
                                        class="fa-solid fa-arrow-rotate-left"></i></button>
                                    <button class="btn btn-success width-fixed" v-if="!loading" @click="order">Paynow <i
                                        class="fa-solid fa-money-bill-1"></i></button>
                                    <button class="btn btn-success width-fixed" v-if="loading">Paying....
                                        <i class="fa fa-spinner fa-spin"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Username">
                                    </div>
                                </div>
                            </div>
                            <div class="default-cart">
                                <div class="d-flex flex-nowrap overflow-auto pb-2 mb-2">
                                    <button class="btn btn-sm light btn-dark me-2"
                                            :class="{'active-btn': selectedProductIndex == undefined}"
                                            @click="getProducts()">All Categories
                                    </button>
                                    <button class="btn btn-sm light btn-dark me-2" v-for="(type, i) in productType"
                                            :class="{'active-btn': i == selectedProductIndex}"
                                            @click="getProducts(type.id, i)">{{ type.name }}
                                    </button>
                                </div>
                                <div class="product-list">
                                    <div class="each-product" v-for="(p, i) in products" @click="cartProduct(p)">
                                        <div class="img">
                                            <img :src="'https://via.placeholder.com/100x70?text='+p.name" alt="">
                                        </div>
                                        <div class="detail">
                                            <div class="name">{{ p.name }}</div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="desc">{{ p.product_type }}</div>
                                                <div>৳ {{ p.selling_price }}</div>
                                            </div>
                                            <p class="mt-1 mb-0" v-if="i < 9">
                                                <kbd>Alt</kbd>+<kbd>{{ getProductNumber(i) }}</kbd></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="print" v-if="singleSaleData">
            <header class="text-center">
                <img id="logo" class="media" src="/images/fuelBL.jpeg"></img>
            </header>
            <p>Invoice Number : {{ singleSaleData.invoice_number }}</p>
            <table class="bill-details">
                <tbody>
                <tr>
                    <td>Date : <span>{{ singleSaleData.date }}</span></td>
                </tr>
                <tr>
                    <th class="center-align" colspan="2"><span class="receipt">Original Receipt</span></th>
                </tr>
                </tbody>
            </table>

            <table class="items">
                <thead>
                <tr>
                    <th class="heading name">Item</th>
                    <th class="heading qty">Qty</th>
                    <th class="heading rate">Price</th>
                    <th class="heading amount">Subtotal</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="p in singleSaleData.products">
                    <td>{{ p?.product_name }}</td>
                    <td>{{ p.quantity }}</td>
                    <td class="price">{{ p.price }}</td>
                    <td class="price">{{ p.subtotal }}</td>
                </tr>
                <tr>
                    <th colspan="3" class="total text">Total</th>
                    <th class="total price">{{singleSaleData.total_amount}}</th>
                </tr>
                </tbody>
            </table>
            <section>
                <p>
                    Paid by : <span>CASH</span>
                </p>
                <p style="text-align:center">
                    Thank you for your visit!
                </p>
            </section>
        </div>
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import {Printd} from "printd"

export default {
    data() {
        return {
            sale: [],
            products: [],
            productType: [],
            selectedProductIndex: null,
            saleId: null,
            loading: false,
            singleSaleData: null,
            printD: null,
            cssText: `
                 @page {
                    size: 2.8in 11in;
                    margin-top: 0cm;
                    margin-left: 0cm;
                    margin-right: 0cm;
                }

                table {
                    width: 100%;
                }

                tr {
                    width: 100%;

                }

                h1 {
                    text-align: center;
                    vertical-align: middle;
                }

                #logo {
                    width: 60%;
                    text-align: center;
                    -webkit-align-content: center;
                    align-content: center;
                    padding: 5px;
                    margin: 2px;
                    display: block;
                    margin: 0 auto;
                }

                header {
                    width: 100%;
                    text-align: center;
                    -webkit-align-content: center;
                    align-content: center;
                    vertical-align: middle;
                }

                .items thead {
                    text-align: center;
                }

                .center-align {
                    text-align: center;
                }

                .bill-details td {
                    font-size: 12px;
                }

                .receipt {
                    font-size: medium;
                }

                .items .heading {
                    font-size: 12.5px;
                    text-transform: uppercase;
                    border-top:1px solid black;
                    margin-bottom: 4px;
                    border-bottom: 1px solid black;
                    vertical-align: middle;
                }

                .items thead tr th:first-child,
                .items tbody tr td:first-child {
                    width: 47%;
                    min-width: 47%;
                    max-width: 47%;
                    word-break: break-all;
                    text-align: left;
                }

                .items td {
                    font-size: 12px;
                    text-align: right;
                    vertical-align: bottom;
                }

                .price::before {
                    content: "৳";
                    font-family: Arial;
                    text-align: right;
                }

                .sum-up {
                    text-align: right !important;
                }
                .total {
                    font-size: 13px;
                    border-top:1px dashed black !important;
                    border-bottom:1px dashed black !important;
                }
                .total.text, .total.price {
                    text-align: right;
                }
                .total.price::before {
                    content: "৳";
                }
                .line {
                    border-top:1px solid black !important;
                }
                .heading.rate {
                    width: 20%;
                }
                .heading.amount {
                    width: 25%;
                }
                .heading.qty {
                    width: 5%
                }
                p {
                    padding: 1px;
                    margin: 0;
                }
                section, footer {
                    font-size: 12px;
                }
            `
        }
    },
    methods: {
        updateSubtotal: function (i) {
            this.sale[i].subtotal = parseFloat(this.sale[i].price * this.sale[i].quantity).toFixed(2)
        },
        updateQuantity: function (i) {
            this.sale[i].quantity = parseFloat(this.sale[i].subtotal / this.sale[i].price).toFixed(2)
        },
        order: function () {
            if (this.sale.length == 0) {
                return;
            }
            this.loading = true
            ApiService.POST(ApiRoutes.SaleAdd, {payment_method: 'cash', products: this.sale}, res => {
                if (parseInt(res.status) === 200) {
                    this.saleId = res.data
                    this.singleOrder()
                }
            });
        },
        singleOrder: function () {
            ApiService.POST(ApiRoutes.SaleSingle, {id: this.saleId}, res => {
                if (parseInt(res.status) === 200) {
                    this.singleSaleData = res.data
                    setTimeout(() => {
                        this.loading = false
                        this.print()
                    }, 1000)
                }
            });
        },
        print () {
            this.printD.print( document.getElementById('print'), [this.cssText])
        },

        getProductTotalPrice: function () {
            let total = 0
            this.sale.map(v => {
                total += parseFloat(v.subtotal)
            })
            if (isNaN(total)) {
                return 0
            }
            return total
        },
        updateProduct: function (type, i) {
            if (type == 'minus') {
                if (this.sale[i].quantity == 1) {
                    this.sale.splice(i, 1)
                } else {
                    this.sale[i].quantity--
                    this.sale[i].subtotal = this.sale[i].quantity * this.sale[i].price
                }
            }
            if (type == 'plus') {
                this.sale[i].quantity++
                this.sale[i].subtotal = this.sale[i].quantity * this.sale[i].price
            }
        },
        removeProduct: function (i) {
            this.sale.splice(i, 1)
        },
        cartProduct: function (p) {
            let product = {
                name: p.name,
                type: p.product_type,
                product_id: p.id,
                quantity: parseFloat(1).toFixed(2),
                price: parseFloat(p.selling_price).toFixed(2),
                subtotal: parseFloat(p.selling_price).toFixed(2),
            }
            let isExist = this.sale.map(v => v.product_id).indexOf(product.product_id);
            if (isExist > -1) {
                this.updateProduct('plus', isExist)
            } else {
                this.sale.push(product)
            }
        },
        getProducts: function (id = null, index = null) {
            this.selectedProductIndex = index
            let param = {
                limit: 5000,
                page: 1
            }
            if (id != null) {
                param.type_id = id;
            }
            ApiService.POST(ApiRoutes.ProductList, param, res => {
                if (parseInt(res.status) === 200) {
                    this.products = res.data.data;
                    document.addEventListener("keydown", (event) => {
                        if (event.altKey) {
                            event.stopPropagation();
                            event.preventDefault();
                        }
                        if (event.altKey && event.key == 'a') {
                            let product = this.products[0]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 's') {
                            let product = this.products[1]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 'd') {
                            let product = this.products[2]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 'f') {
                            let product = this.products[3]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 'g') {
                            let product = this.products[4]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 'h') {
                            let product = this.products[5]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 'j') {
                            let product = this.products[6]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 'k') {
                            let product = this.products[7]
                            this.cartProduct(product)
                        }
                        if (event.altKey && event.key == 'l') {
                            let product = this.products[8]
                            this.cartProduct(product)
                        }
                    });
                }
            });
        },
        getProductNumber: function (index) {
            let alphabet = ''
            switch (index) {
                case 0:
                    alphabet = 'A'
                    break;
                case 1:
                    alphabet = 'S'
                    break;
                case 2:
                    alphabet = 'D'
                    break;
                case 3:
                    alphabet = 'F'
                    break;
                case 4:
                    alphabet = 'G'
                    break;
                case 5:
                    alphabet = 'H'
                    break;
                case 6:
                    alphabet = 'J'
                    break;
                case 7:
                    alphabet = 'K'
                    break;
                case 8:
                    alphabet = 'L'
                    break;
                default:
                // do nothing
            }
            return alphabet
        },
        getProductType: function () {
            ApiService.POST(ApiRoutes.ProductType, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.productType = res.data
                }
            });
        }
    },
    created() {
        this.getProducts()
        this.getProductType()
    },
    mounted() {
        this.printD = new Printd()
        $('#dashboard_bar').text('Pos')
    }
}
</script>

<style lang="scss" scoped>
@media print {
    #print{
        display: block;
    }
}
#print{
    display: none;
}
.product-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    .each-product {
        cursor: pointer;
        border: 1px solid #f2f2f2;
        background-color: #ffffff;
        margin-right: 15px;
        border-radius: 10px;
        box-shadow: 0rem 0.3125rem 0.3125rem 0rem rgba(82, 63, 105, 0.05);
        width: 180px;
        transition: 500ms;
        margin-bottom: 15px;

        &:hover {
            border: 1px solid #6572FF;
            transition: 500ms;
        }

        .img {
            width: 100%;
            height: 170px;

            img {
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }
        }

        .detail {
            padding: 10px;

            .name {
                font-weight: bold;
            }

            .desc {
                font-size: 13px;
                color: #808080;
            }
        }
    }
}

.blank-white {
    width: 100%;
    border-radius: 15px;
    padding: 27px 10px;
    background-color: #ffffff;
    box-shadow: 0rem 0.3125rem 0.3125rem 0rem rgba(82, 63, 105, 0.05);
}

.t-section {
    height: 530px;
    overflow: auto;
}

.default-cart {
    margin-bottom: 1.875rem;
    background-color: #fff;
    transition: all 0.5s ease-in-out;
    position: relative;
    border: 0rem solid transparent;
    border-radius: 1.25rem;
    box-shadow: 0rem 0.3125rem 0.3125rem 0rem rgba(82, 63, 105, 0.05);
    /* height: calc(100% - 30px); */
    padding: 20px;
}

.btn-cart-plus {
    background-color: #D653C1;
    border-radius: 10px;
    padding: 8px 15px;
    color: #ffffff;
}

.width-fixed {
    width: 150px;
}

.active-btn {
    background-color: #6572FF;
    border-color: #6572FF;
    color: #ffffff;
}

.control-sm {
    padding: 8px 10px;
    width: 100px;
    margin-left: 10px;
    margin-right: 10px;
    height: 2.5rem;
}

</style>
