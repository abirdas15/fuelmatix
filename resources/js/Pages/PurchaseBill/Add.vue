<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'purchaseList'}">Purchase Bill</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Purchase Bill</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="save">

                                <div class="row">
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="date" value="Tank Name">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Vendor</label>
                                        <select id="inputState" class="default-select form-control wide">
                                            <option selected>vendor-1</option>
                                            <option>vendor-2</option>
                                            <option>vendor-3</option>
                                            <option>vendor-4</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Bill ID</label>
                                        <input type="text" class="form-control" value="Dispenser Brand" name="Bill ID">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Product</label>
                                        <input type="text" class="form-control" placeholder="Product">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Unit Price</label>
                                        <input type="text" class="form-control" placeholder="Unit Price">
                                    </div>

                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Quantity </label>
                                        <input type="text" class="form-control" placeholder="Quantity">
                                    </div>
                                    <div class="mb-3 col-md-2">
                                        <label class="form-label">Amount</label>
                                        <input type="text" class="form-control"  name="Amount" placeholder="Amount">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-3">

                                        <input type="text" class="form-control" placeholder="Product">
                                    </div>
                                    <div class="mb-3 col-md-3">

                                        <input type="text" class="form-control" placeholder="Unit Price">
                                    </div>

                                    <div class="mb-3 col-md-3">

                                        <input type="text" class="form-control" placeholder="Quantity">
                                    </div>
                                    <div class="mb-3 col-md-2">

                                        <input type="text" class="form-control" name="Amount" placeholder="Amount">

                                    </div>
                                    <div class="mb-3 col-md-1">
                                        <label class="form-label"> <br> </label>
                                        <button class="btn btn-primary" type="submit">+</button>
                                    </div>
                                </div>
                                <div class="row" style="text-align: right;" >
                                    <div class="toolbar toolbar-bottom " style="margin-top: 5px;" role="toolbar"><button
                                        class="btn btn-primary ms-2" type="submit">Submit</button>
                                        <router-link :to="{name: 'purchaseList'}" class="btn btn-primary ms-2" type="button">Cancel</router-link>
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
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
export default {
    data() {
        return {
            param: {
                name: '',
            },
            listParam: {
                limit: 5000,
                page: 1,
            },
            loading: false,
            listData: [],
        }
    },
    methods: {
        save: function () {
            ApiService.ClearErrorHandler();
            this.loading = true
            ApiService.POST(ApiRoutes.BankAdd, this.param,res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$router.push({
                        name: 'purchaseList'
                    })
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getVendor: function () {
            ApiService.POST(ApiRoutes.VendorList, this.Param,res => {
                this.TableLoading = false
                if (parseInt(res.status) === 200) {
                    this.listData = res.data.data;
                } else {
                    ApiService.ErrorHandler(res.error);
                }
            });
        },
    },
    created() {
        this.getVendor()
    },
    mounted() {
        $('#dashboard_bar').text('Purchase Bill')
    }
}
</script>

<style scoped>

</style>
