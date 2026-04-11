<template>
  <div class="tuyen-xe-page">
    <CrudModule :config="adminModules.tuyenXes" @row-action="handleRowAction" />

    <TuyenXeRouteDetailDialog
      v-model="dialogVisible"
      :tuyen-xe-id="selectedTuyenXeId"
    />

    <TuyenXeAddChiTietDialog
      v-model="addChiTietDialogVisible"
      :tuyen-xe-id="selectedTuyenXeId"
      :tuyen-xe-name="selectedTuyenXeName"
      @created="handleCreatedChiTiet"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import CrudModule from '../../components/admin/CrudModule.vue';
import TuyenXeRouteDetailDialog from '../../components/admin/TuyenXeRouteDetailDialog.vue';
import TuyenXeAddChiTietDialog from '../../components/admin/TuyenXeAddChiTietDialog.vue';
import { adminModules } from '../../config/adminModules';

const dialogVisible = ref(false);
const addChiTietDialogVisible = ref(false);
const selectedTuyenXeId = ref(null);
const selectedTuyenXeName = ref('');

function handleRowAction({ actionKey, row }) {
  selectedTuyenXeId.value = row?.id || null;
  selectedTuyenXeName.value = row?.ten_tuyen || '';

  if (actionKey === 'view-stops') {
    dialogVisible.value = true;
    return;
  }

  if (actionKey === 'add-route-detail') {
    addChiTietDialogVisible.value = true;
    return;
  }

  ElMessage.warning('Thao tác chưa được hỗ trợ.');
}

function handleCreatedChiTiet() {
  ElMessage.success('Đã thêm mới chi tiết lộ trình cho tuyến xe.');
}
</script>
