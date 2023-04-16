<template>
    <div class="content-body">
        <form @submit.prevent="addTransaction" id="transaction_form"></form>

        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <router-link :to="{name: 'Accounts'}"><i class="fa-solid fa-left-long fa-3x"></i></router-link>
                <div class="categoryName">{{singleCategory.category}}</div>
                <button class="btn btn-success" v-if="!loading" type="button" @click="saveTransaction">Save</button>
                <button class="btn btn-success" v-if="loading" type="button">Saving...</button>
            </div>
            <div class="table-height d-flex flex-column-reverse" id="table-scroll">
                <table class="table table-sm table-transaction table-responsive">
                    <thead>
                    <tr>
                        <th style="width: 10%" class="text-start">Date</th>
                        <th style="width: 30%" class="text-start">Description</th>
                        <th style="width: 20%" class="text-start">Transfer</th>
                        <th style="width: 10%">{{getNameDr()}}</th>
                        <th style="width: 10%">{{getNameCr()}}</th>
                        <th style="width: 15%">Balance</th>
                        <th style="width: 5%">Action</th>
                    </tr>
                    </thead>
                    <tbody v-if="!getLoading">
                    <tr v-for="transaction of transactionParam.transaction">
                        <td class="text-start">{{ formatDate(transaction.date) }}</td>
                        <td class="text-start">{{ transaction.description }}</td>
                        <td class="text-start">{{ categoryName(transaction.account_id) }}</td>
                        <td>{{ formatPrice(transaction.debit_amount) }}</td>
                        <td>{{ formatPrice(transaction.credit_amount) }}</td>
                        <td>
                            <span v-if="transaction.balance < 0" class="text-danger">({{ formatPrice(Math.abs(transaction.balance)) }})</span>
                            <span v-else>{{ formatPrice(transaction.balance) }}</span>
                        </td>
                        <td>
                            <i class="fa fa-edit me-2 cursor-pointer"></i>
                            <i class="fa fa-trash cursor-pointer"></i>
                        </td>
                    </tr>
                    </tbody>
                    <tbody v-if="getLoading">
                    <tr>
                        <td colspan="20">
                            <div class="d-flex align-items-center justify-content-center" style="height: 300px"><i class="fas fa-spinner fa-5x fa-spin"></i></div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <table class="table table-sm table-transaction table-responsive">
                <tbody>
                <tr class="input-box">
                    <td style="width: 10%" class="text-start">
                        <input type="text" class="date bg-transparent-input" placeholder="Date" v-model="param.date"
                               form="transaction_form">
                    </td>
                    <td style="width: 30%" class="text-start">
                        <input type="text" placeholder="Description" v-model="param.description"
                               form="transaction_form">
                    </td>
                    <td style="width: 20%">
                        <select name="parent_category" v-model=param.account_id form="transaction_form">
                            <option v-for="pCat in parentCategory" :value="pCat.id">{{ pCat.category }}</option>
                        </select>
                    </td>
                    <td style="width: 10%">
                        <input type="number" class="text-end" :placeholder="getNameDr()" v-model="param.debit_amount"
                               form="transaction_form">
                    </td>
                    <td style="width: 10%">
                        <input type="number" class="text-end" :placeholder="getNameCr()" v-model="param.credit_amount"
                               form="transaction_form">
                    </td>
                    <td style="width: 15%">
                        <i class="p-1">Balance</i>
                    </td>
                    <td style="width: 5%">
                        <button type="submit" class="btn btn-primary btn-sm" form="transaction_form">Add</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data: function () {
        return {
            parentCategory: [],
            transactionParam: {
                transaction: [],
                linked_id: ''
            },
            param: {
                date: '',
                description: '',
                account_id: '',
                credit_amount: '',
                debit_amount: '',
                balance: 0,

            },
            parent_category_id: '',
            singleCategory: {},
            loading: false,
            getLoading: false
        }
    },
    methods: {
        getNameDr: function () {
            if (this.singleCategory.type == 'assets') {
                return 'Increase'
            }
            if (this.singleCategory.type == 'equity') {
                return 'Decrease'
            }
            if (this.singleCategory.type == 'expenses') {
                return 'Expense'
            }
            if (this.singleCategory.type == 'income') {
                return 'Charge'
            }
            if (this.singleCategory.type == 'liabilities') {
                return 'Decrease'
            }
        },
        getNameCr: function () {
            if (this.singleCategory.type == 'assets') {
                return 'Decrease'
            }
            if (this.singleCategory.type == 'equity') {
                return 'Increase'
            }
            if (this.singleCategory.type == 'expenses') {
                return 'Rebate'
            }
            if (this.singleCategory.type == 'income') {
                return 'Income'
            }
            if (this.singleCategory.type == 'liabilities') {
                return 'Increase'
            }
        },
        saveTransaction: function () {
            this.loading = true
            let transaction = {
                transaction: [],
                linked_id: this.parent_category_id
            }
            this.transactionParam.transaction.map(v => {
                if (v.id == undefined) {
                    transaction.transaction.push(v)
                }
            })
            ApiService.POST(ApiRoutes.TransactionSave, transaction, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.message);
                }
            });
        },
        singleTransaction: function () {
            this.getLoading = true
            ApiService.POST(ApiRoutes.TransactionSingle, {id: this.parent_category_id}, res => {
                this.getLoading = false
                if (parseInt(res.status) === 200) {
                    this.transactionParam.transaction = res.data
                }
            });
        },
        categoryName: function (id) {
            let rv = ''
            this.parentCategory.map(v => {
                if (v.id == id) {
                    rv = v.category
                }
            })
            return rv
        },
        formatDate: function (date) {
            let dateArr = date.split('-')
            return dateArr[2] + '/' + dateArr[1] + '/' + dateArr[0]
        },
        addTransaction: function () {
            if (this.param.account_id != '' && (this.param.credit_amount != '' || this.param.debit_amount != '')) {
                if (this.param.credit_amount != '' && this.param.debit_amount != '') {
                    if (Number(this.param.debit_amount) > Number(this.param.credit_amount)) {
                        this.param.debit_amount = this.param.debit_amount - this.param.credit_amount
                        this.param.credit_amount = ''
                    } else {
                        this.param.credit_amount = this.param.credit_amount - this.param.debit_amount
                        this.param.debit_amount = ''
                    }
                }
                if (this.param.date == '') {
                    this.param.date = moment().format('YYYY-MM-DD')
                }
                this.param.balance = this.param.debit_amount - this.param.credit_amount
                this.transactionParam.transaction.push(this.param)
                this.calculateBalance()
                this.param = {
                    date: '',
                    description: '',
                    account_id: '',
                    credit_amount: '',
                    debit_amount: '',
                    balance: '',
                }
                let objDiv = document.getElementById("table-scroll");
                objDiv.scrollTop = objDiv.scrollHeight;
            } else {
                this.$toast.error('Please insert all input field');
            }

        },
        calculateBalance: function () {
            this.transactionParam.transaction.map((v, i) => {
                if (i == 0) {
                    v.balance = v.debit_amount - v.credit_amount
                } else {
                    v.balance = this.transactionParam.transaction[i - 1].balance + (v.debit_amount - v.credit_amount)
                }
            })
        },
        getParentCategory: function () {
            ApiService.POST(ApiRoutes.CategoryParent, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.parentCategory = res.data;
                }
            });
        },
        getCategorySingle: function () {
            ApiService.POST(ApiRoutes.CategorySingle, {id: this.parent_category_id}, res => {
                if (parseInt(res.status) === 200) {
                    this.singleCategory = res.data;
                }
            });
        },
    },
    created() {
        this.getParentCategory()
        this.parent_category_id = this.$route.params.id;
        this.transactionParam.linked_id = this.parent_category_id;
        this.getCategorySingle()
        this.singleTransaction()
    },
    mounted() {
        $('#dashboard_bar').text('Transaction')
        setTimeout(() => {
            $('.date').flatpickr({
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                defaultDate: 'today',
                onChange: (dateStr) => {
                    this.param.date = dateStr
                }
            })
        }, 500)
    }
}
</script>

<style scoped lang="scss">
.form-control:focus {
    border-color: transparent !important;
}

/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
input[type=number] {
    -moz-appearance: textfield;
}

.bg-transparent-input {
    background: transparent !important;
    height: 2.5rem !important;

    &:focus {
        border-color: transparent;
        border: none !important;
    }
}
.table-height{
    max-height: 65vh;
    overflow: auto;
}
.table-transaction {
    margin-bottom: 0;
    thead {
        tr {
            th {
                background-color: #96B183;
                color: #ffffff;
                text-align: right;
            }
        }
    }
    &.table-scroll {
        tbody{
            display:block;
            overflow:auto;
            height:200px;
            width:100%;
        }
    }

    tbody {
        tr {
            td {
                text-align: right;
            }

            &.input-box {
                background-color: #fff69f !important;

                td {
                    padding: 0;
                    color: #000;
                    border: 1px solid #d6d6d6;
                    border-bottom: none;
                }

                input {
                    width: 100% !important;
                    border: none !important;
                    border-radius: 0;
                    padding: 0 10px !important;
                    color: #000 !important;
                    background-color: #fff69f !important;
                    background: #fff69f !important;
                    outline: none !important;
                }

                .form-control[readonly] {
                    width: 100% !important;
                    border: none !important;
                    padding: 0 10px !important;
                    color: #000 !important;
                    background-color: #fff69f !important;
                    border-radius: 0;
                    background: #fff69f !important;
                    outline: none !important;

                    &:focus {
                        border-color: transparent;
                        border: none !important;
                    }
                }

                .form-control {
                    width: 100% !important;
                    border: none !important;
                    padding: 0 10px !important;
                    color: #000 !important;
                    background-color: #fff69f !important;
                    border-radius: 0;
                    background: #fff69f !important;
                    outline: none !important;

                    &:focus {
                        border-color: transparent;
                        border: none !important;
                    }
                }

                select {
                    width: 100% !important;
                    border: none !important;
                    padding: 0 10px !important;
                    color: #000 !important;
                    background-color: #fff69f !important;
                    outline: none !important;
                }
            }
        }

        tr:nth-child(odd) {
            background-color: #BFDEB9;
        }

        tr:nth-child(even) {
            background-color: #F6FFDA;
        }
    }
}

</style>
