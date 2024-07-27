<template>
    <div>
        <!-- Top Section with Download Buttons, Date Range Picker, and Search Input -->
        <div class="col-md-2">
            <div class="align-items-center">
                <div class="form-group mb-3">
                    <input type="text" class="form-control" placeholder="Search..." v-model="params.keyword">
                </div>
            </div>
        </div>

        <div class="long-scroll table-responsive">
            <!-- Table Section -->
            <table class="table table-bordered table-striped dataTable">
                <thead class="table-light">
                <tr>
                    <!-- Table Headers -->
                    <template v-for="column in tableData.columns">
                        <th @click="orderData(column.key)"
                            :class="[column.type === 'amount' ? 'text-end' : '', 'cursor-pointer']"
                            :style="{'width':column.width !== undefined ? column.width: '' }">
                            <div class="d-flex align-items-center" :class="column.type === 'amount' ? 'justify-content-end' : ''">
                                <!-- Column Label -->
                                <div v-text="column.label"></div>
                                <!-- Sort Icons -->
                                <div class="sort sort-buttons">
                                    <div class="inner-sort">
                                        <img :class="params.order_by === column.key && params.order_mode === 'ASC' ? 'sort-asc-blue' : 'sort-asc'" width="10" height="6" />
                                    </div>
                                    <div class="inner-sort">
                                        <img :class="params.order_by === column.key && params.order_mode === 'DESC' ? 'sort-desc-blue' : 'sort-desc'" width="10" height="6" />
                                    </div>
                                </div>
                            </div>
                        </th>
                    </template>
                    <!-- Action Column Header -->
                    <th class="text-right" v-if="tableData.row_actions.length > 0">Action</th>
                </tr>
                </thead>
                <tbody v-if="!tableData.loading && tableData.rows.length > 0">
                <!-- Table Rows -->
                <tr v-for="(dataRow, index) in tableData.rows">
                    <template v-for="column in tableData.columns">
                        <!-- Render Data Based on Column Type -->
                        <td v-if="column.type === 'text'">{{ dataRow[column['key']] }}</td>
                        <td v-else-if="column.type === 'image'">
                            <img class="image" :src="dataRow[column['key']]">
                        </td>
                        <td v-else-if="column.type === 'amount'" class="text-right">
                            {{ dataRow[column['key']] }}
                        </td>
                    </template>
                    <!-- Row Action Buttons -->
                    <td v-if="tableData.row_actions.length > 0" class="text-right">
                        <template v-for="action in tableData.row_actions">
                            <template v-if="action['type'] === 'action'">
                                <Button type="button" v-if="action.permission"  @click="onActionIconClick(action['name'], dataRow, $event, 'actionBtn'+action+index)" class="me-2 btn-sm" :class="action.color">
                                    <i :class="action.icon"></i>
                                </Button>
                            </template>
                        </template>
                    </td>
                </tr>
                </tbody>
                <tbody v-if="tableData.loading">
                <!-- Loading Indicator -->
                <tr>
                    <td colspan="100" class="loader-height">
                        <Loader></Loader>
                    </td>
                </tr>
                </tbody>
                <tbody v-if="!tableData.loading && tableData.rows.length <= 0">
                <!-- No Data Message -->
                <tr class="text-center">
                    <td colspan="100" class="fw-bold fs-6 text-height">
                        No Data Found
                        <br>
                        {{ tableData.noDataError }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination and Data Info Section -->
        <div class="row" v-if="tableData.paginateData != null">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <!-- Row Limit Dropdown -->
                    <div class="dataTables_length me-2" id="example_length">
                        <label class="table-filter fw-bold">Show
                            <select class="form-select form-select-sm ms-2 me-2" v-model="params.limit">
                                <option v-for="data in limitData" :value="data.value" v-text="data.label"></option>
                            </select>
                            entries
                        </label>
                    </div>
                    <!-- Pagination Info -->
                    <div class="dataTables_info" id="example3_info" role="status" aria-live="polite" v-if="tableData.paginateData != null">
                        Showing {{ tableData.paginateData.from }} to {{ tableData.paginateData.to }} of {{ tableData.paginateData.total }} entries
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Pagination Component -->
                <nav class="float-end mt-0" aria-label="Page navigation">
                    <Bootstrap5Pagination :data="tableData.paginateData" :limit="1" @pagination-change-page="tableData.updatePagination"></Bootstrap5Pagination>
                </nav>
            </div>
        </div>

    </div>

</template>

<script>

import Loader from "./Loader.vue";
import  Bootstrap5Pagination  from 'laravel-vue-pagination'
export default {
    props: ['tableData', 'params'], // Props received from parent components: tableData and params
    components: { Loader, Bootstrap5Pagination },
    data() {
        return {
            searchTimeout: null, // Timeout for delaying search input
            sortOrders: {}, // Object to track sorting order (not fully utilized in the provided code)
            limitData: [ // Options for number of items per page
                { label: 20, value: 20 },
                { label: 30, value: 30 },
                { label: 50, value: 50 },
                { label: 100, value: 100 },
            ]
        }
    },
    watch: {
        'params.keyword': function() {
            // Watcher for changes in search keyword
            this.searchTimeout = setTimeout(() => {
                this.tableData.updateFilter(this.params); // Call updateFilter method with params after delay
            }, 800); // Delay of 800 milliseconds to avoid frequent API calls on each keystroke
        },
        'params.limit': function() {
            // Watcher for changes in limit (items per page)
            this.tableData.updateFilter(this.params); // Call updateFilter method with params
        },
    },
    methods: {
        orderData: function(order_name) {
            // Method to handle sorting of data
            this.params.order_by = order_name; // Set order_by parameter to specified column name
            this.params.order_mode = this.params.order_mode === 'DESC' ? 'ASC' : 'DESC'; // Toggle order_mode between ASC and DESC
            this.tableData.updateFilter(this.params); // Call updateFilter method with updated params
        },
        onActionIconClick: function(rowAction, rowData, event, index) {
            // Method to handle click events on action icons in table rows
            event.stopPropagation(); // Stop propagation to prevent bubbling up to parent elements
            if (this.tableData.tableIconAction !== undefined) {
                this.tableData.tableIconAction({ row_action: rowAction, row_data: rowData, row_index: index }); // Call tableIconAction method with action details
            }
        },
    }
}

</script>
<style lang="scss">
::-webkit-scrollbar {
    width: 20px;
}

::-webkit-scrollbar-track {
    background-color: transparent;
}

::-webkit-scrollbar-thumb {
    background-color: #d6dee1;
    border-radius: 20px;
    border: 6px solid transparent;
    background-clip: content-box;
}

::-webkit-scrollbar-thumb:hover {
    background-color: #a8bbbf;
}

table {
    img.sort-asc {
        content: url(/svg/sort_asc.svg);
    }
    img.sort-desc {
        content: url(/svg/sort_desc.svg);
    }
    img.sort-asc-blue {
        content: url(/svg/sort_asc_blue.svg);
    }
    img.sort-desc-blue {
        content: url(/svg/sort_desc_blue.svg);
    }
    .inner-sort {
        border: none;
        background: none;
        width: 18px;
        height: 7px;
        display: flex;
        justify-content: center;
    }

}
.long-scroll {
    height: 65vh;
    overflow-x: auto;
}
.table-filter {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 170px;
}
</style>


