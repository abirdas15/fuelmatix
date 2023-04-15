<template>
    <div>
        <div class="popup-wrapper">
            <div class="popup">
                <ul style="padding: 0">
                    <li><a href="#">Open Account</a></li>
                    <li><a href="#">Edit Account</a></li>
                    <li><a href="javascript:void(0)" @click="openCategoryModal(node.id)">New account</a></li>
                    <li><a href="#">Delete account</a></li>
                </ul>
            </div>
        </div>
        <li>
            <a href="#" @contextmenu="rightClick($event)" class="accordion-btn">
                <span>
                    <img src="images/arrow-svg.svg" alt="" v-if="node.children.length > 0"/>
                    {{ node.category }}
                </span>
                <span> {{ node.description }}</span>
                <span>{{node.balance_format }}</span>
            </a>
            <ul class="accordion" v-if="node.children.length > 0">
                <TreeNode v-for="category in node.children" :key="category.id" :node="category" />
            </ul>
        </li>
        <div class="popup-wrapper-modal categoryModal d-none">
            <form @submit.prevent="" class="popup-box">
                <button type="button" class=" btn  closeBtn" @click="closeModal()"><i class="fas fa-times"></i></button>
                <div class="row">
                    <div class="col-sm-12">
                        <label >Account Name</label>
                        <input type="text" class="form-control sm-control bg-white" name="category" v-model="accountParam.category">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12">
                        <label >Account Code</label>
                        <input type="text" class="form-control sm-control bg-white" name="code" v-model="accountParam.code">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12">
                        <label >Account Description</label>
                        <textarea name="description" class="form-control sm-area bg-white" cols="30" rows="10" v-model="accountParam.description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12">
                        <label >Parent Account</label>
                        <select class="form-control sm-control " name="parent_category"  v-model="accountParam.parent_category">
                            <option v-for="category in parentCategory" :value="category.id">{{category.category}}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-12">
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
                <button type="submit" class="btn btn-primary " v-if="!infoLoading">Merge</button>
                <button type="button" class="btn btn-primary " disabled v-if="infoLoading">Merging...</button>
            </form>
        </div>
    </div>
</template>

<script>
import TreeNode from "./TreeNode";
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
export default {
    props: ['node', 'parentCategory'],
    name: "TreeNode",
    components: {TreeNode},
    data() {
        return {
            infoLoading: false,
            accountParam: {
                category: '',
                code: '',
                description: '',
                parent_category: '',
                type: '',
            },
        }
    },
    mounted() {
        const accordion = document.querySelector(".accordion");
        const accordionBtn = document.querySelectorAll(".accordion-btn");
        this.popup = document.querySelector(".popup-wrapper");
        const newAccount = document.getElementById('newAccount');
        const newAccForm = document.querySelector('.new-account-form-wrapper');
        const cancelBtn = document.querySelector('.cancel-btn');
        const form = document.querySelector('form');

        // accourdion show and hide
        accordionBtn.forEach((e) => {
            e.onclick = function (event) {
                event.preventDefault();
                e.nextElementSibling.classList.toggle("open");
                e.classList.toggle('active');
            }
        });

        // hide the popup when user clicks outside the popup box
        window.addEventListener('click',  (e) => {
            this.hidePopup(e);
            if (e.target == newAccForm && e.target !== form) {
                newAccForm.classList.remove('active')
            }
        })

        // hide the popup when user spin the mouse wheel
        window.addEventListener('wheel',  (e) =>  {
            this.hidePopup(e)
        })
    },
    methods: {
        openCategoryModal: function (parent_id) {
            console.log(parent_id)
            this.accountParam.parent_category = parent_id
            $(".categoryModal").removeClass('d-none');
        },
        rightClick: function (e) {
            e.preventDefault();
            this.showPopup(e);
        },
        showPopup: function (evt) {
            this.popup.style.left = `${evt.pageX}px`;
            this.popup.style.top = `${evt.pageY}px`;
            this.popup.classList.add('active')
        },
        hidePopup: function (e) {
            if (e.target !== this.popup) {
                if (this.popup.classList.contains('active')) {
                    this.popup.classList.remove('active')
                }
            }
        },
        closeModal: function () {
            $(".categoryModal").addClass('d-none');
        },

    },
    created() {

    }
}
</script>

<style scoped>

</style>
