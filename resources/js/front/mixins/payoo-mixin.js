import PayooForm from "../../front/components/PayooForm";

export default {
    component: { PayooForm },
    data() {
        let viewData = $('#view-data').val();
        viewData = atob(viewData) || '{}';
        viewData = JSON.parse(viewData);
        return {
            payoo: {
                appUrl: viewData.appUrl,
                shopId: viewData.shopId,
                sellerName: viewData.sellerName,
                methodApi: viewData.payooMethodListApi,
                orderForPayoo: [],
                payooMethodList: {},
                bankCode: null,
            }
        }
    },
    methods: {
        initPayoo() {
            let a = Object.assign({}, $.ajaxSettings.headers);
            delete $.ajaxSettings.headers;
            //common.loading.show('.pay-by-payoo');
            let request = $.ajax({
                url: window.stringUtil.getUrlWithQueries(this.payoo.methodApi, {
                    url: this.payoo.appUrl,
                    id: this.payoo.shopId,
                    seller: this.payoo.sellerName,
                }),
                method: 'GET',
                dataType  : 'text',
            }).done((res) => {
                res = res.replace('Payoo.render(', '').replace(/\);$/, '');
                let data = JSON.parse(res.replace('Payoo.render(', '').replace(/\);$/, ''));
                data.methods.forEach((method) => {
                    this.$set(this.payooMethodList, method.name, method.icons || []);
                });
            }).fail((xhr, a, b) => {
                console.error(xhr, a, b);
            }).always(() => {
                //common.loading.hide('.pay-by-payoo');
            });
            $.ajaxSettings.headers = a;
            return request;
        },
    }
}
