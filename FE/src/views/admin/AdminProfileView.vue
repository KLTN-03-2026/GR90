<template>
  <section class="profile-page">
    <div class="profile-grid">
      <el-card shadow="never" class="profile-card">
        <template #header>
          <div class="card-head">
            <div>
              <h3>Thông tin cá nhân</h3>
              <p>Chỉ được cập nhật họ tên và số điện thoại.</p>
            </div>
          </div>
        </template>

        <el-form label-position="top" class="profile-form" @submit.prevent="submitProfile">
          <el-form-item label="Tên đăng nhập">
            <el-input :model-value="adminAuthStore.admin?.ten_dang_nhap || ''" disabled />
          </el-form-item>

          <el-form-item label="Email">
            <el-input :model-value="adminAuthStore.admin?.email || ''" disabled />
          </el-form-item>

          <el-form-item label="Họ tên">
            <el-input v-model="profileForm.ho_ten" placeholder="Nhập họ tên" />
          </el-form-item>

          <el-form-item label="Số điện thoại">
            <el-input v-model="profileForm.so_dien_thoai" placeholder="Nhập số điện thoại" />
          </el-form-item>

          <div class="form-actions">
            <el-button type="primary" :loading="savingProfile" @click="submitProfile">
              Cập nhật thông tin
            </el-button>
          </div>
        </el-form>
      </el-card>

      <el-card shadow="never" class="profile-card">
        <template #header>
          <div class="card-head">
            <div>
              <h3>Đổi mật khẩu</h3>
              <p>Đặt mật khẩu mới để bảo vệ tài khoản quản trị của bạn.</p>
            </div>
          </div>
        </template>

        <el-form label-position="top" class="profile-form" @submit.prevent="submitPassword">
          <el-form-item label="Mật khẩu hiện tại">
            <el-input
              v-model="passwordForm.current_password"
              type="password"
              show-password
              placeholder="Nhập mật khẩu hiện tại"
            />
          </el-form-item>

          <el-form-item label="Mật khẩu mới">
            <el-input
              v-model="passwordForm.password"
              type="password"
              show-password
              placeholder="Nhập mật khẩu mới"
            />
          </el-form-item>

          <el-form-item label="Xác nhận mật khẩu mới">
            <el-input
              v-model="passwordForm.password_confirmation"
              type="password"
              show-password
              placeholder="Nhập lại mật khẩu mới"
            />
          </el-form-item>

          <div class="form-actions">
            <el-button type="primary" :loading="savingPassword" @click="submitPassword">
              Đổi mật khẩu
            </el-button>
          </div>
        </el-form>
      </el-card>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { useAdminAuthStore } from '../../stores/adminAuth';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const adminAuthStore = useAdminAuthStore();

const savingProfile = ref(false);
const savingPassword = ref(false);

const profileForm = reactive({
  ho_ten: '',
  so_dien_thoai: '',
});

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

function syncProfileForm(admin) {
  profileForm.ho_ten = admin?.ho_ten || '';
  profileForm.so_dien_thoai = admin?.so_dien_thoai || '';
}

watch(
  () => adminAuthStore.admin,
  (admin) => {
    syncProfileForm(admin);
  },
  { immediate: true },
);

async function submitProfile() {
  savingProfile.value = true;

  try {
    await adminAuthStore.updateProfile({
      ho_ten: profileForm.ho_ten,
      so_dien_thoai: profileForm.so_dien_thoai,
    });

    await showAlert({
      icon: 'success',
      title: 'Cập nhật thành công',
      text: 'Thông tin cá nhân đã được lưu.',
      toast: true,
    });
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không thể cập nhật',
      text: extractFirstErrorMessage(error, 'Cập nhật thông tin cá nhân thất bại.'),
    });
  } finally {
    savingProfile.value = false;
  }
}

async function submitPassword() {
  savingPassword.value = true;

  try {
    await adminAuthStore.changePassword({
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
    });

    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';

    await showAlert({
      icon: 'success',
      title: 'Đổi mật khẩu thành công',
      text: 'Mật khẩu mới đã được cập nhật.',
      toast: true,
    });
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không thể đổi mật khẩu',
      text: extractFirstErrorMessage(error, 'Đổi mật khẩu thất bại.'),
    });
  } finally {
    savingPassword.value = false;
  }
}
</script>

<style scoped>
.profile-page {
  display: flex;
  flex-direction: column;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.profile-card {
  border-radius: 18px;
}

.card-head h3 {
  margin: 0;
  font-size: 20px;
  color: #2f1d13;
}

.card-head p {
  margin: 6px 0 0;
  color: #7f6757;
  font-size: 13px;
}

.profile-form {
  display: flex;
  flex-direction: column;
}

.form-actions {
  margin-top: 6px;
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 980px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}
</style>