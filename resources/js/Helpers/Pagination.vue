<template>
    <div>
        <a class="paginate_button previous" :class="{'disabled' : data.prev_page_url == null}" @click="prevPage" :disabled="data.prev_page_url == null">
            <i class="fa fa-angle-double-left"></i>
        </a>
        <span>
        <a class="paginate_button" :class="{'big': btnBig}" v-if="start > button_divider"  @click="pageChange(1)">1</a>
        <a class="paginate_button" :class="{'big': btnBig}" v-if="start > button_divider">...</a>
        <a class="paginate_button" :class="{'current': page === current_page, 'big': btnBig}" v-for="page in total_pages"  v-if="page >= start && page <= end" @click="pageChange(page)">{{ page }}</a>
        <a class="paginate_button" :class="{'big': btnBig}" v-if="end < (total_pages-button_divider)">...</a>
        <a class="paginate_button" :class="{'big': btnBig}" @click="pageChange(total_pages)" v-if="end < (total_pages-button_divider)">{{total_pages}}</a>
    </span>
        <a class="paginate_button next"  :class="{'disabled' : data.prev_page_url == null}" @click="nextPage" :disabled="data.prev_page_url == null">
            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
        </a>
    </div>
</template>
<script>
export default {
    data() {
        return {
            buttons: [],
            current_page: 0,
            total_pages: 0,
            button_limit: 5,
            button_divider: 2,
            start: 0,
            end: 5,
            align: 'right',
            prev_btn: '<i class="fa-solid fa-chevron-left"></i>',
            next_btn: '<i class="fa-solid fa-angle-right"></i>',
        }
    },
    name: 'App',
    props: ['data', 'onChange', 'btnBig'],
    components: {},
    methods: {
        prevPage() {
            if (this.current_page > 1) {
                this.current_page = this.current_page - 1;
                this.onChange({page: this.current_page});
                this.refreshButton()
            }

        },
        nextPage() {
            if (this.current_page > 0 && this.current_page < this.total_pages) {
                this.current_page = this.current_page + 1;
                this.onChange({page: this.current_page});
                this.refreshButton()
            }
        },
        pageChange(page) {
            this.current_page = page;
            this.onChange({page: page});
            this.refreshButton()
        },
        refreshButton() {
            this.start = this.current_page - this.button_divider;
            this.start = this.start > 0 ? this.start : 0;
            this.start = (this.total_pages - this.start) < this.button_limit ? (this.total_pages - this.button_limit) + 1 : this.start;
            this.end = this.current_page + this.button_divider;
            this.end = this.end < this.button_limit ? this.button_limit : this.end;
        },
        initButtons() {
            this.total_pages = this.data.total < this.data.per_page ? 1 : Math.ceil((this.data.total / this.data.per_page))
            this.button_divider = Math.floor(this.button_limit / 2);
            this.current_page = this.data.current_page;
            this.refreshButton();
        }
    },
    watch: {
        data: function (newVal) {
            this.initButtons();
        }
    },
    created() {
        //this.initButtons()
    }
}
</script>
