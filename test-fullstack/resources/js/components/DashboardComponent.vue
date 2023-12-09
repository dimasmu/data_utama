<template>
    <v-card>
        <v-layout>
            <v-navigation-drawer expand-on-hover rail>
                <v-list>
                    <v-list-item
                        prepend-avatar="https://static.vecteezy.com/system/resources/previews/020/911/740/original/user-profile-icon-profile-avatar-user-icon-male-icon-face-icon-profile-icon-free-png.png"
                        :title="username"></v-list-item>
                </v-list>

                <v-divider></v-divider>

                <v-list density="compact" nav>
                    <v-list-item :class="{ 'active': currentRouteName === 'products' }" @click="navigateToProducts"
                        prepend-icon="mdi-archive" title="Products" value="myfiles"></v-list-item>
                    <v-list-item :class="{ 'active': currentRouteName === 'transactions' }" @click="navigateToTransactions"
                        prepend-icon="mdi-account-multiple" title="Transaction" value="shared"></v-list-item>
                </v-list>
            </v-navigation-drawer>

            <v-main style="height: 1024px; background-color: #f5f5f5;">
                <!-- Header -->
                <v-app-bar color="primary" dark fixed style="margin-bottom: 50px;">
                    <template v-slot:append>
                        {{ currentRouteName }}
                        <v-btn icon="mdi-logout" @click="logout"></v-btn>
                    </template>
                </v-app-bar>

                <v-container fluid style="height: 500px;padding: 2.5rem;margin-top: 5rem;background-color: whitesmoke;">
                    <router-view></router-view>
                </v-container>
            </v-main>
        </v-layout>
    </v-card>
</template>

<script>
export default {
    data() {
        return {
            "username": ""
        }
    },
    computed: {
        currentRouteName() {
            return this.$route.name;
        }
    },
    methods: {
        navigateToProducts() {
            this.$router.push('/products');
        },
        navigateToTransactions() {
            this.$router.push('/transactions');
        },
        async logout() {
            try {
                await axios.post('/api/auth/logout');
                this.$router.push('/login');
                localStorage.removeItem('username');
            } catch (error) {
                console.error('Logout failed:', error);
            }
        }
    },
    watch: {
        currentRouteName(newRoute, oldRoute) {
            console.log(`Route changed from ${oldRoute} to ${newRoute}`);
            this.previousRoute = oldRoute;
        }
    },
    mounted() {
        this.username = localStorage.getItem('username');
    }
};

</script>
