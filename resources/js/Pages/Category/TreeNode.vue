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
            <a href="#" @contextmenu="rightClick($event, node.id)" class="accordion-btn">
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
            parent_id: ''
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
        openCategoryModal: function () {
            this.$parent.openCategoryModal()
        },
        rightClick: function (e, id) {
            e.preventDefault();
            this.$store.commit('PutParentCategory', id);
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
