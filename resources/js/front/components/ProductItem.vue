<template>
    <div class="card position-relative" :class="classItem" :style="styleItem" style="overflow: hidden;">
        <div class="position-relative">
<!--            <div @click="redirectTo()"-->
<!--                style="width: 100%;-->
<!--                height: 176px;-->
<!--                overflow: hidden;-->
<!--                background-size:cover;-->
<!--                background-repeat: no-repeat;"-->
<!--                :style="{'background-image': `url(${itemData.image})`}"-->
<!--            >-->
<!--            </div>-->
            <img @click="redirectTo()"
                 class="lazyload"
                 :alt="itemData.name"
                 style="width: 100%;
                height: 176px;
                overflow: hidden;
                object-fit: cover;"
                 :data-src="itemData.image"
            >
            <i
                v-if="displayBtnLike"

                class="text-primary fa-heart position-absolute p-2 font-size-16px"
                :class="{'fa': itemData.isSaved, 'far': !itemData.isSaved}"
                @click="interestProduct"
                style="bottom: 0; right: 0; z-index:0"></i>
        </div>
        <div class="card-body px-2 pt-2 pb-0" @click="redirectTo()">
            <p :id="'product' + index" class="mb-1 text-dark-50" :title="itemData.name" v-shave="{'height': 60}">{{itemData.name}}</p>
            <p class="mb-1 text-success position-absolute" style="top: 0; left: 1rem" v-if="itemData.prioritize"><img src="/images/icon/push-post.svg" alt="Takamart"></p>
        </div>
        <div class="card-bottom px-2 py-0 mb-1">
            <p v-if="itemData.type === EProductType.PRODUCT_FOR_SALE"
               :title="itemData.price"
               class="mb-0 font-weight-bold font-siz-16px">
                {{itemData.price}}
            </p>
            <p v-else
               title="Liên hệ"
               class="mb-0 font-weight-bold font-siz-16px">
                Liên hệ
            </p>
            <span class="mb-1 font-weight-bold posted-date-area">{{itemData.createdAt}} - </span>
            <span :title="displayLowestLevelArea ? itemData.area[0].name : itemData.area[itemData.area.length -1].name"
                  class="mb-1 font-weight-bold posted-date-area"
                  v-shave="{'height': 20}">
                {{displayLowestLevelArea ? itemData.area[0].name : itemData.area[itemData.area.length -1].name }}
            </span>
        </div>
    </div>
</template>

<script>
    import EErrorCode from "../../constants/error-code";
    import EProductType from "../../constants/product-type";

    export default {
        props: {
            itemData: {
                default: null,
            },
            index: {
                default: 0,
            },
            classItem: {
                default: null
            },
            displayBtnLike: {
                default: false,
            },
            displayLowestLevelArea : {
                default: false,
            },
            styleItem: {
                default: null
            }
        },
        data() {
            return {
                // name: this.subString(this.itemData.name, 40),
                EProductType,
            }
        },
        watch: {
            styleItem(val) {
                console.log(val);
            }
        },
        computed: {
            route() {
                return {
                    interest: '/product/interest',
                    unInterest: '/product/un-interest',
                }
            },
        },
        filters: {
            subString(string, number) {
                if (string.length > number) {
                    string = window.stringUtil.shortenText(string, number) + '...';
                }
                return string;
            },
        },
        methods: {
            redirectTo() {
                window.location.assign(this.itemData.redirectTo);
            },

            interestProduct() {
                common.post({
                    url: this.itemData.isSaved ? this.route.unInterest : this.route.interest,
                    data: {
                        code: this.itemData.code
                    }
                }).done(response => {
                    if(response.error === EErrorCode.UNAUTHORIZED){
                        window.location.assign(response.redirectTo);
                    }
                })
                this.$set(this.itemData,'isSaved',!this.itemData.isSaved);
            }
        }
    }
</script>

<style scoped>

</style>
