const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
	.js('resources/js/back/app.js', 'public/js/back')

	.js('resources/js/front/app.js', 'public/js/front')
	.js('resources/js/front/common.js', 'public/js/front')
	.js('resources/js/front/login.js', 'public/js/front')
	.js('resources/js/front/register.js', 'public/js/front')
	.js('resources/js/front/verify.js', 'public/js/front')
	.js('resources/js/front/auth/auth.js', 'public/js/front/auth')

    .js('resources/js/front/news/list.js', 'public/js/front/news')
    .js('resources/js/front/news/detail.js', 'public/js/front/news')


    .js('resources/js/front/order/list.js', 'public/js/front/order')
    .js('resources/js/front/order/detail.js', 'public/js/front/order')
	.js('resources/js/front/home/home.js', 'public/js/front/home')


    .js('resources/js/front/profile/payment-history.js', 'public/js/front/profile')
    .js('resources/js/front/profile/personal-info.js', 'public/js/front/profile')
    .js('resources/js/front/profile/change-password.js', 'public/js/front/profile')


    .js('resources/js/front/shop/create-update-shop.js', 'public/js/front/shop')
	.js('resources/js/front/shop/shop-info.js', 'public/js/front/shop')
    .js('resources/js/front/shop/shop-resource.js', 'public/js/front/shop')
    .js('resources/js/front/shop/notification.js', 'public/js/front/shop')
    .js('resources/js/front/shop/banner.js', 'public/js/front/shop')
    .js('resources/js/front/shop/upgrade.js', 'public/js/front/shop')
    .js('resources/js/front/shop/shop-review.js', 'public/js/front/shop')


    .js('resources/js/front/product/list.js', 'public/js/front/product')
	.js('resources/js/front/product/create.js', 'public/js/front/product')
	.js('resources/js/front/product/detail.js', 'public/js/front/product')
    .js('resources/js/front/product/saved-product.js', 'public/js/front/product')

	.js('resources/js/front/cart/cart.js', 'public/js/front/cart')
	.js('resources/js/front/cart/cart-menu.js', 'public/js/front/cart')

	.js('resources/js/front/payment/deposit.js', 'public/js/front/payment')
	.js('resources/js/front/payment/common.js', 'public/js/front/payment')

	.js('resources/js/front/chat/chat.js', 'public/js/front/chat')

	.js('resources/js/front/notification/notification.js', 'public/js/front/notification')
;

mix
    .sass('resources/sass/common/custom.scss', 'public/css/common')
    .sass('resources/sass/common/core.scss', 'public/css/common')

    .sass('resources/sass/back/core.scss', 'public/css/back')
    .sass('resources/sass/back/app.scss', 'public/css/back')
    .sass('resources/sass/back/login.scss', 'public/css/back')

	.sass('resources/sass/front/core.scss', 'public/css/front')
	.sass('resources/sass/front/app.scss', 'public/css/front')
	.sass('resources/sass/front/home.scss', 'public/css/front')
    .sass('resources/sass/front/login.scss', 'public/css/front')

	.sass('resources/sass/front/home-introduce.scss', 'public/css/front')
	.sass('resources/sass/front/creating-form.scss', 'public/css/front')

	.sass('resources/sass/front/product.scss', 'public/css/front')
	.sass('resources/sass/front/shop.scss', 'public/css/front')
	.sass('resources/sass/front/chat.scss', 'public/css/front')

	.sass('resources/sass/front/order.scss', 'public/css/front')
    .sass('resources/sass/front/payment-history.scss', 'public/css/front')
    .sass('resources/sass/front/news.scss', 'public/css/front')
    .sass('resources/sass/front/menu.scss', 'public/css/front')
;

mix.version();
