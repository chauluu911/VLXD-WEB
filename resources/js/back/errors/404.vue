<template>
    <b-row>
        <b-col md="48">
            <div class="content__inner">
                <div class="flex-center position-ref full-height">
                    <div class="code">
                        404
                    </div>

                    <div class="message" style="padding: 10px;">
                        {{$t('404')}}
                    </div>
                </div>
            </div>
        </b-col>
    </b-row>
</template>

<script>
    import errorsMessage from "../../locales/back/errors";

    export default {
        i18n: {
            messages: errorsMessage
        },
        data() {
            return {
             
            }
        },
    }
</script>

<style scoped>
    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .code {
        border-right: 2px solid;
        font-size: 26px;
        padding: 0 15px 0 15px;
        text-align: center;
    }

    .message {
        font-size: 18px;
        text-align: center;
    }
</style>
