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
            parentCategory: []
        }
    },
    methods: {
        getAccountsHead: function () {
            ApiService.POST(ApiRoutes.CategoryList, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.categories = res.data;
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
    created() {
        this.getAccountsHead()
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
    width: 1000px;
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
    left: 865px;
}

/* main content end here  */
</style>
