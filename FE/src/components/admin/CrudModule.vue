<template>
  <section class="module-page">
    <div class="module-head">
      <div>
        <h2>{{ config.title }}</h2>
        <p>{{ config.description || `Qu\u1ea3n l\u00fd d\u1eef li\u1ec7u module ${config.title.toLowerCase()} v\u00e0 \u0111\u1ed3ng b\u1ed9 tr\u1ef1c ti\u1ebfp v\u1edbi API.` }}</p>
      </div>
      <div class="module-actions">
        <el-button type="primary" @click="openCreate">Th&#234;m m&#7899;i</el-button>
        <el-button @click="fetchList">T&#7843;i l&#7841;i</el-button>
      </div>
    </div>

    <el-card shadow="never" class="module-card">
      <el-table
        :data="rows"
        v-loading="loading"
        border
        class="fancy-table"
        empty-text="Kh&#244;ng c&#243; d&#7919; li&#7879;u"
        :header-cell-style="{ textAlign: 'center' }"
      >
        <el-table-column label="STT" width="80" align="center" header-align="center">
          <template #default="scope">
            {{ getRowNumber(scope.$index) }}
          </template>
        </el-table-column>

        <el-table-column
          v-for="col in visibleColumns"
          :key="col.prop"
          :label="col.label"
          :prop="col.prop"
          :width="col.width"
          :min-width="col.minWidth"
          :align="getColumnAlign(col)"
          header-align="center"
          show-overflow-tooltip
        >
          <template #default="scope">
            <el-button
              v-if="shouldRenderCellButton(col)"
              :type="getCellButtonType(scope.row, col)"
              size="small"
              class="table-cell-button"
              :style="getCellButtonStyle(col)"
            >
              {{ formatCellValue(scope.row, col) }}
            </el-button>
            <template v-else>
              {{ formatCellValue(scope.row, col) }}
            </template>
          </template>
        </el-table-column>

        <el-table-column
          label="Thao t&#225;c"
          fixed="right"
          :width="actionColumnWidth"
          align="center"
          header-align="center"
          class-name="action-col"
        >
          <template #default="scope">
            <el-dropdown
              v-if="getRowActions(scope.row).length"
              trigger="click"
              @command="(command) => handleActionCommand(command, scope.row)"
            >
              <el-button class="action-btn" size="small" type="primary" plain>
                Thao t&#225;c
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item
                    v-for="action in getRowActions(scope.row)"
                    :key="`${action.key}-${scope.row.id}`"
                    :command="action.command"
                    :divided="action.divided"
                  >
                    {{ action.label }}
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
            <el-tag v-else type="info" effect="plain">Kh&#244;ng kh&#7843; d&#7909;ng</el-tag>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-wrap">
        <el-pagination
          background
          layout="total, sizes, prev, pager, next, jumper"
          :total="pagination.total"
          :current-page="pagination.page"
          :page-size="pagination.perPage"
          :page-sizes="[10, 20, 50, 100]"
          @current-change="onPageChange"
          @size-change="onPageSizeChange"
        />
      </div>
    </el-card>

    <el-dialog
      v-model="dialog.visible"
      :title="dialog.mode === 'create' ? `Th\u00eam ${config.title}` : `C\u1eadp nh\u1eadt ${config.title}`"
      width="780px"
    >
      <el-form label-width="220px" :model="form" class="module-form">
        <el-form-item
          v-for="field in activeFormFields"
          :key="field.prop"
          :label="field.label"
        >
          <el-input
            v-if="field.type === 'password'"
            v-model="form[field.prop]"
            type="password"
            :placeholder="field.placeholder || `Nh\u1eadp ${field.label.toLowerCase()}`"
            :show-password="true"
            autocomplete="new-password"
          />

          <el-input
            v-else-if="field.type === 'text'"
            v-model="form[field.prop]"
            type="text"
            :placeholder="field.placeholder || `Nh\u1eadp ${field.label.toLowerCase()}`"
          />

          <el-input
            v-else-if="field.type === 'number'"
            v-model.number="form[field.prop]"
            type="number"
            :placeholder="field.placeholder || `Nh\u1eadp ${field.label.toLowerCase()}`"
          />

          <el-input
            v-else-if="field.type === 'textarea'"
            v-model="form[field.prop]"
            type="textarea"
            :rows="3"
            :placeholder="field.placeholder || `Nh\u1eadp ${field.label.toLowerCase()}`"
          />

          <el-select
            v-else-if="field.type === 'select'"
            v-model="form[field.prop]"
            clearable
            filterable
            style="width: 100%"
            :multiple="Boolean(field.multiple)"
            :collapse-tags="Boolean(field.multiple)"
            :collapse-tags-tooltip="Boolean(field.multiple)"
            :multiple-limit="field.multipleLimit || 0"
            :placeholder="field.placeholder || `Ch\u1ecdn ${field.label.toLowerCase()}`"
          >
            <el-option
              v-for="opt in getFieldOptions(field)"
              :key="`${field.prop}-${opt.value}`"
              :label="opt.label"
              :value="opt.value"
            />
          </el-select>

          <el-input
            v-else
            v-model="form[field.prop]"
            :placeholder="field.placeholder || `Nh\u1eadp ${field.label.toLowerCase()}`"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialog.visible = false">H&#7911;y</el-button>
        <el-button type="primary" :loading="saving" @click="submitForm">L&#432;u</el-button>
      </template>
    </el-dialog>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { ElMessageBox } from 'element-plus';
import { apiClient, getValidationErrors, unwrapResponse } from '../../services/api';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const props = defineProps({
  config: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['row-action']);

const rows = ref([]);
const loading = ref(false);
const saving = ref(false);
const form = reactive({});
const selectOptions = reactive({});

const pagination = reactive({
  page: 1,
  perPage: 20,
  total: 0,
});

const dialog = reactive({
  visible: false,
  mode: 'create',
  rowId: null,
});

const visibleColumns = computed(() => (props.config.columns || []).filter((col) => col.prop !== 'id'));
const activeFormFields = computed(() => (props.config.formFields || []).filter((field) => {
  if (dialog.mode === 'create' && field.showOnCreate === false) {
    return false;
  }

  if (dialog.mode === 'update' && field.showOnUpdate === false) {
    return false;
  }

  return true;
}));
const extraActions = computed(() => props.config.extraActions || []);
const actionColumnWidth = computed(() => (extraActions.value.length > 0 ? 190 : 160));

function getValue(obj, path) {
  if (!path.includes('.')) {
    return obj[path] ?? '';
  }

  return path.split('.').reduce((acc, cur) => (acc ? acc[cur] : ''), obj) ?? '';
}

function getColumnAlign(col) {
  if (col?.align) {
    return col.align;
  }

  if (shouldRenderCellButton(col)) {
    return 'center';
  }

  const prop = col?.prop || '';
  const label = (col?.label || '').toLowerCase();
  const isNumericField = /(count|tong|total|so_|thu_tu|vi_do|kinh_do|gia|id$)/i.test(prop)
    || /(s\u1ed1|th\u1ee9 t\u1ef1|v\u0129 \u0111\u1ed9|kinh \u0111\u1ed9|gi\u00e1|id)/i.test(label);

  return isNumericField ? 'center' : 'left';
}

function isNumericColumn(col) {
  return getColumnAlign(col) === 'center';
}

function toNumber(value) {
  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : null;
  }

  if (typeof value === 'string') {
    const normalized = value.replace(/,/g, '').trim();
    if (/^-?\d+(\.\d+)?$/.test(normalized)) {
      const parsed = Number(normalized);
      return Number.isFinite(parsed) ? parsed : null;
    }
  }

  return null;
}

function formatNumberValue(num, col) {
  const prop = col?.prop || '';
  const label = (col?.label || '').toLowerCase();

  if (/(vi_do|kinh_do)/i.test(prop) || /(v\u0129 \u0111\u1ed9|kinh \u0111\u1ed9)/i.test(label)) {
    return new Intl.NumberFormat('vi-VN', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 6,
    }).format(num);
  }

  if (/(gia|tien|so_tien)/i.test(prop) || /(gi\u00e1|s\u1ed1 ti\u1ec1n)/i.test(label)) {
    return `${new Intl.NumberFormat('vi-VN', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(num)} \u0111`;
  }

  return new Intl.NumberFormat('vi-VN', {
    minimumFractionDigits: Number.isInteger(num) ? 0 : 2,
    maximumFractionDigits: Number.isInteger(num) ? 0 : 2,
  }).format(num);
}

function mapColumnValue(col, raw, row) {
  if (typeof col?.format === 'function') {
    return col.format(raw, row);
  }

  if (col?.valueMap && typeof col.valueMap === 'object') {
    if (Object.prototype.hasOwnProperty.call(col.valueMap, raw)) {
      return col.valueMap[raw];
    }

    const normalizedKey = String(raw);
    if (Object.prototype.hasOwnProperty.call(col.valueMap, normalizedKey)) {
      return col.valueMap[normalizedKey];
    }
  }

  return raw;
}

function isStatusColumn(col) {
  if (col?.prop === 'trang_thai') {
    return true;
  }

  // Only treat the real "Trạng thái" column as a status badge.
  // Avoid matching columns like "Mã trạng thái" / "Tên trạng thái".
  const normalizedLabel = normalizeVietnameseText(col?.label || '');
  return normalizedLabel === 'trang thai';
}

function normalizeVietnameseText(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\u0111/g, 'd')
    .replace(/\u0110/g, 'D')
    .toLowerCase()
    .trim();
}

function shouldRenderCellButton(col) {
  return col?.cellType === 'button' || isStatusColumn(col);
}

function getCellButtonType(row, col) {
  const raw = getValue(row, col.prop);
  const typeMap = col?.buttonTypeMap;

  if (typeMap && typeof typeMap === 'object') {
    if (Object.prototype.hasOwnProperty.call(typeMap, raw)) {
      return typeMap[raw];
    }

    const normalizedKey = String(raw);
    if (Object.prototype.hasOwnProperty.call(typeMap, normalizedKey)) {
      return typeMap[normalizedKey];
    }
  }

  if (isStatusColumn(col)) {
    if (typeof raw === 'number' || raw === '0' || raw === '1') {
      return Number(raw) === 1 ? 'success' : 'danger';
    }

    const displayValue = mapColumnValue(col, raw, row);
    const normalizedValue = normalizeVietnameseText(displayValue);

    if (normalizedValue.includes('dang hoat dong') || normalizedValue === 'hoat dong') {
      return 'success';
    }

    if (normalizedValue.includes('tam dung')) {
      return 'warning';
    }

    if (normalizedValue.includes('thu tuc van hanh')) {
      return 'primary';
    }

    if (normalizedValue.includes('bi khoa') || normalizedValue.includes('ngung')) {
      return 'danger';
    }

    return 'info';
  }

  return col?.buttonType || 'primary';
}

function getCellButtonStyle(col) {
  if (col?.buttonWidth) {
    return {
      width: col.buttonWidth,
    };
  }

  // Keep buttons compact and consistent across tables.
  if (isStatusColumn(col)) {
    return {
      width: 'min(210px, 100%)',
    };
  }

  return {
    width: 'min(140px, 100%)',
  };
}

function formatCellValue(row, col) {
  const raw = getValue(row, col.prop);
  if (raw === null || raw === undefined || raw === '') {
    return '';
  }

  const mappedValue = mapColumnValue(col, raw, row);

  if (Array.isArray(mappedValue)) {
    return mappedValue.join(', ');
  }

  if (mappedValue !== raw) {
    return mappedValue;
  }

  if (isNumericColumn(col)) {
    const num = toNumber(raw);
    if (num !== null) {
      return formatNumberValue(num, col);
    }
  }

  return raw;
}

function getRowNumber(index) {
  return (pagination.page - 1) * pagination.perPage + index + 1;
}

function emitRowAction(actionKey, row) {
  emit('row-action', { actionKey, row });
}

function canEditRow(row) {
  if (typeof props.config.canEditRow === 'function') {
    return props.config.canEditRow(row) !== false;
  }

  return true;
}

function canDeleteRow(row) {
  if (typeof props.config.canDeleteRow === 'function') {
    return props.config.canDeleteRow(row) !== false;
  }

  return true;
}

function getVisibleExtraActions(row) {
  return extraActions.value.filter((action) => {
    if (typeof action.show === 'function') {
      return action.show(row) !== false;
    }

    return true;
  });
}

function getRowActions(row) {
  const actions = getVisibleExtraActions(row).map((action) => ({
    key: action.key,
    label: action.label,
    command: `extra:${action.key}`,
    divided: false,
  }));

  if (canEditRow(row)) {
    actions.push({
      key: 'edit',
      label: 'S\u1eeda',
      command: 'edit',
      divided: false,
    });
  }

  if (canDeleteRow(row)) {
    actions.push({
      key: 'delete',
      label: 'X\u00f3a',
      command: 'delete',
      divided: actions.length > 0,
    });
  }

  return actions;
}

async function handleActionCommand(command, row) {
  if (typeof command !== 'string') {
    return;
  }

  if (command === 'edit') {
    openEdit(row);
    return;
  }

  if (command === 'delete') {
    try {
      await ElMessageBox.confirm('B\u1ea1n ch\u1eafc ch\u1eafn mu\u1ed1n x\u00f3a?', 'X\u00e1c nh\u1eadn', {
        confirmButtonText: 'X\u00f3a',
        cancelButtonText: 'H\u1ee7y',
        type: 'warning',
      });
      await removeRow(row);
    } catch (_) {
      // Nguoi dung huy thao tac.
    }
    return;
  }

  if (command.startsWith('extra:')) {
    const actionKey = command.slice('extra:'.length);
    if (actionKey) {
      emitRowAction(actionKey, row);
    }
  }
}

function sanitizePayload(payload) {
  const cleaned = {};
  Object.keys(payload).forEach((key) => {
    const val = payload[key];
    if (val === '' || val === undefined) {
      return;
    }
    cleaned[key] = val;
  });

  return cleaned;
}

function buildEmptyForm() {
  const next = {};
  activeFormFields.value.forEach((field) => {
    if (field.multiple) {
      next[field.prop] = Array.isArray(field.defaultValue) ? [...field.defaultValue] : [];
      return;
    }

    next[field.prop] = field.defaultValue ?? '';
  });
  return next;
}

function applyForm(values) {
  const model = buildEmptyForm();
  Object.assign(model, values || {});
  Object.keys(form).forEach((key) => {
    if (!Object.prototype.hasOwnProperty.call(model, key)) {
      delete form[key];
    }
  });
  Object.keys(model).forEach((key) => {
    form[key] = model[key];
  });
}

async function loadSelectOptions() {
  const dynamicFields = activeFormFields.value.filter((field) => field.optionsEndpoint);

  for (const field of dynamicFields) {
    try {
      const response = await apiClient.get(field.optionsEndpoint);
      const payload = unwrapResponse(response);
      const dataRows = Array.isArray(payload?.data) ? payload.data : [];
      selectOptions[field.prop] = dataRows.map((item) => ({
        label: typeof field.optionFormatter === 'function'
          ? field.optionFormatter(item)
          : (getValue(item, field.optionLabel) || `#${item.id}`),
        value: item[field.optionValue] ?? item.id,
      }));
    } catch (error) {
      selectOptions[field.prop] = [];
      await showAlert({
        icon: 'warning',
        title: 'Kh\u00f4ng t\u1ea3i duoc danh s\u00e1ch',
        text: `Kh\u00f4ng t\u1ea3i duoc du lieu cho truong ${field.label}.`,
        toast: true,
      });
    }
  }
}

function getFieldOptions(field) {
  if (field.options) {
    return field.options;
  }
  if (field.optionsEndpoint) {
    return selectOptions[field.prop] || [];
  }
  return [];
}

async function fetchList() {
  loading.value = true;
  try {
    const response = await apiClient.get(props.config.endpoint, {
      params: {
        page: pagination.page,
        per_page: pagination.perPage,
      },
    });

    const payload = unwrapResponse(response);
    rows.value = Array.isArray(payload?.data) ? payload.data : [];
    pagination.total = Number(payload?.total || rows.value.length || 0);
    pagination.perPage = Number(payload?.per_page || pagination.perPage || 20);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'L\u1ed7i t\u1ea3i d\u1eef li\u1ec7u',
      text: extractFirstErrorMessage(error, 'Kh\u00f4ng th\u1ec3 t\u1ea3i d\u1eef li\u1ec7u tu API.'),
    });
  } finally {
    loading.value = false;
  }
}

function onPageChange(page) {
  pagination.page = page;
  fetchList();
}

function onPageSizeChange(size) {
  pagination.perPage = size;
  pagination.page = 1;
  fetchList();
}

function openCreate() {
  dialog.mode = 'create';
  dialog.rowId = null;
  applyForm({});
  dialog.visible = true;
}

function openEdit(row) {
  dialog.mode = 'update';
  dialog.rowId = row.id;
  applyForm(row);
  dialog.visible = true;
}

async function submitForm() {
  saving.value = true;
  try {
    const payload = sanitizePayload(form);
    const isCreate = dialog.mode === 'create';

    if (isCreate) {
      await apiClient.post(props.config.endpoint, payload);
      await showAlert({
        icon: 'success',
        title: 'T\u1ea1o th\u00e0nh c\u00f4ng',
        text: 'D\u1eef li\u1ec7u da duoc them moi.',
        toast: true,
      });
    } else {
      await apiClient.put(`${props.config.endpoint}/${dialog.rowId}`, payload);
      await showAlert({
        icon: 'success',
        title: 'C\u1eadp nh\u1eadt th\u00e0nh c\u00f4ng',
        text: 'D\u1eef li\u1ec7u da duoc luu.',
        toast: true,
      });
    }

    dialog.visible = false;
    await fetchList();
  } catch (error) {
    const validationErrors = getValidationErrors(error);
    const detail = validationErrors
      ? Object.values(validationErrors).flat().filter(Boolean).join('\n')
      : extractFirstErrorMessage(error, 'Kh\u00f4ng th\u1ec3 l\u01b0u d\u1eef li\u1ec7u.');

    await showAlert({
      icon: 'error',
      title: 'Kh\u00f4ng th\u1ec3 l\u01b0u d\u1eef li\u1ec7u',
      text: detail,
    });
  } finally {
    saving.value = false;
  }
}

async function removeRow(row) {
  try {
    await apiClient.delete(`${props.config.endpoint}/${row.id}`, {
      data: {
        id: row.id,
      },
    });

    await showAlert({
      icon: 'success',
      title: 'X\u00f3a th\u00e0nh c\u00f4ng',
      text: 'D\u1eef li\u1ec7u da duoc xoa.',
      toast: true,
    });
    await fetchList();
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Kh\u00f4ng th\u1ec3 x\u00f3a d\u1eef li\u1ec7u',
      text: extractFirstErrorMessage(error, 'Thao t\u00e1c x\u00f3a th\u1ea5t b\u1ea1i.'),
    });
  }
}

watch(
  () => activeFormFields.value.map((field) => `${field.prop}:${field.optionsEndpoint || ''}:${field.multiple ? '1' : '0'}`).join('|'),
  async () => {
    await loadSelectOptions();
  },
  { immediate: true }
);

onMounted(() => {
  applyForm({});
  fetchList();
});
</script>

<style scoped>
.module-page {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.module-head {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  align-items: center;
}

.module-head h2 {
  margin: 0;
  font-size: 24px;
  line-height: 1.1;
}

.module-head p {
  margin: 4px 0 0;
  color: #6f6a64;
  font-size: 13px;
}

.module-actions {
  display: flex;
  gap: 8px;
}

.module-card {
  border-radius: 14px;
}

.fancy-table {
  --el-table-border-color: #f0d9c0;
}

.module-card :deep(.el-table__header-wrapper th) {
  background: linear-gradient(180deg, #ffe8d2 0%, #ffdcb8 100%);
  color: #5e3115;
  font-weight: 700;
}

.module-card :deep(.el-table__body td) {
  transition: background-color 0.15s ease;
}

.module-card :deep(.el-table__body tr:nth-child(odd) > td) {
  background: #fffdf9;
}

.module-card :deep(.el-table__body tr:nth-child(even) > td) {
  background: #fff8f0;
}

.module-card :deep(.el-table__body tr:hover > td) {
  background: #ffeedc !important;
}

.pagination-wrap {
  margin-top: 14px;
  display: flex;
  justify-content: flex-end;
}

.module-card :deep(.action-col .cell) {
  white-space: nowrap;
}

.module-card :deep(.action-btn > span) {
  white-space: nowrap;
}

.table-cell-button {
  justify-content: center;
  pointer-events: none;
  white-space: nowrap;
}

@media (max-width: 900px) {
  .module-head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
