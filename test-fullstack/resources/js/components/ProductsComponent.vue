<template>
    <v-app>
        <v-dialog v-model="modalControl.confirmationModal" max-width="500">
            <v-card>
                <v-card-title>Confirm Deletion</v-card-title>
                <v-card-text>
                    Are you sure you want to delete this product?
                </v-card-text>
                <v-card-actions>
                    <v-btn @click="modalControl.confirmationModal = false">No</v-btn>
                    <v-btn color="primary" @click="confirmDeleteProduct">Yes</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="modalControl.addModal" max-width="500">
            <v-card>
                <v-card-title>Add Product</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="submitProduct">
                        <v-text-field v-model="setProduct.name" label="Name"></v-text-field>
                        <v-text-field v-model="setProduct.price" label="Price"
                            @input="restrictToNumber('price')"></v-text-field>
                        <v-text-field v-model="setProduct.stock" label="Stock"
                            @input="restrictToNumber('stock')"></v-text-field>
                        <v-text-field v-model="setProduct.description" label="Description"></v-text-field>
                        <v-btn type="submit" color="primary">{{ is_edit ? "Edit" : "Save" }}</v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-container>
            <v-row>
                <v-col cols="12" class="text-right mb-4">
                    <v-btn color="primary" @click="openAddModal">Add Product</v-btn>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <v-data-table-server v-model:items-per-page="pagination.rowsPerPage" :headers="headers"
                        :items-length="pagination.totalItems" :items="products" :loading="loading" item-value="id"
                        @update:options="fetchData">
                        <template v-slot:item.actions="{ item }">
                            <v-icon size="small" class="me-2" icon=" mdi-pencil" @click="editProduct(item)"></v-icon>
                            <v-icon size="small" icon=" mdi-delete" @click="deleteProduct(item)"></v-icon>
                        </template>
                        <template v-slot:no-data>
                            <v-btn color="primary" @click="initialize">
                                Reset
                            </v-btn>
                        </template>
                    </v-data-table-server>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            is_edit: false,
            headers: [
                { title: 'Name', key: 'name', sortable: false },
                { title: 'Price', key: 'price', sortable: false },
                { title: 'Stock', key: 'stock', sortable: false },
                { title: 'Description', key: 'description', sortable: false },
                { title: 'Actions', key: 'actions', sortable: false }
            ],
            products: [],
            loading: false,
            search: '',
            pagination: {
                sortBy: 'id',
                descending: false,
                page: 1,
                rowsPerPage: 5,
                totalItems: 0,
            },
            modalControl: {
                addModal: false,
                confirmationModal: false
            },
            setProduct: {
                id: '',
                name: '',
                price: '',
                stock: '',
                description: '',
            }
        };
    },
    mounted() {
        // this.fetchData();
    },
    methods: {
        openAddModal() {
            this.clearForm();
            this.is_edit = false;
            this.modalControl.addModal = true;
        },
        async fetchData() {
            this.loading = true;
            try {
                const { page, rowsPerPage } = this.pagination;
                const response = await axios.get('/api/product', {
                    params: {
                        page,
                        rowsPerPage,
                    },
                });
                this.products = response.data.data.data;
                this.pagination.totalItems = response.data.data.total;
                this.loading = false;
            } catch (error) {
                console.error('Error fetching data:', error);
                this.loading = false;
            }
        },
        async submitProduct() {
            if (!this.is_edit) {
                this.saveProduct();
            } else {
                this.updateProduct();
            }
        },
        async saveProduct() {
            try {
                const response = await axios.post('/api/product', this.setProduct);
                console.log('Product saved:', response.data);
                this.modalControl.addModal = false;
                this.clearForm();
                await this.fetchData();
            } catch (error) {
                console.error('Error saving product:', error);
            }
        },
        async updateProduct() {
            try {
                const response = await axios.put(`/api/product/${this.setProduct.id}`, this.setProduct);
                console.log('Product updated:', response.data);
                this.modalControl.addModal = false;
                this.clearForm();
                this.editMode = false;
                await this.fetchData();
            } catch (error) {
                console.error('Error updating product:', error);
            }
        },
        async confirmDeleteProduct() {
            if (this.setProduct.id) {
                try {
                    const response = await axios.delete(`/api/product/${this.setProduct.id}`);
                    console.log('Product deleted:', response.data);
                    await this.fetchData();
                } catch (error) {
                    console.error('Error deleting product:', error);
                }
                this.clearForm();
                this.modalControl.confirmationModal = false;
            }
        },
        deleteProduct(data) {
            this.modalControl.confirmationModal = true;
            this.setProduct = data;
        },
        editProduct(data) {
            this.is_edit = true;
            this.setProduct.id = data.id;
            this.setProduct.name = data.name;
            this.setProduct.price = data.price;
            this.setProduct.stock = data.stock;
            this.setProduct.description = data.description;
            this.modalControl.addModal = true;
        },
        clearForm() {
            this.setProduct = {
                name: '',
                price: '',
                stock: '',
                description: '',
            };
        },
        restrictToNumber(field) {
            this.setProduct[field] = this.setProduct[field].replace(/\D/g, '');
        },
    },
};
</script>

<style></style>
