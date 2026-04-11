<template>
  <div class="auth-page register-page">
    <div class="auth-panel">
      <router-link to="/" class="back-link">← Quay về trang chủ</router-link>

      <div class="auth-copy">
        <span class="eyebrow">Tạo tài khoản mới</span>
        <h1>Đăng ký để lưu lịch sử tra cứu, tuyến yêu thích và trải nghiệm cá nhân hơn.</h1>
        <p>Điền thông tin cơ bản để tạo tài khoản khách hàng và bắt đầu sử dụng hệ thống.</p>
      </div>

      <el-card shadow="never" class="auth-card">
        <template #header>
          <div class="card-head">
            <h2>Đăng ký</h2>
            <p>Thông tin của bạn sẽ được dùng cho tài khoản khách hàng trên hệ thống.</p>
          </div>
        </template>

        <el-form label-position="top" @submit.prevent="submitRegister">
          <el-form-item label="Họ và tên">
            <el-input v-model="form.ho_ten" placeholder="Nhập họ và tên" autocomplete="name" />
          </el-form-item>

          <el-form-item label="Email">
            <el-input v-model="form.email" placeholder="Nhập email" autocomplete="email" />
          </el-form-item>

          <el-form-item label="Số điện thoại">
            <el-input v-model="form.so_dien_thoai" placeholder="Nhập số điện thoại" autocomplete="tel" />
          </el-form-item>

          <el-form-item label="Mật khẩu">
            <el-input
              v-model="form.password"
              type="password"
              show-password
              placeholder="Nhập mật khẩu"
              autocomplete="new-password"
            />
          </el-form-item>

          <el-form-item label="Xác nhận mật khẩu">
            <el-input
              v-model="form.password_confirmation"
              type="password"
              show-password
              placeholder="Nhập lại mật khẩu"
              autocomplete="new-password"
            />
          </el-form-item>

          <label class="agree-line">
            <input v-model="form.agree" type="checkbox" />
            <span>Tôi đồng ý với điều khoản sử dụng và chính sách dữ liệu của hệ thống.</span>
          </label>

          <el-button
            type="primary"
            class="submit-btn"
            :loading="submitting"
            native-type="submit"
          >
            Tạo tài khoản
          </el-button>
        </el-form>

        <p class="switch-text">
          Đã có tài khoản?
          <router-link to="/dang-nhap">Đăng nhập</router-link>
        </p>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useClientAuthStore } from '../../stores/clientAuth';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const router = useRouter();
const route = useRoute();
const clientAuthStore = useClientAuthStore();
const submitting = ref(false);

const form = reactive({
  ho_ten: '',
  email: '',
  so_dien_thoai: '',
  password: '',
  password_confirmation: '',
  agree: true,
});

async function submitRegister() {
  if (submitting.value) {
    return;
  }

  submitting.value = true;

  try {
    await clientAuthStore.register({
      ho_ten: form.ho_ten,
      email: form.email,
      so_dien_thoai: form.so_dien_thoai,
      password: form.password,
      password_confirmation: form.password_confirmation,
      agree: form.agree,
    });

    await showAlert({
      icon: 'success',
      title: 'Đăng ký thành công',
      text: 'Tài khoản của bạn đã được tạo và đăng nhập tự động.',
      toast: true,
    });

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/ban-do';
    await router.push(redirect);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Đăng ký thất bại',
      text: extractFirstErrorMessage(error, 'Không thể tạo tài khoản. Vui lòng kiểm tra lại thông tin.'),
    });
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  const hasSession = await clientAuthStore.ensureSession();
  if (hasSession) {
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/ban-do';
    await router.replace(redirect);
  }
});
</script>

<style scoped>
.auth-page {
  position: relative;
  min-height: 100vh;
  padding: 22px;
  background:
    radial-gradient(circle at top right, rgba(255, 190, 92, 0.18), transparent 28%),
    linear-gradient(180deg, #fff7ef 0%, #f2e9df 100%);
}

.register-page {
  background:
    radial-gradient(circle at top right, rgba(255, 190, 92, 0.18), transparent 28%),
    radial-gradient(circle at bottom left, rgba(34, 133, 255, 0.12), transparent 24%),
    linear-gradient(180deg, #fff7ef 0%, #f2e9df 100%);
}

.auth-panel {
  max-width: 1120px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(360px, 480px);
  gap: 24px;
  align-items: center;
  min-height: calc(100vh - 44px);
}

.back-link {
  position: absolute;
  top: 22px;
  left: 22px;
  color: #8a5533;
  font-size: 14px;
}

.eyebrow {
  display: inline-flex;
  padding: 7px 12px;
  border-radius: 999px;
  background: rgba(255, 140, 61, 0.12);
  color: #bf5a1f;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.auth-copy h1 {
  margin: 18px 0 12px;
  font-size: clamp(38px, 6vw, 62px);
  line-height: 0.98;
}

.auth-copy p {
  margin: 0;
  max-width: 52ch;
  color: #6f5a4d;
  font-size: 17px;
  line-height: 1.7;
}

.auth-card {
  border-radius: 30px;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 28px 56px rgba(90, 54, 27, 0.12);
}

.card-head h2 {
  margin: 0;
  font-size: 28px;
}

.card-head p {
  margin: 8px 0 0;
  color: #786457;
  font-size: 14px;
}

.agree-line {
  display: inline-flex;
  align-items: flex-start;
  gap: 10px;
  margin: 6px 0 18px;
  color: #6f5a4d;
  font-size: 14px;
  line-height: 1.5;
}

.submit-btn {
  width: 100%;
  min-height: 46px;
}

.switch-text {
  margin: 18px 0 0;
  text-align: center;
  color: #776458;
}

.switch-text a {
  color: #d95f22;
}

@media (max-width: 900px) {
  .auth-panel {
    grid-template-columns: 1fr;
    align-items: flex-start;
    padding-top: 48px;
  }
}

@media (max-width: 560px) {
  .auth-page {
    padding: 14px;
  }

  .auth-card {
    border-radius: 24px;
  }
}
</style>
