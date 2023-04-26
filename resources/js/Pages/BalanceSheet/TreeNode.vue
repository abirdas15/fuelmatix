<template>
    <div>
        <div class="d-flex align-items-center justify-content-between ">
            <div>{{ node.category }}</div>
            <div>
                <span v-if="node.balance < 0" class="text-danger">({{formatPrice(Math.abs(node.balance))}})</span>
                <span v-else>{{formatPrice(node.balance)}}</span>
            </div>
        </div>
        <div>
            <div class="ps-4 w-85">
                <TreeNode v-for="data in node.children" :key="data.id" :node="data" />
            </div>
        </div>
    </div>
</template>

<script>
import TreeNode from "./TreeNode";
export default {
    name: "TreeNode",
    props: ['node'],
    components: {TreeNode},
}
</script>

<style scoped>
.w-85{
    width: 85%;
}
</style>
