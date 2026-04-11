<template>
  <CrudModule :config="adminModules.xeBuyts" />
</template>

<script setup>
import CrudModule from '../../components/admin/CrudModule.vue';
import { adminModules } from '../../config/adminModules';
</script>
