import VueRouter from 'vue-router';
import MainHeader from "./component/MainHeader";
import MainContent from "./component/MainContent";
import User from "./user/UserManagement";
import UserInfo from "./user/UserInfo";
import UserDetail from "./user/UserDetail";
import Home from "./home/Home";
import CustomerType from "../constants/customer-type";
import EUserType from "../constants/user-type";
import CategoryList from "./category/product/List";
import IssueReportList from "./category/issue-report/List";
import CategoryCreatingForm from "./category/product/CreatingForm";
import ECategoryType from "../constants/category-type";
import PaymentList from "./payment/List";
import BranchList from "./branch/List";
import BranchInfo from "./branch/BranchInfo";
// import FeedbackList from "./feedback/List";
import NotificationList from "./notification/List";
import NotificationCreatingForm from "./notification/CreatingForm";
import Error404 from "./errors/404";
// import HomeConfig from "./config-content/HomeConfig";
// import EHomeConfigType from "../constants/home-config-type";
import EPolicyType from "../constants/policy-type";
import PolicyConfig from "./policy/PolicyConfig";
import ShopManagement from "./shop/ShopManagement";
import ShopInfo from "./shop/ShopInfo";
import ShopOrder from "./shop/ShopOrder";
import ShopResource from "./shop/ShopResource";
import ShopDetail from "./shop/ShopDetail";
import News from "./news/NewsList";
import NewsInfo from "./news/NewsInfo";
import GeneralConfig from "./config/General"
import BannerConfig from "./config/Banner";
import ForbiddenSearchConfig from "./config/forbiddenSeach/ForbiddenSearch.vue";
import PackagePushProduct from "./config/package/PackagePushProduct"
import PackageUpgradeShop from "./config/package-upgrade-shop/PackageUpgradeShop"
import ShopLevel from "./config/shop-level/ShopLevel";
import ProductList from "./product/ProductList";
import ProductInfo from "./product/ProductInfo";
import ProductReported from "./product-reported/List";
import Analytics from "./analytic/Analytics";

function prefixRoutes(prefix, routes) {
    return routes.map((route) => {
        route.path = `${prefix}/${route.path}`;
        return route;
    });
}

function getRouter(i18n, appConfig) {
    const routes = [
        {
            path: '/',
            component: MainContent,
            children: [
                {
                    path: '/',
                    name: 'home',
                    components: {default: Home, header: MainHeader},
                    props: {
                        header: {
                            title: i18n.t('home.title')
                        },
                    },
                    meta: {
                        module: 'home',
                    }
                },
            ],
            // redirect: '/dashboard'
        },
        {
            path: '/analytic',
            component: MainContent,
            children: [
                {
                    path: '/analytic',
                    name: 'analytic',
                    components: {default: Analytics, header: MainHeader},
                    props: {
                        header: {
                            title: i18n.t('home.title')
                        },
                    },
                    meta: {
                        module: 'analytic',
                    }
                },
            ],
            // redirect: '/dashboard'
        },
        {
            name: 'customer',
            path: '/customer',
            component: MainContent,
            redirect: {name: 'customer.list'},
            children: [
                {
                    path: 'list',
                    name: 'customer.list',
                    components: {default: User, header: MainHeader},
                    props: {
                        default: {userType: EUserType.NORMAL_USER}
                    },
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/customer`,
                    },
                },
                {
                    path: ':userId',
                    name: 'customer.detail',
                    redirect: {name: 'customer.info'},
                    components: {default: UserDetail, header: MainHeader},
                    children: [
                        {
                            path: ':action(create||edit)',
                            name: 'customer.info',
                            components: {default: UserInfo, header: MainHeader},
                            props: {
                                default: {userType: EUserType.NORMAL_USER}
                            },
                            meta: {
                                baseUrl: `${appConfig.baseApiUrl}/customer`,
                            },
                        },
                    ],
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/customer`,
                    },
                },
            ],
            meta: {
                module: 'customer',
            }
        },
        {
            name: 'shop',
            path: '/shop',
            component: MainContent,
            redirect: {name: 'shop.list'},
            children: [
                {
                    path: 'list',
                    name: 'shop.list',
                    components: {default: ShopManagement, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/shop`,
                    },
                },
                {
                    path: ':shopId',
                    name: 'shop.detail',
                    redirect: {name: 'shop.info'},
                    components: {default: ShopDetail, header: MainHeader},
                    children: [
                        {
                            path: ':action(create||edit)',
                            name: 'shop.info',
                            components: {default: ShopInfo, header: MainHeader},
                            meta: {
                                baseUrl: `${appConfig.baseApiUrl}/shop`,
                                module: 'info',
                            },
                        },
                        {
                            path: 'resource',
                            name: 'shop.resource',
                            components: {default: ShopResource, header: MainHeader},
                            meta: {
                                baseUrl: `${appConfig.baseApiUrl}/shop`,
                                module: 'resource',
                            },
                        },
                    ],
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/shop`,
                    },
                },
                {
                    path: ':shopId',
                    name: 'shop.order',
                    redirect: {name: 'shop.order'},
                    components: {default: ShopDetail, header: MainHeader},
                    children: [
                        {
                            path: 'order',
                            name: 'shop.order',
                            components: {default: ShopOrder, header: MainHeader},
                            meta: {
                                baseUrl: `${appConfig.baseApiUrl}/shop`,
                                module: 'order',
                            },
                        },
                    ],
                },
            ],
            meta: {
                module: 'shop',
            }
        },
        {
            name: 'product-category',
            path: '/product-category',
            component: MainContent,
            redirect: { name: 'product-category.list'},
            children: [
                {
                    name: 'product-category.list',
                    path: 'list',
                    components: {default: CategoryList, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/product-category`
                    }
                },

            ],
            meta: {
                module: 'category'
            }
        },
        {
            name: 'issue-report',
            path: '/issue-report',
            component: MainContent,
            redirect: { name: 'issue-report.list'},
            children: [
                {
                    name: 'issue-report.list',
                    path: 'list',
                    components: {default: IssueReportList, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/issue-report`
                    }
                },
            ],
            meta: {
                module: 'category'
            }
        },
        {
            name: 'product',
            path: '/product',
            component: MainContent,
            redirect: { name: 'product.list'},
            children: [
                {
                    name: 'product.list',
                    path: 'list',
                    components: {default: ProductList, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/product`
                    }
                },
                {
                    path: ':productId/:action(create||edit||info)',
                    name: 'product.info',
                    components: {default: ProductInfo, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/product`,
                    },
                },
            ],
            meta: {
                module: 'product'
            }
        },
        {
            name: 'product-report',
            path: '/product-report',
            component: MainContent,
            redirect: { name: 'product-report.list'},
            children: [
                {
                    name: 'product-report.list',
                    path: 'list',
                    components: {default: ProductReported, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/product-report`
                    }
                },
            ],
            meta: {
                module: 'product'
            }
        },
        {
            name: 'branch',
            path: '/branch',
            component: MainContent,
            redirect: { name: 'branch.list'},
            children: [
                {
                    name: 'branch.list',
                    path: 'list',
                    components: {default: BranchList, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/branch`
                    }
                },
                {
                    path: ':action',
                    name: 'branch.create',
                    components: {default: BranchInfo, header: MainHeader},
                    props: {
                        default: {userType: EUserType.INTERNAL_USER}
                    },
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/branch`,
                    },
                },
                {
                    path: ':branchId/:action/',
                    name: 'branch.info',
                    components: {default: BranchInfo, header: MainHeader},
                    props: {
                        default: {userType: EUserType.INTERNAL_USER}
                    },
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/branch`,
                    },
                },
            ],
            meta: {
                module: 'branch'
            }
        },
        {
            name: 'payment',
            path: '/payment',
            component: MainContent,
            redirect: { name: 'payment.list' },
            children: [
                {
                    name: 'payment.list',
                    path: 'list',
                    components: {default: PaymentList, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/payment`,
                        module: 'subscription'
                    }
                },
                // {
                //     name: 'payment.order.list',
                //     path: 'order/list',
                //     components: {default: PaymentList, header: MainHeader},
                //     meta: {
                //         baseUrl: `${appConfig.baseApiUrl}/payment`,
                //         module: 'order'
                //     }
                // },
            ],
            meta: {
                module: 'payment'
            }
        },
        {
            name: 'employee',
            path: '/employee',
            component: MainContent,
            redirect: {name: 'employee.list'},
            children: [
                {
                    path: 'list',
                    name: 'employee.list',
                    components: {default: User, header: MainHeader},
                    props: {
                        default: {userType: EUserType.INTERNAL_USER}
                    },
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/employee`,
                    },
                },
                {
                    path: ':userId/:action/',
                    name: 'employee.info',
                    components: {default: UserInfo, header: MainHeader},
                    props: {
                        default: {userType: EUserType.INTERNAL_USER}
                    },
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/employee`,
                    },
                },
                {
                    path: ':action',
                    name: 'employee.create',
                    components: {default: UserInfo, header: MainHeader},
                    props: {
                        default: {userType: EUserType.INTERNAL_USER}
                    },
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/employee`,
                    },
                },
            ],
            meta: {
                module: 'employee',
            }
        },
        {
            name: 'notification',
            path: '/notification',
            component: MainContent,
            redirect: { name: 'notification.list' },
            children: [
                {
                    name: 'notification.list',
                    path: 'list',
                    components: {default: NotificationList, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/notification`
                    }
                },
                {
                    name: 'notification.create',
                    path: 'create',
                    components: {default: NotificationCreatingForm, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/notification`
                    },
                },
                {
                    name: 'notification.detail',
                    path: ':notificationScheduleId/:action(detail||edit)',
                    components: {default: NotificationCreatingForm, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/notification`
                    },
                },
            ],
            meta: {
                module: 'notification'
            }
        },
        {
            name: 'news',
            path: '/news',
            component: MainContent,
            redirect: {name: 'news.list'},
            children: [
                {
                    path: 'list',
                    name: 'news.list',
                    components: {default: News, header: MainHeader},
                    props: {
                        default: {userType: EUserType.INTERNAL_USER}
                    },
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/news`,
                    },
                },
                {
                    path: ':newsId/:action/',
                    name: 'news.info',
                    components: {default: NewsInfo, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/news`,
                    },
                },
                {
                    path: ':action',
                    name: 'news.create',
                    components: {default: NewsInfo, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/news`,
                    },
                },
            ],
            meta: {
                module: 'news',
            }
        },
        {
            name: 'config',
            path: '/config',
            component: MainContent,
            redirect: {name: 'config.general'},
            children: [
                {
                    name: 'config.general',
                    path: '/config/general',
                    components: {default: GeneralConfig, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/config/general`,
                    },
                },
                {
                    name: 'config.banner',
                    path: '/config/banner',
                    components: {default: BannerConfig, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/config/banner`,
                    },
                },
                {
                    name: 'config.forbidden_search',
                    path: '/config/forbidden_search',
                    components: {default: ForbiddenSearchConfig, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/config/forbidden_search`,
                    },
                },
                {
                    name: 'config.package-push-product',
                    path: '/config/package-push-product',
                    components: {default: PackagePushProduct, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/config/package`,
                    },
                },
                {
                    name: 'config.package-upgrade-shop',
                    path: '/config/package-upgrade-shop',
                    components: {default: PackageUpgradeShop, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/config/package-upgrade-shop`,
                    },
                },
                {
                    name: 'config.shop-level',
                    path: '/config/shop-level',
                    components: {default: ShopLevel, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/config/shop-level`,
                    },
                },
                {
                    name: 'config.policy',
                    path: ':policyType',
                    components: {default: PolicyConfig, header: MainHeader},
                    meta: {
                        baseUrl: `${appConfig.baseApiUrl}/config/policy`
                    },
                },
            ],
            meta: {
                module: 'config',
            }
        },
        {
            path: '/not-found',
            name: '404',
            component: Error404,
        },
        {
            path: '*',
            redirect: { name: '404' },
        }
    ];

    const router = new VueRouter({
        mode: 'history',
        base: '/back',
        routes
    });
    return router;
}

export default getRouter;
