<template>
	<div>
		<b-row>
			<b-col md="7" class="mb-3">
				<morphing-input
                        v-model="createdAtGt"
                        :field="{name: 'createdAtGt', maxWidth: '100%', type: 'date',placeholder:'Từ ngày'}"
                        class="mb-3"
                        ref="createdAtGt"
                        placeholder="Từ ngày"
                    />
			</b-col>
			<b-col md="7" class="mb-3">
				<morphing-input
                        v-model="createdAtLt"
                        :field="{name: 'createdAtLt', maxWidth: '100%', type: 'date', 
                        placeholder:'Đến ngày'}"
                        class="mb-3"
                        ref="createdAtLt"
                    />
			</b-col>
			</b-col>
			<b-col md="4" class="mb-3">
				<button class="btn btn-primary" @click="getListData(null)">Thống kê</button>
			</b-col>
			<b-col md="30" class="mb-3">
				<button class="btn btn-primary float-right" @click="allData">Thống kê từ trước đến nay</button>
			</b-col>
			<b-col md="48" class="mb-3">
				<table class="w-100 table table-striped">
					<tbody>
						<tr>
							<th class="text-center">Số lượt truy cập</th>
							<th class="text-center">Tổng số người bán</th>
							<th class="text-center">Tổng số lượt giao dịch</th>
							<th class="text-center">Tổng giá trị giao dịch</th>
						</tr>
						<tr>
							<td class="text-center">{{googleAnalytics}}</td>
							<td class="text-center">{{totalShop}}</td>
							<td class="text-center">{{totalTransaction}}</td>
							<td class="text-center">{{totalValue}}</td>
						</tr>
					</tbody>
				</table>
			</b-col>
		</b-row>
	</div>
</template>

<script>
	import homeMessage from '../../locales/back/home';
	import listMixin from '../mixins/list-mixin';
	export default {
		name: "Home",
		i18n: {
			messages: homeMessage,
		},
		inject: ['Util', 'DateUtil'],
		mixins: [listMixin],
		data () {
			return {
				googleAnalytics: 0,
				totalShop: 0,
				totalTransaction: 0,
				totalValue: 0,
				createdAtGt: null,
				createdAtLt: null
			}
		},
		created() {
			this.$store.commit('updateBreadcrumbsState', [
                {
                    text: 'Thống kê',
                    to: ''
                },
            ]);
			this.$store.commit('updateQueryFilterState', {enable: false});
			this.getListData();
		},
		methods: {
			allData() {
				this.createdAtGt = null;
				this.createdAtLt = null;
				$('.form-control').val('');
				this.getListData(true);
			},
			getListData(getAll) {
				this.Util.loadingScreen.show();
				this.Util.get({
	                url: `/api/back/analytics`,
	                data: {
	                	filter: {
		                    createdAtGt: this.createdAtGt,
		                    createdAtLt: this.createdAtLt,
		                    all: getAll
		                }
	                },
	            }).done(async (res) => {
	            	if (res.error == 1) {
	            		this.Util.showMsg('error', null, res.msg);
	            		return;
	            	}
	            	this.googleAnalytics = 0;
	                res.data.googleAnalytics.forEach((item, index) => {
                        this.googleAnalytics += item.pageViews;
                    });
                    this.totalShop = res.data.totalShop;
                    this.totalTransaction = res.data.totalTransaction;
                    this.totalValue = res.data.totalValue;
	            }).always(() => {
	            	this.Util.loadingScreen.hide();
	            });
			}
		}
	}
</script>