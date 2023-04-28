<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="text-end">
                <!--                <button class="btn btn-primary" @click="print">Print</button>-->
            </div>
            <div id="print_area">
                <div class="text-center mb-3">
                    <!--                    <h2>Trial Balance</h2>-->
                    <input type="text" class="date form-control m-auto w-25 mb-2" placeholder="Date">

                    <select class="form-control m-auto w-25" v-model="param.account_id" @change="getLedger()">
                        <option v-for="pCat in parentCategory" :value="pCat.id">{{ pCat.category }}</option>
                    </select>
                </div>

                <div class="w-50 balance-sheet" v-if="!loading && balance.length > 0">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th class="bg-secondary text-white border-0">Date</th>
                            <th class="bg-secondary text-white border-0">Description</th>
                            <th class="bg-secondary text-white text-end border-0">Debit</th>
                            <th class="bg-secondary text-white text-end border-0">Credit</th>
                            <th class="bg-secondary text-white text-end border-0">Running Balance</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="data in balance">
                            <td class="border-0">{{ data.date }}</td>
                            <td class="border-0">{{ data.description }}</td>
                            <td class="text-end border-0">{{ data.debit_amount }}</td>
                            <td class="text-end border-0">{{ data.credit_amount }}</td>
                            <td class="text-end border-0">{{ data.balance }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="w-50 balance-sheet text-center" v-if="loading">
                    <i class="fas fa-spinner fa-5x fa-spin"></i>
                </div>
                <div class="w-50 balance-sheet text-center p-5" v-if="!loading && balance.length === 0">
                    No Data Found
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data: function () {
        return {
            balance: [],
            parentCategory: {},
            param: {
                start_date: '',
                end_date: '',
                account_id: '',
            },
            loading: false
        }
    },
    watch: {

    },
    methods: {
        getLedger: function () {
            this.loading = true
            ApiService.POST(ApiRoutes.LedgerGet, this.param, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.balance = res.data;
                }
            });
        },
        getParentCategory: function () {
            ApiService.POST(ApiRoutes.CategoryParent, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.parentCategory = res.data;
                    this.param.account_id = this.parentCategory[0].id
                }
            });
        },
    },
    mounted() {
        $('#dashboard_bar').text('Ledger')
        this.loading = true
        this.param.start_date = new Date().getFullYear() + '-01-01'
        this.param.end_date = new Date().getFullYear() + '-12-31'
        $('.date').val(this.param.start_date + ' to ' + this.param.end_date)
        this.getParentCategory()
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
                        this.getLedger()
                    }
                }
            })
            this.getLedger()
        }, 1000)
    }
}
</script>

<style scoped lang="scss">
.balance-sheet {
    background-color: #ffffff;
    margin: auto;
    border: 1px solid #d1cfcf;
}
</style>
