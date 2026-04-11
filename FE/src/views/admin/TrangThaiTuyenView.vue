<template>
  <CrudModule :config="adminModules.trangThaiTuyens" />
</template>

<script setup>
import CrudModule from '../../components/admin/CrudModule.vue';
import { adminModules } from '../../config/adminModules';
</script>
