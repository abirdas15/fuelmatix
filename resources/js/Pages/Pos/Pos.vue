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
                                        <tbody>
                                        <tr>
                                            <td>
                                                <div class="fw-bold">T-shirt</div>
                                                <div>
                                                    <span class="badge badge-primary">Primary</span>
                                                    <i class="fa-solid fa-pen"></i>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="btn-cart-plus">-</div>
                                                    <div>2</div>
                                                    <div class="btn-cart-plus">+</div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $ 20.00
                                            </td>
                                            <td class="text-end">
                                                $ 40.00
                                            </td>
                                            <td class="text-end">
                                                <i class="fa-regular text-danger fa-trash-can"></i>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="btn-section text-center mt-3">
                                    <button class="btn btn-warning me-2 width-fixed">Hold <i class="fa-regular fa-hand"></i></button>
                                    <button class="btn btn-danger me-2 width-fixed">Reset <i class="fa-solid fa-arrow-rotate-left"></i></button>
                                    <button class="btn btn-success width-fixed">Paynow <i class="fa-solid fa-money-bill-1"></i></button>
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
                                    <button class="btn btn-sm active-btn me-2">All Categories</button>
                                    <button class="btn btn-sm light btn-dark me-2" v-for="type in productType" @click="getProducts(type.id)">{{ type.name }}</button>
                                </div>
                                <div class="product-list">
                                    <div class="each-product" v-for="(p, i) in products">
                                        <div class="img">
                                            <img :src="'https://via.placeholder.com/100x70?text='+p.name" alt="">
                                        </div>
                                        <div class="detail">
                                            <div class="name">{{p.name}}</div>
                                            <div class="desc">{{ p.product_type }}</div>
                                            <p class="mt-1 mb-0"><kbd>Ctrl</kbd>+<kbd>{{getProductNumber(i)}}</kbd></p>
                                        </div>
                                    </div>
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
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data() {
        return {
            products: [],
            productType: []
        }
    },
    methods: {
        getProducts: function (id = null) {
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
                    document.addEventListener("keydown", function (event) {
                        event.stopPropagation();
                        event.preventDefault();
                        if(event.ctrlKey && event.keyCode == 81) {
                            console.log("CTRL + Q was pressed!");
                        }
                        if(event.ctrlKey && event.keyCode == 82) {
                            console.log("CTRL + W was pressed!");
                        }
                    });
                }
            });
        },
        getProductNumber: function (index) {
            let alphabet = ''
            switch(index) {
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
                    alphabet = 'I'
                    break;
                case 7:
                    alphabet = 'J'
                    break;
                case 8:
                    alphabet = 'K'
                    break;
                case 9:
                    alphabet = 'L'
                    break;
                default:
                // do nothing
            }
            return alphabet
        },
        getProductType: function () {
            ApiService.POST(ApiRoutes.ProductType, {},res => {
                if (parseInt(res.status) === 200) {
                    this.productType = res.data
                }
            });
        }
    },
    created() {
        this.getProducts()
    },
    mounted() {
        $('#dashboard_bar').text('Pos')
    }
}
</script>

<style lang="scss" scoped>
.product-list{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    .each-product{
        border: 1px solid #f2f2f2;
        background-color: #ffffff;
        margin-right: 15px;
        border-radius: 10px;
        box-shadow: 0rem 0.3125rem 0.3125rem 0rem rgba(82, 63, 105, 0.05);
        width: 180px;
        transition: 500ms;
        margin-bottom: 15px;
        &:hover{
            border: 1px solid #6572FF;
            transition: 500ms;
        }
        .img{
            width: 100%;
            height: 170px;
            img{
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }
        }
        .detail{
            padding: 10px;
            .name{
                font-weight: bold;
            }
            .desc{
                font-size: 13px;
                color: #808080;
            }
        }
    }
}
.blank-white{
    width: 100%;
    border-radius: 15px;
    padding: 27px 10px;
    background-color: #ffffff;
    box-shadow: 0rem 0.3125rem 0.3125rem 0rem rgba(82, 63, 105, 0.05);
}
.t-section{
    height: 530px;
    overflow: auto;
}
.default-cart{
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
.btn-cart-plus{
    background-color: #D653C1;
    border-radius: 10px;
    padding: 8px 15px;
    color: #ffffff;
}
.width-fixed{
    width: 150px;
}
.active-btn{
    background-color: #6572FF;
    border-color: #6572FF;
    color: #ffffff;
}
</style>
