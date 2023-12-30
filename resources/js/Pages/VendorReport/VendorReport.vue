<template>
    <div class="content-body">
        <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">Vendor Report</a></li>
            </ol>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card ">
                    <div class="card-header bg-secondary">
                        <h4 class="card-title">Vendor Report</h4>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-xl-3 mb-3">
                                <div class="example">
                                    <p class="mb-1">Select Date</p>
                                    <input class="form-control input-daterange-datepicker date" type="text">
                                </div>
                            </div>
                            <div class="col-xl-3 mb-3">
                                <div class="example">
                                    <p class="mb-1">Vendor</p>
                                    <select class="form-control" v-model="param.vendor_id">
                                        <option selected>Choose...</option>
                                        <option v-for="each in vendors" :value="each.id">{{ each.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-3 mb-3">
                                <button v-if="!loading" type="button" class="btn btn-rounded btn-white border" @click="fetchVendorAmount">
                                    <span class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i></span>Filter
                                </button>
                                <button v-if="loading" type="button" class="btn btn-rounded btn-white border">
                                    <span class="btn-icon-start text-info"><i class="fa fa-filter color-white"></i></span>Filter...
                                </button>
                            </div>
                        </div>
                        <div class=" mt-4">
                            <div class="table-responsive">
                                <table class="table table-striped table-responsive-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Product</th>
                                            <th>Billed</th>
                                            <th>Paid</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="vendorReport.length > 0">
                                            <tr v-for="each in vendorReport">
                                                <th v-text="each.date"></th>
                                                <td v-text="each.product"></td>
                                                <td><span class="badge badge-success" v-text="each.bill"></span></td>
                                                <td v-text="each.paid"></td>
                                                <td class="color-primary" v-text="each.balance"></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <th class="w-800"><h3>Total</h3></th>
                                                <th v-text="total.bill"></th>
                                                <th v-text="total.paid"></th>
                                                <th class="color-danger" v-text="total.balance"></th>
                                            </tr>
                                        </template>
                                        <tr v-if="vendorReport.length == 0">
                                            <td colspan="5" class="text-center">No data found</td>
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
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data() {
        return {
            param: {
                start_date: '',
                end_date: '',
                vendor_id: ''
            },
            vendors: [],
            vendorReport: [],
            total: {},
            loading: false
        }
    },
    mounted() {
        $('#dashboard_bar').text('Vendor Report')
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                mode: 'range',
                onChange: (date, dateStr) => {
                    let dateArr = dateStr.split('to')
                    if (dateArr.length == 2) {
                        this.param.start_date = dateArr[0]
                        this.param.end_date = dateArr[1]
                    }
                }
            })
        }, 1000);
        this.fetchVendor();
    },
    methods: {
        fetchVendorAmount: function() {
            this.loading = true;
            ApiService.POST(ApiRoutes.VendorReport, this.param, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 200) {
                    this.vendorReport = res.data;
                    this.total = res.total;
                }
            });
        },
        fetchVendor: function() {
            ApiService.POST(ApiRoutes.VendorList, {limit: 500}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.vendors = res.data.data;
                }
            });
        }
    }
}
</script>
<style scoped>

</style>
