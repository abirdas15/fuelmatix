<template>
    <div class="content-body">
        <div class="container-fluid">
            <table class="table table-sm table-transaction table-responsive">
                <thead>
                <tr>
                    <th class="text-start">Date</th>
                    <th class="text-start">Description</th>
                    <th>Transfer</th>
                    <th>Increase</th>
                    <th>Decrease</th>
                    <th>Balance</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="transaction of transactionParam.transaction">
                    <td class="text-start">{{transaction.date}}</td>
                    <td class="text-start">{{ transaction.description }}</td>
                    <td>{{ transaction.debit_amount }}</td>
                    <td>{{ transaction.credit_amount }}</td>
                    <td>{{ balance() }}</td>
                    <td>
                        <i class="fa fa-edit me-2 cursor-pointer"></i>
                        <i class="fa fa-delete cursor-pointer"></i>
                    </td>
                </tr>
                <tr class="input-box">
                    <td class="text-start">
                        <input type="text" class="date" placeholder="Date" v-model="param.date">
                    </td>
                    <td class="text-start">
                        <input type="text" placeholder="Description" v-model="param.description">
                    </td>
                    <td>
                        <select v-model="param.account_id">

                        </select>
                    </td>
                    <td>
                        <input type="number" placeholder="Increase" v-model="param.debit_amount">
                    </td>
                    <td>
                        <input type="number" placeholder="Decrease" v-model="param.credit_amount">
                    </td>
                    <td>
                        <i>Balance</i>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-primary" form="transaction_form">Add</button>
                    </td>
                </tr>
                </tbody>
            </table>
            <form @submit.prevent="addTransaction" id="transaction_form"></form>
        </div>
    </div>
</template>

<script>
export default {
    data: function () {
        return {
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

            },
            parent_category_id:'',
            parent_category: ''
        }
    },
    methods: {
        addTransaction: function () {
            this.transactionParam.transaction.push(this.param)
        },
        balance: function () {
            return ''
        }
    },
    created() {
        this.parent_category_id = this.$route.params.id;
        this.parent_category = this.$route.params.name;
        this.transactionParam.linked_id = this.parent_category_id;
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
.table-transaction {
    thead{
        tr{
            th{
                background-color: #96B183;
                color: #ffffff;
                text-align: right;
            }
        }
    }
    tbody {
        tr{
            td{
                text-align: right;
            }
            &.input-box{
                background-color: #fff69f!important;
                td{
                    padding: 0;
                    color: #000;
                    border: 1px solid #d6d6d6;
                    border-bottom: none;
                }
                input{
                    width: 100%;
                    border: none;
                    padding: 10px;
                    color: #000;
                    background-color: #fff69f;
                    outline: none;
                }
                select{
                    width: 100%;
                    border: none;
                    padding: 10px;
                    color: #000;
                    background-color: #fff69f;
                    outline: none;
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
