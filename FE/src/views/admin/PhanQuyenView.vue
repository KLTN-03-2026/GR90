<template>
  <section class="permission-page">
    <div class="permission-head">
      <div>
        <h2>Quản lý quyền</h2>
        <p>Quản lý nhóm quyền và gán chức năng cho từng quyền trong cùng một màn hình.</p>
      </div>
      <div class="permission-actions">
        <el-button type="primary" @click="openCreate">Thêm mới</el-button>
        <el-button @click="fetchPermissions">Tải lại</el-button>
      </div>
    </div>

    <el-card shadow="never" class="permission-card">
      <el-table
        :data="rows"
        v-loading="loading"
        border
        empty-text="Không có dữ liệu"
        :header-cell-style="{ textAlign: 'center' }"
      >
        <el-table-column label="STT" width="80" align="center" header-align="center">
          <template #default="scope">
            {{ getRowNumber(scope.$index) }}
          </template>
        </el-table-column>
        <el-table-column prop="ten_quyen" label="Tên quyền" min-width="220" header-align="center" />
        <el-table-column prop="mo_ta" label="Mô tả" min-width="260" header-align="center" show-overflow-tooltip />
        <el-table-column label="Chức năng" min-width="340" header-align="center">
          <template #default="scope">
            <div class="function-tags">
              <el-tag
                v-for="name in (scope.row.ten_chuc_nangs || []).slice(0, 4)"
                :key="`${scope.row.id}-${name}`"
                size="small"
                effect="plain"
              >
                {{ name }}
              </el-tag>
              <el-tag
                v-if="(scope.row.ten_chuc_nangs || []).length > 4"
                size="small"
                type="info"
                effect="plain"
              >
                +{{ (scope.row.ten_chuc_nangs || []).length - 4 }}
              </el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="chuc_nangs_count" label="Số chức năng" width="130" align="center" header-align="center" />
        <el-table-column prop="quan_tri_viens_count" label="Đang gán" width="110" align="center" header-align="center" />
        <el-table-column label="Thao tác" width="180" align="center" header-align="center">
          <template #default="scope">
            <div class="row-actions">
              <el-button size="small" type="primary" plain @click="openEdit(scope.row)">Sửa</el-button>
              <el-button size="small" type="danger" plain @click="removePermission(scope.row)">Xóa</el-button>
            </div>
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
      :title="dialog.mode === 'create' ? 'Thêm nhóm quyền' : 'Cập nhật nhóm quyền'"
      width="860px"
    >
      <el-form label-width="150px" class="permission-form">
        <el-form-item label="Tên quyền">
          <el-input v-model="form.ten_quyen" placeholder="Nhập tên quyền" />
        </el-form-item>

        <el-form-item label="Mô tả">
          <el-input v-model="form.mo_ta" type="textarea" :rows="3" placeholder="Nhập mô tả quyền" />
        </el-form-item>

        <el-form-item label="Chức năng">
          <el-select
            v-model="form.chuc_nang_ids"
            multiple
            filterable
            collapse-tags
            collapse-tags-tooltip
            style="width: 100%"
            placeholder="Chọn chức năng cho nhóm quyền"
          >
            <el-option-group
              v-for="group in functionGroups"
              :key="group.label"
              :label="group.label"
            >
              <el-option
                v-for="item in group.options"
                :key="item.id"
                :label="item.label"
                :value="item.id"
              >
                <div class="function-option">
                  <span>{{ item.label }}</span>
                  <small>{{ item.http_method }} {{ item.uri }}</small>
                </div>
              </el-option>
            </el-option-group>
          </el-select>
        </el-form-item>

        <el-form-item label="Đã chọn">
          <div class="selected-tags">
            <el-tag
              v-for="name in selectedFunctionNames"
              :key="name"
              size="small"
              effect="plain"
            >
              {{ name }}
            </el-tag>
            <span v-if="selectedFunctionNames.length === 0" class="empty-selected">
              Chưa chọn chức năng nào
            </span>
          </div>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialog.visible = false">Hủy</el-button>
        <el-button type="primary" :loading="saving" @click="submitForm">Lưu</el-button>
      </template>
    </el-dialog>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { ElMessageBox } from 'element-plus';
import { apiClient, getValidationErrors, unwrapResponse } from '../../services/api';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const rows = ref([]);
const functionGroups = ref([]);
const loading = ref(false);
const saving = ref(false);

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

const form = reactive({
  ten_quyen: '',
  mo_ta: '',
  chuc_nang_ids: [],
});

const flatFunctionOptions = computed(() => functionGroups.value.flatMap((group) => group.options || []));
const selectedFunctionNames = computed(() => {
  const selectedIds = new Set((form.chuc_nang_ids || []).map((id) => Number(id)));
  return flatFunctionOptions.value
    .filter((item) => selectedIds.has(Number(item.id)))
    .map((item) => item.label);
});

function resetForm() {
  form.ten_quyen = '';
  form.mo_ta = '';
  form.chuc_nang_ids = [];
}

function applyForm(row = {}) {
  form.ten_quyen = row.ten_quyen || '';
  form.mo_ta = row.mo_ta || '';
  form.chuc_nang_ids = Array.isArray(row.chuc_nang_ids) ? [...row.chuc_nang_ids] : [];
}

function getRowNumber(index) {
  return (pagination.page - 1) * pagination.perPage + index + 1;
}

async function fetchPermissions() {
  loading.value = true;

  try {
    const response = await apiClient.get('/admin/tai-khoan/phan-quyens', {
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
      title: 'Không thể tải dữ liệu',
      text: extractFirstErrorMessage(error, 'Tải danh sách nhóm quyền thất bại.'),
    });
  } finally {
    loading.value = false;
  }
}

async function fetchFunctionGroups() {
  try {
    const response = await apiClient.get('/admin/tai-khoan/phan-quyens/chuc-nang-options');
    const payload = unwrapResponse(response);
    functionGroups.value = Array.isArray(payload?.data) ? payload.data : [];
  } catch (error) {
    functionGroups.value = [];
    await showAlert({
      icon: 'error',
      title: 'Không thể tải chức năng',
      text: extractFirstErrorMessage(error, 'Tải danh sách chức năng thất bại.'),
    });
  }
}

function openCreate() {
  dialog.mode = 'create';
  dialog.rowId = null;
  resetForm();
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
    const payload = {
      ten_quyen: form.ten_quyen,
      mo_ta: form.mo_ta,
      chuc_nang_ids: form.chuc_nang_ids,
    };

    if (dialog.mode === 'create') {
      await apiClient.post('/admin/tai-khoan/phan-quyens', payload);
      await showAlert({
        icon: 'success',
        title: 'Tạo thành công',
        text: 'Nhóm quyền đã được thêm mới.',
        toast: true,
      });
    } else {
      await apiClient.put(`/admin/tai-khoan/phan-quyens/${dialog.rowId}`, payload);
      await showAlert({
        icon: 'success',
        title: 'Cập nhật thành công',
        text: 'Nhóm quyền đã được lưu.',
        toast: true,
      });
    }

    dialog.visible = false;
    await fetchPermissions();
  } catch (error) {
    const validationErrors = getValidationErrors(error);
    const detail = validationErrors
      ? Object.values(validationErrors).flat().filter(Boolean).join('\n')
      : extractFirstErrorMessage(error, 'Không thể lưu nhóm quyền.');

    await showAlert({
      icon: 'error',
      title: 'Không thể lưu nhóm quyền',
      text: detail,
    });
  } finally {
    saving.value = false;
  }
}

async function removePermission(row) {
  try {
    await ElMessageBox.confirm(
      `Bạn chắc chắn muốn xóa nhóm quyền "${row.ten_quyen}"?`,
      'Xác nhận',
      {
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        type: 'warning',
      }
    );

    await apiClient.delete(`/admin/tai-khoan/phan-quyens/${row.id}`, {
      data: { id: row.id },
    });

    await showAlert({
      icon: 'success',
      title: 'Xóa thành công',
      text: 'Nhóm quyền đã được xóa.',
      toast: true,
    });

    await fetchPermissions();
  } catch (error) {
    if (error === 'cancel' || error === 'close' || error?.message === 'cancel') {
      return;
    }

    await showAlert({
      icon: 'error',
      title: 'Không thể xóa nhóm quyền',
      text: extractFirstErrorMessage(error, 'Xóa nhóm quyền thất bại.'),
    });
  }
}

function onPageChange(page) {
  pagination.page = page;
  fetchPermissions();
}

function onPageSizeChange(size) {
  pagination.perPage = size;
  pagination.page = 1;
  fetchPermissions();
}

onMounted(async () => {
  await fetchFunctionGroups();
  await fetchPermissions();
});
</script>

<style scoped>
.permission-page {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.permission-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
}

.permission-head h2 {
  margin: 0;
  font-size: 24px;
  line-height: 1.1;
}

.permission-head p {
  margin: 4px 0 0;
  color: #6f6a64;
  font-size: 13px;
}

.permission-actions {
  display: flex;
  gap: 8px;
}

.permission-card {
  border-radius: 14px;
}

.permission-card :deep(.el-table__header-wrapper th) {
  background: linear-gradient(180deg, #ffe8d2 0%, #ffdcb8 100%);
  color: #5e3115;
  font-weight: 700;
}

.permission-card :deep(.el-table__body tr:nth-child(odd) > td) {
  background: #fffdf9;
}

.permission-card :deep(.el-table__body tr:nth-child(even) > td) {
  background: #fff8f0;
}

.permission-card :deep(.el-table__body tr:hover > td) {
  background: #ffeedc !important;
}

.function-tags,
.selected-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.row-actions {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.function-option {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.function-option small {
  color: #8e796b;
  font-size: 11px;
}

.empty-selected {
  color: #8e796b;
  font-size: 13px;
}

.pagination-wrap {
  margin-top: 14px;
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 960px) {
  .permission-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .row-actions {
    flex-direction: column;
  }
}
</style>
