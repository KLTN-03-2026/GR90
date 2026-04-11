<template>
  <CrudModule :config="adminModules.loaiTuyens" />
</template>

<script setup>
import CrudModule from '../../components/admin/CrudModule.vue';
import { adminModules } from '../../config/adminModules';
</script>
