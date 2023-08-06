<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Invoices'}">Invoice</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">View</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Invoice</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 text-end">
                                <button class="btn btn-primary">Download Invoice</button>
                            </div>
                        </div>
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
                                        <div class="text-end mb-3">
                                            <h1 style="color: #418dff" class="mt-0 text-end">INVOICE</h1>
                                        </div>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th style="background-color: rgba(134,183,255,0.9)" class="text-center">Invoice</th>
                                                <th style="background-color: rgba(134,183,255,0.9)" class="text-center">Date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="text-center">#{{param.invoice_number}}</td>
                                                <td class="text-center">{{param.date}}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row mb-5">
                                    <div class="col-sm-12">
                                        <div class="bill-to mb-2">Bill To</div>
                                        <strong>{{param.customer_company.name}}</strong>
                                        <br>
                                        <strong>{{param.customer_company.address}}</strong>
                                        <br>
                                        <strong>{{param.customer_company.email}}</strong>
                                        <br>
                                        <strong>{{param.customer_company.phone}}</strong>
                                        <br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table table-bordered align-top    ">
                                            <thead>
                                            <tr>
                                                <th style="background-color: rgba(134,183,255,0.9)">Description</th>
                                                <th style="background-color: rgba(134,183,255,0.9)" class="text-end">Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td style="height: 400px; vertical-align: top">{{param.description}}</td>
                                                <td style="height: 400px; vertical-align: top" class="text-end">{{param.amount}}</td>
                                            </tr>
                                            <tr>
                                                <td ></td>
                                                <td class="text-end">Total: {{param.amount}}</td>
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
            id: '',
            listData: [],
            listDataTank: [],
        }
    },
    watch: {

    },
    methods: {
        getSingle: function () {
            ApiService.POST(ApiRoutes.invoiceSingle, {id: this.id},res => {
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
        $('#dashboard_bar').text('Invoice View')
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
