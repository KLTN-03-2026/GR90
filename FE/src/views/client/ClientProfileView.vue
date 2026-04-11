<template>
  <section class="client-page">
    <div class="client-container">
      <div class="page-head">
        <div>
          <span class="eyebrow">Tài khoản</span>
          <h1>Hồ sơ khách hàng</h1>
          <p>Cập nhật thông tin cơ bản và đổi mật khẩu để bảo vệ tài khoản của bạn.</p>
        </div>
      </div>

      <div class="profile-grid">
        <el-card shadow="never" class="profile-card">
          <template #header><strong>Thông tin cá nhân</strong></template>
          <el-form label-position="top" @submit.prevent="submitProfile">
            <el-form-item label="Họ và tên">
              <el-input v-model="profileForm.ho_ten" />
            </el-form-item>
            <el-form-item label="Email">
              <el-input :model-value="clientAuthStore.user?.email || ''" disabled />
            </el-form-item>
            <el-form-item label="Số điện thoại">
              <el-input v-model="profileForm.so_dien_thoai" />
            </el-form-item>
            <el-button type="primary" :loading="updatingProfile" native-type="submit">Lưu thay đổi</el-button>
          </el-form>
        </el-card>

        <el-card shadow="never" class="profile-card">
          <template #header><strong>Đổi mật khẩu</strong></template>
          <el-form label-position="top" @submit.prevent="submitPassword">
            <el-form-item label="Mật khẩu hiện tại">
              <el-input v-model="passwordForm.current_password" type="password" show-password />
            </el-form-item>
            <el-form-item label="Mật khẩu mới">
              <el-input v-model="passwordForm.password" type="password" show-password />
            </el-form-item>
            <el-form-item label="Xác nhận mật khẩu mới">
              <el-input v-model="passwordForm.password_confirmation" type="password" show-password />
            </el-form-item>
            <el-button type="primary" :loading="updatingPassword" native-type="submit">Đổi mật khẩu</el-button>
          </el-form>
        </el-card>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useClientAuthStore } from '../../stores/clientAuth';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const clientAuthStore = useClientAuthStore();
const updatingProfile = ref(false);
const updatingPassword = ref(false);

const profileForm = reactive({
  ho_ten: '',
  so_dien_thoai: '',
});

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

async function submitProfile() {
  updatingProfile.value = true;

  try {
    await clientAuthStore.updateProfile({
      ho_ten: profileForm.ho_ten,
      so_dien_thoai: profileForm.so_dien_thoai,
    });

    await showAlert({
      icon: 'success',
      title: 'Đã cập nhật hồ sơ',
      text: 'Thông tin cá nhân của bạn đã được lưu.',
      toast: true,
    });
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không thể cập nhật hồ sơ',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  } finally {
    updatingProfile.value = false;
  }
}

async function submitPassword() {
  updatingPassword.value = true;

  try {
    await clientAuthStore.changePassword({ ...passwordForm });
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';

    await showAlert({
      icon: 'success',
      title: 'Đổi mật khẩu thành công',
      text: 'Bạn có thể dùng mật khẩu mới từ lần đăng nhập tiếp theo.',
      toast: true,
    });
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không thể đổi mật khẩu',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  } finally {
    updatingPassword.value = false;
  }
}

onMounted(() => {
  profileForm.ho_ten = clientAuthStore.user?.ho_ten || '';
  profileForm.so_dien_thoai = clientAuthStore.user?.so_dien_thoai || '';
});
</script>

<style scoped>
.client-page { padding: 0 0 20px; }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.page-head h1 { margin: 14px 0 8px; font-size: clamp(32px, 5vw, 50px); line-height: 0.98; }
.page-head p { margin: 0 0 20px; color: #6f5a4d; font-size: 16px; }
.profile-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
.profile-card { border-radius: 26px; }

@media (max-width: 980px) {
  .profile-grid { grid-template-columns: 1fr; }
}
</style>
