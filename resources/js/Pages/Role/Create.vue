<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
                <ol class="breadcrumb align-items-center ">
                    <li class="breadcrumb-item active"><router-link :to="{name: 'Dashboard'}">Home</router-link></li>
                    <li class="breadcrumb-item active"><router-link :to="{name: 'user'}">Users</router-link></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Add</a></li>

                </ol>
            </div>
            <!-- row -->
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Role</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form @submit.prevent="saveRole">
                                <div class="row">
                                    <div class="mb-3 form-group col-md-4">
                                        <label class="form-label">Name:</label>
                                        <input type="text" class="form-control" name="name" v-model="roleParam.name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-8"></div>
                                    <div class="table-responsive table-container">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th class="w-20">Permission</th>
                                                <!-- Loop through actions -->
                                                <template v-for="action in actions">
                                                    <th class="w-10">{{ action.name }}</th>
                                                </template>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <!-- Row for 'All' permissions -->
                                            <tr>
                                                <th style="background-color: #dddddd">All</th>
                                                <!-- Loop through actions for 'All' checkboxes -->
                                                <template v-for="(action,index) in actions">
                                                    <td style="background-color: #dddddd">
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" v-model="action.checked" @click="switchAllToggle(index)" class="form-check-input">
                                                        </div>
                                                    </td>
                                                </template>
                                            </tr>
                                            <!-- Rows for each section -->
                                            <tr v-for="section in sections">
                                                <td>{{ section.name }}</td>
                                                <!-- Loop through actions for each section -->
                                                <template v-for="(action,index) in section.actions">
                                                    <td class="text-center">
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" @click="switchToggle()" class="form-check-input" v-model="action.checked">
                                                        </div>
                                                    </td>
                                                </template>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row" style="text-align: right;">
                                        <div class="mb-3 col-md-6">

                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <button type="submit" class="btn btn-primary" v-if="!loading">Submit</button>
                                            <button type="button" class="btn btn-primary" v-if="loading">Submitting...</button>
                                            <router-link :to="{name: 'role'}" type="button" class="btn btn-primary">Cancel</router-link>
                                        </div>
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
            permissions: [],
            roleParam: {
                name: '',
                permission: []
            },
            loading:false,
            actions: [],
            sections: []

        }
    },
    methods: {
        saveRole: function() {
            this.loading = true;
            this.sections.map((section) => {
                section.actions.map((action, index) => {
                    if (action['checked'] === true) {
                        this.roleParam.permission.push(section['value'] + '-' + action['value']);
                    }
                })
            });
            ApiService.POST(ApiRoutes.RoleSave, this.roleParam, (res) => {
                this.loading = false;
                if (parseInt(res.status) === 200) {
                    this.$toast.info(res.message);
                    this.$router.push({
                        name: 'role'
                    })
                } else if (parseInt(res.status) === 500) {
                    ApiService.ErrorHandler(res.errors);
                } else {
                    this.$toast.warning(res.message);
                }
            });
        },
        toggleAllCheckUncheck() {
            let actionArray = [];
            this.actions.map((action, index) => {
                actionArray[index] = 0;
            })
            let totalSection = this.sections.length ?? 0;
            this.actions.map((action, index) => {
                this.sections.map((section) => {
                    if (section['actions'][index]['checked'] === true) {
                        actionArray[index]++;
                    }
                });
                this.actions[index]['checked'] = actionArray[index] === totalSection;
            });
        },
        switchAllToggle: function(index) {
            setTimeout(() => {
                this.sections.map((v) => {
                    v.actions[index].checked = this.actions[index]['checked'];
                });
            }, 100)

        },
        switchToggle: function() {
            setTimeout(() => {
                this.toggleAllCheckUncheck();
            }, 200)
        },
        fetchPermission: function() {
            ApiService.POST(ApiRoutes.PermissionList, this.param, (res) => {
                this.skeleton = false;
                if (parseInt(res.status) === 200) {
                    this.permissions = res.data;
                    this.actions = res.data.actions;
                    this.sections = res.data.sections;
                }
            });
        }
    },
    created() {
        this.fetchPermission();
    },
    mounted() {
        $('#dashboard_bar').text('Role Add')
    }
}
</script>

<style lang="scss" scoped>
.customTable tr th, td {
    padding: 10px;
}
.table-responsive {
    max-height: 60vh;
    width: 50%;
}
.table-container {
    max-height: 400px; /* Set your desired table height */
    overflow-y: auto; /* Enable vertical scrolling */
    border: 1px solid #ccc; /* Optional: Add a border around the table */
}

.table thead th {
    position: sticky;
    top: 0; /* Fix to the top of the container */
    z-index: 2; /* Ensure it stays above table body content */
    background-color: #f8f9fa; /* Optional: Add background color to match the table style */
}
</style>
