<template>
    <div class="d-flex align-center justify-center" style="height: 100vh">
        <v-sheet width="400" class="mx-auto">
            <v-form fast-fail @submit.prevent="login">
                <v-text-field variant="outlined" v-model="username" label="User Name"></v-text-field>

                <v-text-field variant="outlined" v-model="password" label="password"></v-text-field>
                <!-- <a href="#" class="text-body-2 font-weight-regular">Forgot Password?</a> -->

                <v-btn type="submit" color="primary" block class="mt-2">Sign in</v-btn>

            </v-form>
            <!-- <div class="mt-2">
                <p class="text-body-2">Don't have an account? <a href="#">Sign Up</a></p>
            </div> -->
        </v-sheet>
    </div>
</template>
<script>
import axios from 'axios';

export default {
    data() {
        return {
            username: '',
            password: '',
        };
    },
    methods: {
        async login() {
            try {
                const response = await axios.post('/api/login', {
                    username: this.username,
                    password: this.password,
                });

                if (response.data.response == true) {
                    this.$router.push({
                        name: 'dashboard',
                        params: {
                            username: this.username
                        }
                    });
                    localStorage.setItem('username', this.username);
                }
            } catch (error) {
                // Handle login error (e.g., show error message)
                console.error('Login failed:', error.response.data);
            }
        },
    },
};
</script>

