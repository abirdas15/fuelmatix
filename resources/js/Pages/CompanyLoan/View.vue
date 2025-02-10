<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Staff Loan View</a></li>
                </ol>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h4 class="card-title">Staff Loan View</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mt-4">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Payment Method</th>
                                                <th class="text-end">Loan Amount</th>
                                                <th class="text-end">Paid Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="each in loans">
                                                <td v-text="each.date"></td>
                                                <td v-text="each.payment_method"></td>
                                                <td class="text-end" v-text="each.debit_amount"></td>
                                                <td class="text-end" v-text="each.credit_amount"></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th class="text-end" v-text="total.debit_amount"></th>
                                                <th class="text-end" v-text="total.credit_amount"></th>
                                            </tr>
                                            <tr>
                                                <th colspan="3">Due</th>
                                                <th class="text-end" v-text="total.due_amount"></th>
                                            </tr>
                                        </tfoot>
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
import Swal from 'sweetalert2/dist/sweetalert2.js'
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import Pagination from "../../Helpers/Pagination";
import Table from "../../admin/Pages/Common/Table.vue";
export default {
    components: {
        Table,
        Pagination,
    },
    data() {
        return {
            loans: [],
            total: {}
        };
    },
    watch: {

    },
    created() {
        this.fetchStaffLoanDetails();
    },
    computed: {
        Auth: function () {
            return this.$store.getters.GetAuth;
        },
    },
    methods: {
        fetchStaffLoanDetails() {
            ApiService.POST(ApiRoutes.StaffLoan + '/single', {id: this.$route.params.id}, (res) => {
                if (parseInt(res.status) === 200) {
                    this.loans = res.data;
                    this.total = res.total;
                }
            });
        }
    },
    mounted() {
        $('#dashboard_bar').text('Staff Loan')
    }
}
</script>

<style scoped>

</style>
