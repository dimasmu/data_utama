<template>
    <v-app>
        <v-dialog v-model="modalControl.confirmationModal" max-width="500">
            <v-card>
                <v-card-title>Confirm Deletion</v-card-title>
                <v-card-text>
                    Are you sure you want to delete this Transaction?
                </v-card-text>
                <v-card-actions>
                    <v-btn @click="modalControl.confirmationModal = false">No</v-btn>
                    <v-btn color="primary" @click="confirmDeleteTransaction">Yes</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="modalControl.addModal" max-width="500">
            <v-card>
                <v-card-title>Add Transaction</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="submitTransaction">
                        <v-text-field v-model="setTransaction.name" label="Name"></v-text-field>
                        <v-text-field v-model="setTransaction.price" label="Price"
                            @input="restrictToNumber('price')"></v-text-field>
                        <v-text-field v-model="setTransaction.stock" label="Stock"
                            @input="restrictToNumber('stock')"></v-text-field>
                        <v-text-field v-model="setTransaction.description" label="Description"></v-text-field>
                        <v-btn type="submit" color="primary">{{ is_edit ? "Edit" : "Save" }}</v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-container>
            <v-row>
                <v-col cols="12" class="text-right mb-4">
                    <v-btn color="primary" @click="openAddModal">Add Transaction</v-btn>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <v-data-table-server v-model:items-per-page="pagination.rowsPerPage" :headers="headers"
                        :items-length="pagination.totalItems" :items="Transactions" :loading="loading" item-value="id"
                        @update:options="fetchData">
                        <template v-slot:item.actions="{ item }">
                            <v-icon size="small" class="me-2" icon=" mdi-pencil" @click="editTransaction(item)"></v-icon>
                            <v-icon size="small" icon=" mdi-delete" @click="deleteTransaction(item)"></v-icon>
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
                { title: 'Reference No', key: 'reference_no', sortable: false },
                { title: 'Price', key: 'price', sortable: false },
                { title: 'Quantity', key: 'quantity', sortable: false },
                { title: 'Payment Amount', key: 'payment_amount', sortable: false },
                { title: 'Product_id', key: 'product_id', sortable: false },
                { title: 'Actions', key: 'actions', sortable: false }
            ],
            Transactions: [],
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
            setTransaction: {
                reference_no: '',
                price: '',
                quantity: '',
                payment_amount: '',
                product_id: '',
                actions: '',
            },
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
                const response = await axios.get('/api/transaction', {
                    params: {
                        page,
                        rowsPerPage,
                    },
                });
                this.Transactions = response.data.data.data;
                this.pagination.totalItems = response.data.data.total;
                this.loading = false;
            } catch (error) {
                console.error('Error fetching data:', error);
                this.loading = false;
            }
        },
        async submitTransaction() {
            if (!this.is_edit) {
                this.saveTransaction();
            } else {
                this.updateTransaction();
            }
        },
        async saveTransaction() {
            try {
                const response = await axios.post('/api/transaction', this.setTransaction);
                console.log('Transaction saved:', response.data);
                this.modalControl.addModal = false;
                this.clearForm();
                await this.fetchData();
            } catch (error) {
                console.error('Error saving Transaction:', error);
            }
        },
        async updateTransaction() {
            try {
                const response = await axios.put(`/api/transaction/${this.setTransaction.id}`, this.setTransaction);
                console.log('Transaction updated:', response.data);
                this.modalControl.addModal = false;
                this.clearForm();
                this.editMode = false;
                await this.fetchData();
            } catch (error) {
                console.error('Error updating Transaction:', error);
            }
        },
        async confirmDeleteTransaction() {
            if (this.setTransaction.id) {
                try {
                    const response = await axios.delete(`/api/transaction/${this.setTransaction.id}`);
                    console.log('Transaction deleted:', response.data);
                    await this.fetchData();
                } catch (error) {
                    console.error('Error deleting Transaction:', error);
                }
                this.clearForm();
                this.modalControl.confirmationModal = false;
            }
        },
        deleteTransaction(data) {
            this.modalControl.confirmationModal = true;
            this.setTransaction = data;
        },
        editTransaction(data) {
            this.is_edit = true;
            this.setTransaction.id = data.id;
            this.setTransaction.name = data.name;
            this.setTransaction.price = data.price;
            this.setTransaction.stock = data.stock;
            this.setTransaction.description = data.description;
            this.modalControl.addModal = true;
        },
        clearForm() {
            this.setTransaction = {
                name: '',
                price: '',
                stock: '',
                description: '',
            };
        },
        restrictToNumber(field) {
            this.setTransaction[field] = this.setTransaction[field].replace(/\D/g, '');
        },
    },
};
</script>

<style></style>
