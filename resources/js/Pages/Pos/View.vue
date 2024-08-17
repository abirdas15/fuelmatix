<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Pos'}">Pos</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">View</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Sale View</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <div class="container">
                                <div class="row mb-5">
                                    <div class="col-sm-6">
                                        <h2 class="mb-1">{{param.company.name}}</h2>
                                        <div>{{param.company.address}}</div>
                                        <div><strong>Email</strong>: {{param.company.email}}</div>
                                        <div><strong>Phone</strong>: {{param.company.phone_number}}</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th class="text-start">Invoice Number</th>
                                                <td class="text-start">{{param.invoice_number}}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-start">Customer Name</th>
                                                <td class="text-start">{{param.customer_name}}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-start">Payment Method</th>
                                                <td class="text-start">{{param.payment_method}}</td>
                                            </tr>
                                            <tr v-if="param.company_name != null">
                                                <th class="text-start">Company Name</th>
                                                <td class="text-start">{{param.company_name}}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-start">Voucher Number</th>
                                                <td class="text-start">{{param.voucher_number}}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-start">Car Number</th>
                                                <td class="text-start">{{param.car_number}}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-start">Date</th>
                                                <td class="text-start">{{param.date}}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table table-bordered align-top    ">
                                            <thead>
                                            <tr>
                                                <th style="background-color: rgba(134,183,255,0.9)">Product</th>
                                                <th style="background-color: rgba(134,183,255,0.9)" class="text-center">Quantity</th>
                                                <th style="background-color: rgba(134,183,255,0.9)" class="text-end">Unit Price</th>
                                                <th style="background-color: rgba(134,183,255,0.9)" class="text-end">Subtotal</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="item in param?.products">
                                                <td>{{item.product_name}}</td>
                                                <td class="text-center">{{item.quantity}}</td>
                                                <td class="text-end">{{item.price}}</td>
                                                <td class="text-end">{{item.subtotal}}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="3" class="text-end"><strong>Total</strong></th>
                                                <th class="text-end">{{param.total_amount}}</th>
                                            </tr>
                                            </tbody>
                                        </table>
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
            param: {},
            loading: false,
            download: false,
            id: '',
            listData: [],
            listDataTank: [],
        }
    },
    watch: {

    },
    methods: {
        getSingle: function () {
            ApiService.POST(ApiRoutes.SaleSingle, {id: this.id},res => {
                if (parseInt(res.status) === 200) {
                    this.param = res.data
                }
            });
        },
    },
    created() {
        this.id = this.$route.params.id
        this.getSingle()
    },
    mounted() {
        $('#dashboard_bar').text('Pos View')
    }
}
</script>

<style scoped>
.bill-to{
    background-color: rgba(134,183,255,0.9);
    font-weight: bold;
    padding: 10px 50px;
    width: max-content;
}
</style>
