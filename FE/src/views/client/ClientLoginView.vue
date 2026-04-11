<template>
  <div class="auth-page">
    <div class="auth-panel">
      <router-link to="/" class="back-link">← Quay về trang chủ</router-link>

      <div class="auth-copy">
        <span class="eyebrow">Tài khoản người dùng</span>
        <h1>Đăng nhập để lưu tuyến yêu thích và cá nhân hóa trải nghiệm tra cứu.</h1>
        <p>Nhập email hoặc tên đăng nhập cùng mật khẩu để tiếp tục sử dụng hệ thống.</p>
      </div>

      <el-card shadow="never" class="auth-card">
        <template #header>
          <div class="card-head">
            <h2>Đăng nhập</h2>
            <p>Hỗ trợ đăng nhập bằng email hoặc tên đăng nhập.</p>
          </div>
        </template>

        <el-form label-position="top" @submit.prevent="submitLogin">
          <el-form-item label="Email hoặc tên đăng nhập">
            <el-input
              v-model="form.login"
              placeholder="Nhập email hoặc tên đăng nhập"
              autocomplete="username"
            />
          </el-form-item>

          <el-form-item label="Mật khẩu">
            <el-input
              v-model="form.password"
              type="password"
              show-password
              placeholder="Nhập mật khẩu"
              autocomplete="current-password"
            />
          </el-form-item>

          <div class="form-row">
            <label class="check-line">
              <input v-model="form.remember" type="checkbox" />
              <span>Ghi nhớ đăng nhập</span>
            </label>
            <a href="#" @click.prevent="showComingSoon('Quên mật khẩu')">Quên mật khẩu?</a>
          </div>

          <el-button
            type="primary"
            class="submit-btn"
            :loading="submitting"
            native-type="submit"
          >
            Đăng nhập
          </el-button>
        </el-form>

        <p class="switch-text">
          Chưa có tài khoản?
          <router-link to="/dang-ky">Đăng ký ngay</router-link>
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
  login: '',
  password: '',
  remember: false,
});

async function showComingSoon(feature) {
  await showAlert({
    icon: 'info',
    title: feature,
    text: 'Chức năng này sẽ được cập nhật trong bước tiếp theo.',
  });
}

async function submitLogin() {
  if (submitting.value) {
    return;
  }

  submitting.value = true;

  try {
    await clientAuthStore.login({
      login: form.login,
      password: form.password,
    });

    await showAlert({
      icon: 'success',
      title: 'Đăng nhập thành công',
      text: 'Chào mừng bạn quay trở lại hệ thống.',
      toast: true,
    });

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/ban-do';
    await router.push(redirect);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Đăng nhập thất bại',
      text: extractFirstErrorMessage(error, 'Không thể đăng nhập. Vui lòng thử lại.'),
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
    radial-gradient(circle at top left, rgba(255, 174, 92, 0.22), transparent 28%),
    linear-gradient(180deg, #fff8f1 0%, #f3ebe2 100%);
}

.auth-panel {
  max-width: 1120px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(360px, 460px);
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

.auth-copy {
  padding-right: 22px;
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
  font-size: clamp(38px, 6vw, 64px);
  line-height: 0.98;
}

.auth-copy p {
  margin: 0;
  max-width: 50ch;
  color: #6f5a4d;
  font-size: 17px;
  line-height: 1.7;
}

.auth-card {
  border-radius: 30px;
  background: rgba(255, 255, 255, 0.88);
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

.form-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin: 4px 0 18px;
  font-size: 14px;
}

.check-line {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #6f5a4d;
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

.switch-text a,
.form-row a {
  color: #d95f22;
}

@media (max-width: 900px) {
  .auth-panel {
    grid-template-columns: 1fr;
    align-items: flex-start;
    padding-top: 48px;
  }

  .auth-copy {
    padding-right: 0;
  }
}

@media (max-width: 560px) {
  .auth-page {
    padding: 14px;
  }

  .auth-card {
    border-radius: 24px;
  }

  .form-row {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
