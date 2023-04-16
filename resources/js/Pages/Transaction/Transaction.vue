<template>
    <div class="content-body">
        <form @submit.prevent="addTransaction" id="transaction_form"></form>

        <div class="container-fluid">
            <div class="text-end mb-3">
                <button class="btn btn-success" v-if="!loading" type="button" @click="saveTransaction">Save</button>
                <button class="btn btn-success" v-if="loading" type="button">Saving...</button>
            </div>
            <table class="table table-sm table-transaction table-responsive">
                <thead>
                <tr>
                    <th class="text-start">Date</th>
                    <th class="text-start">Description</th>
                    <th class="text-start">Transfer</th>
                    <th>Increase</th>
                    <th>Decrease</th>
                    <th>Balance</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="transaction of transactionParam.transaction">
                    <td class="text-start">{{ formatDate(transaction.date) }}</td>
                    <td class="text-start">{{ transaction.description }}</td>
                    <td class="text-start">{{ categoryName(transaction.account_id) }}</td>
                    <td>{{ transaction.debit_amount }}</td>
                    <td>{{ transaction.credit_amount }}</td>
                    <td>{{ transaction.balance }}</td>
                    <td>
                        <i class="fa fa-edit me-2 cursor-pointer"></i>
                        <i class="fa fa-trash cursor-pointer"></i>
                    </td>
                </tr>
                <tr class="input-box">
                    <td class="text-start">
                        <input type="text" class="date bg-transparent-input" placeholder="Date" v-model="param.date"
                               form="transaction_form">
                    </td>
                    <td class="text-start">
                        <input type="text" placeholder="Description" v-model="param.description"
                               form="transaction_form">
                    </td>
                    <td>
                        <select name="parent_category" v-model=param.account_id form="transaction_form">
                            <option v-for="pCat in parentCategory" :value="pCat.id">{{ pCat.category }}</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" class="text-end" placeholder="Increase" v-model="param.debit_amount"
                               form="transaction_form">
                    </td>
                    <td>
                        <input type="number" class="text-end" placeholder="Decrease" v-model="param.credit_amount"
                               form="transaction_form">
                    </td>
                    <td>
                        <i class="p-1">Balance</i>
                    </td>
                    <td>
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
            loading: false
        }
    },
    methods: {
        saveTransaction: function () {
            this.loading = true
            ApiService.POST(ApiRoutes.TransactionSave, this.transactionParam, res => {
                this.loading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.msg);
                    this.singleTransaction()
                }
            });
        },
        singleTransaction: function () {
            ApiService.POST(ApiRoutes.TransactionSingle, {id: this.parent_category_id}, res => {
                if (parseInt(res.status) === 200) {

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

.table-transaction {
    thead {
        tr {
            th {
                background-color: #96B183;
                color: #ffffff;
                text-align: right;
            }
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
