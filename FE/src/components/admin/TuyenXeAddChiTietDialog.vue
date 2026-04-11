<template>
  <el-dialog
    :model-value="modelValue"
    width="760px"
    top="6vh"
    :close-on-click-modal="false"
    @update:model-value="(val) => emit('update:modelValue', val)"
    @closed="onClosed"
  >
    <template #header>
      <div class="dialog-head">
        <h3>Thêm Chi Tiết Lộ Trình</h3>
        <p>{{ subtitle }}</p>
      </div>
    </template>

    <el-form :model="form" label-width="220px" class="detail-form">
      <el-form-item label="Chiều" required>
        <el-select v-model="form.chieu" style="width: 100%" placeholder="Chọn chiều">
          <el-option label="di" value="di" />
          <el-option label="ve" value="ve" />
        </el-select>
      </el-form-item>

      <el-form-item label="Thứ tự dừng" required>
        <el-input v-model.number="form.thu_tu_dung" type="number" min="1" placeholder="Nhập thứ tự dừng" />
      </el-form-item>

      <el-form-item label="Trạm xe" required>
        <el-select
          v-model="form.tram_xe_id"
          filterable
          clearable
          :loading="loading"
          style="width: 100%"
          placeholder="Chọn trạm"
          @change="onStationChange"
        >
          <el-option
            v-for="item in tramOptions"
            :key="item.id"
            :label="`${item.ma_tram || 'N/A'} - ${item.ten_tram || 'Không tên'}`"
            :value="item.id"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Tên điểm đi qua">
        <el-input v-model="form.ten_diem_di_qua" placeholder="Tên điểm đi qua" />
      </el-form-item>

      <el-form-item label="TG dừng dự kiến (giây)">
        <el-input v-model.number="form.thoi_gian_dung_du_kien_giay" type="number" min="0" placeholder="Ví dụ: 30" />
      </el-form-item>

      <el-form-item label="Khoảng cách từ điểm trước (m)">
        <el-input v-model.number="form.khoang_cach_tu_diem_truoc_met" type="number" min="0" placeholder="Ví dụ: 250" />
      </el-form-item>
    </el-form>

    <template #footer>
      <el-button @click="emit('update:modelValue', false)">Hủy</el-button>
      <el-button type="primary" :loading="saving" @click="submit">Lưu chi tiết</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { ElMessage, ElNotification } from 'element-plus';
import { apiClient, getErrorMessage, unwrapResponse } from '../../services/api';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  tuyenXeId: {
    type: [Number, String],
    default: null,
  },
  tuyenXeName: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'created']);

const loading = ref(false);
const saving = ref(false);
const tramOptions = ref([]);
const loTrinhByDirection = ref({ di: null, ve: null });

const form = reactive({
  chieu: 'di',
  thu_tu_dung: null,
  tram_xe_id: null,
  ten_diem_di_qua: '',
  thoi_gian_dung_du_kien_giay: null,
  khoang_cach_tu_diem_truoc_met: null,
});

const subtitle = computed(() => {
  const name = props.tuyenXeName || 'Tuyến xe';
  return name;
});

function resetForm() {
  form.chieu = 'di';
  form.thu_tu_dung = null;
  form.tram_xe_id = null;
  form.ten_diem_di_qua = '';
  form.thoi_gian_dung_du_kien_giay = null;
  form.khoang_cach_tu_diem_truoc_met = null;
}

async function loadDependencies() {
  if (!props.tuyenXeId) {
    return;
  }

  loading.value = true;
  try {
    const [tramsRes, tuyenDetailRes] = await Promise.all([
      apiClient.get('/admin/danh-muc/tram-xes', { params: { page: 1, per_page: 200 } }),
      apiClient.get(`/admin/van-hanh/tuyen-xes/${props.tuyenXeId}`),
    ]);

    const tramPayload = unwrapResponse(tramsRes);
    const tuyenPayload = unwrapResponse(tuyenDetailRes);

    const loTrinhs = Array.isArray(tuyenPayload?.lo_trinh_tuyens) ? tuyenPayload.lo_trinh_tuyens : [];
    const routeStations = loTrinhs
      .flatMap((loTrinh) => (Array.isArray(loTrinh?.chi_tiet_lo_trinhs) ? loTrinh.chi_tiet_lo_trinhs : []))
      .map((item) => item?.tram_xe)
      .filter((tram) => tram && Number(tram.id) > 0);

    const firstPageTrams = Array.isArray(tramPayload?.data) ? tramPayload.data : [];

    const tramMap = new Map();
    [...routeStations, ...firstPageTrams].forEach((tram) => {
      const id = Number(tram?.id || 0);
      if (id > 0 && !tramMap.has(id)) {
        tramMap.set(id, tram);
      }
    });
    tramOptions.value = Array.from(tramMap.values());

    loTrinhByDirection.value = {
      di: loTrinhs.find((item) => item?.chieu === 'di') || null,
      ve: loTrinhs.find((item) => item?.chieu === 've') || null,
    };
  } catch (error) {
    tramOptions.value = [];
    loTrinhByDirection.value = { di: null, ve: null };
    ElNotification.error({
      title: 'Không tải được dữ liệu phụ trợ',
      message: getErrorMessage(error),
    });
  } finally {
    loading.value = false;
  }
}

async function ensureLoTrinh(direction) {
  const existing = loTrinhByDirection.value[direction];
  if (existing?.id) {
    return existing.id;
  }

  const moTa = `${props.tuyenXeName || 'Tuyến xe'} - chiều ${direction === 've' ? 'về' : 'đi'}`;
  const response = await apiClient.post('/admin/van-hanh/lo-trinh-tuyens', {
    tuyen_xe_id: Number(props.tuyenXeId),
    chieu: direction,
    mo_ta_lo_trinh: moTa,
  });

  const created = unwrapResponse(response);
  const loTrinhId = Number(created?.id || 0);
  if (loTrinhId > 0) {
    loTrinhByDirection.value[direction] = created;
    return loTrinhId;
  }

  throw new Error('Không thể tạo lộ trình cho tuyến.');
}

function sanitizeNumber(value) {
  if (value === '' || value === null || value === undefined) {
    return null;
  }

  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : null;
}

function onStationChange(stationId) {
  if (!stationId || form.ten_diem_di_qua) {
    return;
  }

  const station = tramOptions.value.find((item) => Number(item.id) === Number(stationId));
  if (station?.ten_tram) {
    form.ten_diem_di_qua = station.ten_tram;
  }
}

async function submit() {
  if (!props.tuyenXeId) {
    ElMessage.warning('Không xác định được tuyến xe.');
    return;
  }

  if (!form.chieu || !sanitizeNumber(form.thu_tu_dung) || !sanitizeNumber(form.tram_xe_id)) {
    ElMessage.warning('Vui lòng nhập đủ Chiều, Thứ tự dừng và Trạm xe.');
    return;
  }

  saving.value = true;
  try {
    const loTrinhId = await ensureLoTrinh(form.chieu);

    await apiClient.post('/admin/van-hanh/chi-tiet-lo-trinhs', {
      lo_trinh_tuyen_id: loTrinhId,
      tram_xe_id: sanitizeNumber(form.tram_xe_id),
      thu_tu_dung: sanitizeNumber(form.thu_tu_dung),
      ten_diem_di_qua: form.ten_diem_di_qua || null,
      thoi_gian_dung_du_kien_giay: sanitizeNumber(form.thoi_gian_dung_du_kien_giay),
      khoang_cach_tu_diem_truoc_met: sanitizeNumber(form.khoang_cach_tu_diem_truoc_met),
    });

    ElMessage.success('Thêm chi tiết lộ trình thành công.');
    emit('created');
    emit('update:modelValue', false);
  } catch (error) {
    ElNotification.error({
      title: 'Không thể thêm chi tiết lộ trình',
      message: getErrorMessage(error),
      duration: 4500,
    });
  } finally {
    saving.value = false;
  }
}

function onClosed() {
  resetForm();
  loTrinhByDirection.value = { di: null, ve: null };
}

watch(
  () => props.modelValue,
  async (visible) => {
    if (visible) {
      resetForm();
      await loadDependencies();
    }
  }
);
</script>

<style scoped>
.dialog-head h3 {
  margin: 0;
  font-size: 20px;
}

.dialog-head p {
  margin: 4px 0 0;
  color: #6f6a64;
}

.detail-form {
  padding-top: 6px;
}
</style>
