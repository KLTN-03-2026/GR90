<template>
  <CrudModule :config="moduleConfig" />
</template>

<script setup>
import { computed } from 'vue';
import CrudModule from '../../components/admin/CrudModule.vue';
import { adminModules } from '../../config/adminModules';
import { useAdminAuthStore } from '../../stores/adminAuth';

const adminAuthStore = useAdminAuthStore();

const moduleConfig = computed(() => {
  const baseConfig = adminModules.quanTriViens;
  const isMaster = Number(adminAuthStore.admin?.is_master || 0) === 1;

  return {
    ...baseConfig,
    columns: (baseConfig.columns || []).filter((column) => {
      if (column.prop === 'ten_quyens' && !isMaster) {
        return false;
      }

      return true;
    }),
    formFields: (baseConfig.formFields || []).filter((field) => {
      if ((field.prop === 'is_master' || field.prop === 'quyen_ids') && !isMaster) {
        return false;
      }

      return true;
    }),
    canEditRow: (row) => isMaster || Number(row?.is_master || 0) !== 1,
    canDeleteRow: (row) => Number(row?.is_master || 0) !== 1,
  };
});
</script>
