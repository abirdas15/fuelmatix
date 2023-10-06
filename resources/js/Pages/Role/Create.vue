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
                                    <div class="table-responsive">
                                        <table class="customTable">
                                            <thead>
                                            <tr>
                                                <th style="width: 20%;">Permission</th>
                                                <template v-for="action in permissions.actions">
                                                    <th style="width: 10%;">{{ action.name }}</th>
                                                </template>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr style="background-color: grey">
                                                <th>All</th>
                                                <template v-for="(action,index) in permissions.actions">
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" @click="switchAllToggle($event, action.value)" class="form-check-input">
                                                        </div>
                                                    </td>
                                                </template>
                                            </tr>
                                            <tr v-for="section in permissions.sections">
                                                <th>{{ section.name }}</th>
                                                <template v-for="(action,index) in permissions.actions">
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" @click="switchToggle($event, section.value, action.value)" class="form-check-input" :id="section.value + '-' + action.value">
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
            loading:false
        }
    },
    methods: {
        saveRole: function() {
            this.loading = true;
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
        switchAllToggle: function(event, actionValue) {
            this.permissions.sections.map((v) => {
                let name = v.value + '-' + actionValue;
                if (event.target.checked) {
                    $("#" + name).attr('checked', 'checked');
                    this.roleParam.permission.push(name);
                } else {
                    $("#" + name).removeAttr('checked');
                    this.roleParam.permission.splice(this.roleParam.permission.indexOf(name), 1);
                }
            });
        },
        switchToggle: function(event, sectionName, actionName) {
            let name = sectionName + '-' + actionName;
            if (event.target.checked) {
                $("#" + name).attr('checked', 'checked');
                this.roleParam.permission.push(name)
            } else {
                $("#" + name).removeAttr('checked');
                this.roleParam.permission.splice(this.roleParam.permission.indexOf(name), 1);
            }
        },
        fetchPermission: function() {
            ApiService.POST(ApiRoutes.PermissionList, this.param, (res) => {
                this.skeleton = false;
                if (parseInt(res.status) === 200) {
                    this.permissions = res.data;
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
</style>
