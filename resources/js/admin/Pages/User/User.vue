<template>
    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Home</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">User</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card">
            <div class="card-body">
                <Table :table-data="table" :params="params"></Table>
            </div>
        </div>
    </main>
</template>
<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import Table from "../Common/Table.vue";

export default {
    components: {Table},
    data() {
        return {
            params: {
                limit: 20,
                page: 1,
                keyword: '',
                order_mode: 'DESC',
                order_by: 'id'
            },
            table: {
                loading: false,
                columns:[
                    {type: 'text', label: 'Name', key: 'name', width: '20%'},
                    {type: 'text', label: 'Company Name', key: 'company_name', width: '20%'},
                    {type: 'text', label: 'Role', key: 'role', width: '20%'},
                    {type: 'text', label: 'Email', key: 'email', width: '20%'},
                    {type: 'text', label: 'Phone Number', key: 'phone', width: '15%'},
                ],
                rows: [],
                paginateData: {},
                row_actions: [
                    {name: 'edit', type: 'action', icon: 'bi bi-pencil', color: 'btn btn-primary', title: 'Edit', permission: true},
                ],
                tableIconAction: (data) => {
                    this.tableIconAction(data.row_action, data.row_data, data.row_index);
                },
                updatePagination: (page) => {
                    this.fetchUser(page)
                },
                noDataError: ' No data found', // Error message when no data is available
                updateFilter: (param) => {
                    this.params = param;
                    this.fetchUser(); // Fetches groups based on updated parameters
                },
            },
        }
    },
    created() {
        this.fetchUser();
    },
    methods: {
        tableIconAction: function(action, data, index = null) {
            console.log(data);
            if (action === 'edit') {
                this.$router.push({
                    name: 'UserEdit',
                    params: {id: data.id}
                });
            } else if (action === 'delete') {

            }
        },
        fetchUser: function(page) {
            this.table.loading = true;
            if (page === undefined) {
                page = 1;
            }
            this.params.page = page;
            ApiService.POST(ApiRoutes.User + '/get',this.params, (res) => {
                this.table.loading = false;
                if (parseInt(res.status) === 200) {
                    this.table.rows = res.users.data;
                    this.table.paginateData = res.users;
                }
            });
        }
    }
}
</script>
<style scoped>

</style>
