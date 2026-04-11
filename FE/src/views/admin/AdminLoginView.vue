<template>
  <section class="admin-login-page">
    <div class="login-hero">
      <p class="hero-kicker">Admin Portal</p>
      <h1>&#272;&#259;ng nh&#7853;p qu&#7843;n tr&#7883; h&#7879; th&#7889;ng xe bu&#253;t</h1>
      <p class="hero-copy">
        S&#7917; d&#7909;ng t&#224;i kho&#7843;n qu&#7843;n tr&#7883; vi&#234;n &#273;&#7875; truy c&#7853;p dashboard, qu&#7843;n l&#253; danh m&#7909;c v&#224; v&#7853;n h&#224;nh d&#7919; li&#7879;u.
      </p>

      <div class="hero-points">
        <div class="hero-point">
          <strong>&#272;&#259;ng nh&#7853;p an to&#224;n</strong>
          <span>Phi&#234;n l&#224;m vi&#7879;c &#273;&#432;&#7907;c l&#432;u b&#7843;o m&#7853;t cho khu v&#7921;c qu&#7843;n tr&#7883;.</span>
        </div>
        <div class="hero-point">
          <strong>Ph&#226;n quy&#7873;n ri&#234;ng</strong>
          <span>Ch&#7881; t&#224;i kho&#7843;n qu&#7843;n tr&#7883; vi&#234;n &#273;ang ho&#7841;t &#273;&#7897;ng m&#7899;i truy c&#7853;p &#273;&#432;&#7907;c menu admin.</span>
        </div>
      </div>
    </div>

    <div class="login-panel">
      <div class="panel-head">
        <img src="/brand-logo.svg" alt="Logo He Thong Xe Buyt Da Nang" class="panel-logo" />
        <h2>&#272;&#259;ng nh&#7853;p Admin</h2>
        <p class="panel-copy">Nh&#7853;p email ho&#7863;c t&#234;n &#273;&#259;ng nh&#7853;p v&#224; m&#7853;t kh&#7849;u &#273;&#7875; ti&#7871;p t&#7909;c.</p>
      </div>

      <el-form class="login-form" label-position="top" @submit.prevent="submitLogin">
        <el-form-item label="Email ho&#7863;c t&#234;n &#273;&#259;ng nh&#7853;p">
          <el-input
            v-model="form.login"
            size="large"
            placeholder="admin@localhost ho&#7863;c admin"
            autocomplete="username"
            @keyup.enter="submitLogin"
          />
        </el-form-item>

        <el-form-item label="M&#7853;t kh&#7849;u">
          <el-input
            v-model="form.password"
            size="large"
            type="password"
            placeholder="Nh&#7853;p m&#7853;t kh&#7849;u"
            show-password
            autocomplete="current-password"
            @keyup.enter="submitLogin"
          />
        </el-form-item>

        <div class="login-actions">
          <el-button type="primary" size="large" :loading="submitting" @click="submitLogin">
            &#272;&#259;ng nh&#7853;p
          </el-button>
        </div>
      </el-form>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAdminAuthStore } from '../../stores/adminAuth';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const route = useRoute();
const router = useRouter();
const adminAuthStore = useAdminAuthStore();

const submitting = ref(false);
const form = reactive({
  login: '',
  password: '',
});

function normalizeAdminRedirect(value) {
  if (typeof value !== 'string') {
    return '/admin/dashboard';
  }

  return value.startsWith('/admin') ? value : '/admin/dashboard';
}

async function submitLogin() {
  if (!form.login.trim() || !form.password) {
    await showAlert({
      icon: 'warning',
      title: 'Thi\u1ebfu th\u00f4ng tin',
      text: 'Vui l\u00f2ng nh\u1eadp \u0111\u1ea7y \u0111\u1ee7 email ho\u1eb7c t\u00ean \u0111\u0103ng nh\u1eadp v\u00e0 m\u1eadt kh\u1ea9u.',
    });
    return;
  }

  submitting.value = true;

  try {
    await adminAuthStore.login({
      login: form.login.trim(),
      password: form.password,
    });

    await showAlert({
      icon: 'success',
      title: '\u0110\u0103ng nh\u1eadp th\u00e0nh c\u00f4ng',
      text: '\u0110ang chuy\u1ec3n v\u00e0o trang qu\u1ea3n tr\u1ecb.',
      toast: true,
    });

    const redirect = normalizeAdminRedirect(route.query.redirect);
    await router.replace(redirect);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: '\u0110\u0103ng nh\u1eadp th\u1ea5t b\u1ea1i',
      text: extractFirstErrorMessage(error, 'Kh\u00f4ng th\u1ec3 \u0111\u0103ng nh\u1eadp. Vui l\u00f2ng th\u1eed l\u1ea1i.'),
    });
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.admin-login-page {
  min-height: 100vh;
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(360px, 480px);
  background:
    radial-gradient(circle at top left, rgba(234, 119, 48, 0.24), transparent 38%),
    linear-gradient(135deg, #f6eee4 0%, #f2dcc4 50%, #f7f0e6 100%);
}

.login-hero {
  padding: 64px 56px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 20px;
}

.hero-kicker,
.panel-label {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 12px;
  color: #a15326;
  font-weight: 700;
}

.panel-logo {
  display: block;
  width: min(100%, 320px);
  margin: 0 0 14px;
}

.login-hero h1,
.panel-head h2 {
  margin: 0;
  line-height: 1.05;
  color: #24170f;
}

.login-hero h1 {
  max-width: 620px;
  font-size: clamp(40px, 5vw, 64px);
}

.hero-copy,
.panel-copy {
  margin: 0;
  font-size: 16px;
  color: #5b4334;
  max-width: 560px;
}

.hero-points {
  display: grid;
  gap: 14px;
  max-width: 520px;
}

.hero-point {
  padding: 18px 20px;
  border-radius: 20px;
  background: rgba(255, 250, 244, 0.72);
  border: 1px solid rgba(161, 83, 38, 0.14);
  backdrop-filter: blur(6px);
}

.hero-point strong,
.hero-point span {
  display: block;
}

.hero-point strong {
  margin-bottom: 6px;
  font-size: 16px;
  color: #2f1d13;
}

.hero-point span {
  color: #6d5342;
}

.login-panel {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 40px 32px;
  background: linear-gradient(180deg, #221812 0%, #18100b 100%);
}

.panel-head {
  margin-bottom: 28px;
}

.panel-head h2 {
  margin-top: 10px;
  font-size: 34px;
  color: #fff4e7;
}

.panel-copy {
  margin-top: 10px;
  color: rgba(255, 244, 231, 0.72);
}

.login-form {
  padding: 28px;
  border-radius: 24px;
  background: rgba(255, 249, 242, 0.08);
  border: 1px solid rgba(255, 244, 231, 0.12);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}

.login-form :deep(.el-form-item__label) {
  color: #fff2e1;
}

.login-form :deep(.el-input__wrapper) {
  min-height: 48px;
  background: #fff7f0;
  box-shadow: none;
}

.login-actions {
  margin-top: 10px;
  display: flex;
}

.login-actions :deep(.el-button) {
  width: 100%;
  min-height: 48px;
  border: none;
  background: linear-gradient(135deg, #ea7730 0%, #d25723 100%);
}

@media (max-width: 980px) {
  .admin-login-page {
    grid-template-columns: 1fr;
  }

  .login-hero {
    padding: 32px 20px 12px;
  }

  .login-panel {
    padding: 20px;
  }

  .login-form {
    padding: 22px 18px;
  }
}
</style>
