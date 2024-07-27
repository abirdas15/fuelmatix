<template>
    <div class="content">
        <div class="line"></div>
        <div class="line line-1"></div>
        <div class="line line-2"></div>
        <div class="line line-3"></div>
        <div class="line line-4"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
        <div class="line line-5"></div>
    </div>
</template>

<script>
    export default {
    name: "Loader"
}
</script>

<style scoped lang="scss">
    .content{
    .line {
    position: relative;
    height: 13px;
    margin: 10px 0;
    border-radius: 10px;
    background: #d9d9d9;
    overflow: hidden;
    &::before{
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background-image: linear-gradient(
    to right,
    #d9d9d9 0%,
    #888b94 20%,
    #d9d9d9 40%,
    #d9d9d9 100%
    );
    background-size: 100% 100%;
    background-repeat: no-repeat;
    animation: shimmer 1.4s linear infinite;
}
    @for $i from 1 through 5 {
    &.line-#{$i} {
    width: calc(100% - #{$i});

}
}

}
}
    @keyframes shimmer {
    0% {
        background-position: -450px 0;
}
    100% {
    background-position: 1000px 0;
}
}

</style>
