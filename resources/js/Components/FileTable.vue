<template>
    <div class="mt-8 rounded-2xl overflow-hidden">

        <table class="table table-auto bg-white text-sm text-left border-collapse">
            <thead>
            <tr class="border">
                <th class="p-2 border">Название файла</th>
                <th class="p-2 border">Размер</th>
                <th class="p-2 border">Расширение</th>
                <th class="p-2 border"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(file, idx) in files.data" :key="file.id" class="border">
                <td class="p-2 border">{{ file.name }}</td>
                <td class="p-2 border">{{ file.size }}</td>
                <td class="p-2 border">.{{ file.extension }}</td>
                <td class="p-2 border">
                    <img v-if="file.thumbnail" :src="file.thumbnail">
                </td>
                <td class="p-2 border">
                    <div class="flex flex-row space-x-1">
                        <a :href="file.url" download>
                            <img src="/images/download.svg" width="20">
                        </a>
                        <a @click="openDeleteModal(file.id, file.name, idx)" id="trash">
                            <img src="/images/trash.svg" width="20">
                        </a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <DeleteModal
        :showDeleteModal="showDeleteModal"
        :closeDeleteModalOutside="closeDeleteModalOutside"
        :closeDeleteModal="closeDeleteModal"
        :deleteFile="deleteFile"
        :fileIdToDelete="fileIdToDelete"
        :fileNameToDelete="fileNameToDelete" />
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
    openDeleteModal: Function,
});

</script>

<style scoped>
#trash:hover {
    cursor: pointer;
}
</style>
