import { createRouter, createWebHistory } from "vue-router";
import Login from "../components/LoginComponent.vue";
import Dashboard from "../components/DashboardComponent.vue";
import ProductsComponent from "../components/ProductsComponent.vue";
import TransactionComponent from "../components/TransactionComponent.vue";

const routes = [
    {
        path: "/login",
        name: "login",
        component: Login,
    },
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
        children: [
            {
                path: "/products",
                name: "products",
                component: ProductsComponent,
            },
            {
                path: "/transactions",
                name: "transactions",
                component: TransactionComponent,
            },
        ],
        redirect: "/products",
    },
    {
        path: "/",
        redirect: "/products",
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    console.log(to, from);
    if (to.name.toLowerCase() !== "login".toLowerCase()) {
        try {
            await axios.get("/api/auth/me");
            if (to.name.toLowerCase() === "login".toLowerCase()) {
                next({ name: "dashboard" });
            } else {
                next();
            }
        } catch (error) {
            next({ name: "login" });
        }
    } else {
        next();
    }
});

export default router;
