<template>
    <Head title="File Storage"/>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <h1 class="text-3xl font-bold">File Storage Task</h1>

        <div class="flex flex-row space-x-8 items-center mt-8">
            <Modal :fileName="fileName"
                   :fileExistError="fileError"
                   :files="files"
                   :isLoading="isLoading"
                   :currentPageFilesCountVar="currentPageFilesCountVar"
                   :totalFilesCountVar="totalFilesCountVar"
                   @addCounter="addCounter"
            />

            <div class="text-sm">
                <form @submit.prevent="submitSearch">
                    <input type="text" v-model="searchTerm" placeholder="Поиск по названию" class="p-2 border rounded text-sm">
                    <button type="submit" class="p-2 border rounded">Искать</button>
                </form>
            </div>
        </div>

        <div class="mt-8">
            <p class="text-sm">Файлов на текущей странице: {{ currentPageFilesCountVar }}</p>
            <p class="text-sm">Общее кол-во файлов: {{ totalFilesCountVar }}</p>
        </div>

        <FileTable :files="files" :openDeleteModal="openDeleteModal" />

        <div class="mt-2">
            <pagination :links="files.links" :totalFilesCount="totalFilesCount" :currentPageFilesCount="currentPageFilesCount" />
        </div>



        <transition name="deleteModal">
            <div v-if="showDeleteModal" class="modal-mask" @click="closeDeleteModalOutside">
                <div class="modal-wrapper" @click.stop>
                    <div class="modal-container text-center">
                        <p class="text-lg mb-4">Подтвердите удаление файла:</p>
                        <p class="text-xl font-bold">{{ fileNameToDelete }}</p>
                        <button @click="closeDeleteModal" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <button @click="deleteFile(fileIdToDelete)" class="text-sm mt-4 bg-green-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Да
                        </button>
                        <button @click="closeDeleteModal" class="text-sm mt-4 ml-2 bg-gray-400 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Отмена
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <modal v-if="showModal" @confirm="deleteFile"></modal>
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref, defineProps } from 'vue';
import { Head } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue'
import FileTable from "@/Components/FileTable.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
    totalFilesCount: Number,
    currentPageFilesCount: Number,
});

const currentPageFilesCountVar = ref(props.currentPageFilesCount);
const totalFilesCountVar = ref(props.totalFilesCount);
const showDeleteModal = ref(false);
const fileName = ref('');
const fileIdToDelete = ref(null);
const fileNameToDelete = ref(null);
let fileIdx = ref(null);
const fileError = ref('');


const openDeleteModal = (fileId, fileName, idx) => {
    fileIdToDelete.value = fileId;
    fileNameToDelete.value = fileName;
    showDeleteModal.value = true;
    console.log(idx);
    fileIdx.value = idx;
};

const addCounter = () => {
    currentPageFilesCountVar.value += 1;
    totalFilesCountVar.value += 1;
}

const closeDeleteModal = () => {
    showDeleteModal.value = false;
};

const closeDeleteModalOutside = (event) => {
    if (event.target.classList.contains('modal-mask')) {
        closeDeleteModal();
    }
};

const deleteFile = async (fileId) => {
    try {
        const response = await axios.delete(`/files/${fileId}`);

        props.files.data.splice(fileIdx.value, 1);

        currentPageFilesCountVar.value -= 1;
        totalFilesCountVar.value -= 1;

        closeDeleteModal();
    } catch (error) {
        console.error('Error deleting file:', error);
    }
};

const searchTerm = ref('');

const submitSearch = async () => {
    try {
        const response = await fetch(`/files/fetch?name=${searchTerm.value}`);
        const data = await response.json();
        props.files.data = data.files.data;

        const params = new URLSearchParams(window.location.search);
        // params.set('search', true);
        params.set('name', searchTerm.value);
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({}, '', newUrl);
        currentPageFilesCountVar.value = data.currentPageFilesCount;
    } catch (error) {
        console.error('Error fetching filtered files:', error);
    }
};

const populateSearchTerm = () => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('name')) {
        searchTerm.value = params.get('name');
    }
};

populateSearchTerm();
</script>

<style scoped>
.modal-mask {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-wrapper {
    max-width: 600px;
    width: 100%;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
