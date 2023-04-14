<template>
    <div>
        <li>
            <a href="#" @contextmenu="rightClick($event)" class="accordion-btn">
                <span>
                    <img src="images/arrow-svg.svg" alt="" v-if="node.children.length > 0"/>
                    {{ node.name }}
                </span>
                <span> {{ node.name }}</span>
                <span>{{node.balance_format }}</span>
            </a>
            <ul class="accordion" v-if="node.children.length > 0">
                <TreeNode v-for="heads in node.children" :key="heads.id" :node="heads" />
            </ul>
        </li>
    </div>
</template>

<script>
import TreeNode from "./TreeNode";
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";
export default {
    props: ['node'],
    name: "TreeNode",
    components: {TreeNode},
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


        // hide pup function
        const hidePopup = (e) => {

        };

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


        newAccount.onclick = function (e) {
            hidePopup(e)
            if (!newAccForm.classList.contains('active')) {
                newAccForm.classList.add('active')
            }

        }

        cancelBtn.onclick = function () {
            newAccForm.classList.remove('active')
        }
    },
    methods: {
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
    },
}
</script>

<style scoped>

</style>
