<template>
    <div class="content-body">
        <div class="container-fluid">
            <ul class="accordion-wrapper">
                <li class="accordion-heading-wrapper">
                    <h4>Account name</h4>
                    <h4>Description</h4>
                    <h4>Total</h4>
                </li>
                <TreeNode v-for="category in categories" :key="category.id" :node="category" :parentCategory="parentCategory"/>
            </ul>
        </div>
        <div class="popup-wrapper-modal categoryModal d-none">
            <form @submit.prevent="saveCategory" class="popup-box">
                <button type="button" class=" btn  closeBtn" @click="closeModal()"><i class="fas fa-times"></i></button>
                <div class="row">
                    <div class="col-sm-12 form-group">
                        <label >Account Name</label>
                        <input type="text" class="form-control sm-control bg-white" name="category" v-model="accountParam.category">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Account Code</label>
                        <input type="text" class="form-control sm-control bg-white" name="code" v-model="accountParam.code">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Account Description</label>
                        <textarea name="description" class="form-control sm-area bg-white"  cols="10" rows="5" v-model="accountParam.description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Parent Account</label>
                        <select class="form-control sm-control " name="parent_category" v-model=accountParam.parent_category >
                            <option value="">New Top Level Account</option>
                            <option  v-for="pCat in parentCategory"  :value="pCat.id">{{pCat.category}}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Account Type</label>
                        <select class="form-control sm-control" name="parent_category"  v-model="accountParam.type">
                            <option value="assets">Assets</option>
                            <option value="equity">Equity</option>
                            <option value="liabilities">Liabilities</option>
                            <option value="income">Income</option>
                            <option value="expenses">Expenses</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary " v-if="!infoLoading">Save</button>
                <button type="button" class="btn btn-primary " disabled v-if="infoLoading">Saving...</button>
            </form>
        </div>
        <div class="popup-wrapper-modal categoryModalEdit d-none">
            <form @submit.prevent="editCategory" class="popup-box">
                <button type="button" class=" btn  closeBtn" @click="closeModal()"><i class="fas fa-times"></i></button>
                <div class="row">
                    <div class="col-sm-12 form-group">
                        <label >Account Name</label>
                        <input type="text" class="form-control sm-control bg-white" name="category" v-model="accountParamEdit.category">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Account Code</label>
                        <input type="text" class="form-control sm-control bg-white" name="code" v-model="accountParamEdit.code">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Account Description</label>
                        <textarea name="description" class="form-control sm-area bg-white" cols="10" rows="5" v-model="accountParamEdit.description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Parent Account</label>
                        <select class="form-control sm-control " name="parent_category" v-model=accountParamEdit.parent_category >
                            <option value="">New Top Level Account</option>
                            <option  v-for="pCat in parentCategory"  :value="pCat.id">{{pCat.category}}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label >Account Type</label>
                        <select class="form-control sm-control" name="parent_category"  v-model="accountParamEdit.type">
                            <option value="assets">Assets</option>
                            <option value="equity">Equity</option>
                            <option value="liabilities">Liabilities</option>
                            <option value="income">Income</option>
                            <option value="expenses">Expenses</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary " v-if="!infoLoading">Update</button>
                <button type="button" class="btn btn-primary " disabled v-if="infoLoading">Updating...</button>
            </form>
        </div>
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import TreeNode from "./TreeNode";

export default {
    components: {TreeNode},
    data() {
        return {
            popup: null,
            categories: [],
            parentCategory: [],
            accountParam: {
                category: '',
                code: '',
                description: '',
                parent_category: '',
                type: '',
            },
            accountParamEdit: {
                id: '',
                category: '',
                code: '',
                description: '',
                parent_category: '',
                type: '',
            },
            parent_id: '',
            infoLoading: false,
        }
    },
    watch: {
        'accountParam.parent_category': function () {
            this.parentCategory.map(v => {
                if (v.id == this.accountParam.parent_category) {
                    this.accountParam.type = v.type
                }
            })
        },
        'accountParamEdit.parent_category': function () {
            this.parentCategory.map(v => {
                if (v.id == this.accountParam.parent_category) {
                    this.accountParam.type = v.type
                }
            })
        }
    },
    methods: {
        openCategoryModal: function () {
            this.accountParam.parent_category = this.$store.getters.GetParentId
            $(".categoryModal").removeClass('d-none');
        },
        openCategoryEditModal: function () {
            this.accountParamEdit.parent_category = this.$store.getters.GetParentId
            this.getCategorySingle()
            $(".categoryModalEdit").removeClass('d-none');
        },
        closeModal: function () {
            $(".popup-wrapper-modal").addClass('d-none');
        },
        saveCategory: function () {
            this.infoLoading = true
            ApiService.POST(ApiRoutes.CategorySave, this.accountParam, res => {
                this.infoLoading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.msg);
                    this.closeModal()
                    this.getCategory()
                    this.getParentCategory()
                    this.accountParam = {
                        category: '',
                        code: '',
                        description: '',
                        parent_category: '',
                        type: '',
                    }
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        editCategory: function () {
            this.infoLoading = true
            ApiService.POST(ApiRoutes.CategoryUpdate, this.accountParamEdit, res => {
                this.infoLoading = false
                if (parseInt(res.status) === 200) {
                    this.$toast.success(res.msg);
                    this.closeModal()
                    this.getCategory()
                    this.getParentCategory()
                } else {
                    ApiService.ErrorHandler(res.errors);
                }
            });
        },
        getCategory: function () {
            ApiService.POST(ApiRoutes.CategoryList, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.categories = res.data;
                }
            });
        },
        getCategorySingle: function () {
            ApiService.POST(ApiRoutes.CategorySingle, {id: this.accountParamEdit.parent_category}, res => {
                if (parseInt(res.status) === 200) {
                    this.accountParamEdit = res.data;
                }
            });
        },
        getParentCategory: function () {
            ApiService.POST(ApiRoutes.CategoryParent, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.parentCategory = res.data;
                }
            });
        },
    },
    mounted() {
        $('#dashboard_bar').text('Category')
    },
    created() {
        this.getCategory()
        this.getParentCategory()
    },

    destroyed() {

    }
}
</script>

<style>

ul {
    list-style: none;
}

a {
    text-decoration: none;
    color: #000;
    font-weight: 400;
}


/* main content start here  */


/* popup area start  */
.popup-wrapper {
    display: block;
    position: absolute;
    background: #fff;
    box-shadow: 0px 0px 4px #00000047;
    z-index: 9;
    display: none;
}

.popup-wrapper.active {
    display: block;
}

.popup-wrapper ul li a {
    display: block;
    padding: 7px 25px 7px 15px;
    font-size: 14px;
    transition: all .3s;
}

.popup-wrapper ul li a:hover {
    color: #01987a;
}

/* popup area end */


/* new account area start  */
.new-account-form-wrapper {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9;
    background: #00000070;
    justify-content: center;
    align-items: center;
    display: none;
}

.new-account-form-wrapper.active {
    display: flex;
}

.new-account-form-wrapper form {
    padding: 30px 20px;
    row-gap: 10px;
    background: #fff;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
}

.new-account-form-wrapper form .input-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.new-account-form-wrapper form input,
textarea,
select {
    border: 1px solid #d2d2d2;
    outline: none;
    padding: 10px;
    width: 70%;
    border-radius: 4px;
}

.new-account-form-wrapper form label {
    margin-right: 10px;
}

.desc-label,
.notes-label {
    align-self: flex-start;
}

form .btn-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    column-gap: 10px;
}

.btn-wrapper button {
    padding: 10px;
    border: none;
    background-color: red;
    color: #fff;
    cursor: pointer;

}

.btn-wrapper button:first-child {
    background-color: rgb(0, 140, 255);
    color: #fff;
    border: none;
    outline: none;
}

/* new account area end  */


.accordion-wrapper .accordion-heading-wrapper {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px solid #d1d1d1;
    margin-bottom: 10px;
    padding: 5px;
}

.accordion-wrapper .accordion-heading-wrapper h4 {
    font-size: 18px;
    font-weight: 600;
    color: #a7a7a7;
}

.accordion-wrapper {

}

.accordion-wrapper li a {
    display: flex;
    justify-content: space-between;
    padding: 5px;
    text-decoration: none;
    color: #000;
    font-size: 18px;
}

.accordion-wrapper ul {
    padding-left: 50px;
}

.accordion-wrapper .accordion-btn img {
    width: 10px;
    margin-right: 10px;
    transition: .4s ease;
}

.accordion {
    display: none;
}

.accordion.open {
    display: block;
}

.accordion-btn.active img {
    transform: rotate(90deg);
}

ul.accordion-wrapper a span:nth-child(2),
ul.accordion a span:nth-child(2) {
    position: absolute;
    left: 66rem;
}

/* main content end here  */
</style>
