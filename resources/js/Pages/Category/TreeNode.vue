<template>
    <div>
        <div class="popup-wrapper">
            <div class="popup">
                <ul style="padding: 0">
                    <li v-if="CheckPermission(Section.ACCOUNTING + '-' + Action.CREATE)"><a href="javascript:void(0)" @click="openCategory()">Open Account</a></li>
                    <li v-if="CheckPermission(Section.ACCOUNTING + '-' + Action.EDIT)"><a href="javascript:void(0)" @click="openCategoryModalEdit()">Edit Account</a></li>
                    <li v-if="CheckPermission(Section.ACCOUNTING + '-' + Action.CREATE)"><a href="javascript:void(0)" @click="openCategoryModal()">New account</a></li>
                    <li v-if="CheckPermission(Section.ACCOUNTING + '-' + Action.DELETE)" id="delete"><a href="javascript:void(0)">Delete account</a></li>
                </ul>
            </div>
        </div>
        <li>
            <a href="#" @dblclick="openTransaction(node)" @contextmenu="rightClick($event, node)" class="accordion-btn">
                <span>
                    <img src="images/arrow-svg.svg" alt="" v-if="node.children.length > 0"/>
                    {{ node.name }}
                </span>
                <span> {{ node.description }}</span>
                <span v-if="node.balance < 0" class="text-danger">({{formatPrice(Math.abs(node.balance))}})</span>
                <span v-else>{{formatPrice(node.balance)}}</span>
            </a>
            <ul class="accordion" v-if="node.children.length > 0">
                <TreeNode v-for="category in node.children" :key="category.id" :node="category" />
            </ul>
        </li>
    </div>
</template>

<script>
import TreeNode from "./TreeNode";
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
import Section from "../../Helpers/Section";
import Action from "../../Helpers/Action";
export default {
    computed: {
        Action() {
            return Action
        },
        Section() {
            return Section
        }
    },
    props: ['node', 'parentCategory'],
    name: "TreeNode",
    components: {TreeNode},
    data() {
        return {
            parent_id: '',
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
        openTransaction: function (category) {
            this.$router.push({
                name: 'Transaction',
                params: {id: category.id}
            })
        },
        openCategory: function () {
            this.$router.push({
                name: 'Transaction',
                params: {id: this.$store.getters.GetParentId}
            })
        },
        openCategoryModal: function () {
            this.$parent.openCategoryModal()
        },
        openCategoryModalEdit: function () {
            this.$parent.openCategoryEditModal()
        },
        rightClick: function (e, node) {
            e.preventDefault();
            this.$store.commit('PutParentCategory', node.id);
            if (node?.default != 1) {
                $('#delete').show()
            } else {
                $('#delete').hide()
            }
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

    },
    created() {

    }
}
</script>

<style scoped>

</style>
