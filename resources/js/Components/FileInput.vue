<template>
    <div class="mb-4">
        <label for="file" class="block text-gray-700 font-bold mb-2">Выберите файл:</label>
        <input type="file" id="file" name="file" class="border rounded-md p-2 w-full" @change="validateFile">
        <span v-if="fileError" class="text-red-500">{{ fileError }}</span>
    </div>
</template>

<script setup>
import { ref, defineExpose } from "vue";

const fileError = ref('Файл не выбран');

const validateFile = (event) => {
    const file = event.target.files[0];
    const maxSize = 8 * 1024 * 1024; // 8MB in bytes
    if (file && file.size > maxSize) {
        fileError.value = 'Размер файла должен быть не более 8MB';
        event.target.value = ''; // Clear the file input
    } else {
        fileError.value = '';
    }
};

defineExpose({
    fileError,
})
</script>
