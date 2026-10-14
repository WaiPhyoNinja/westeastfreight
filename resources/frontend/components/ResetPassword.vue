<template>
  <main class="reset-page">
    <section class="reset-shell" aria-labelledby="reset-title">
      <aside class="brand-panel">
        <router-link to="/" class="brand-logo" aria-label="West East Freight home">
          <img :src="logoUrl" alt="West East Freight">
        </router-link>
        <div class="brand-copy">
          <span class="eyebrow">Secure account access</span>
          <h1>Set a new password</h1>
          <p>Choose a password you have not used before to keep your account protected.</p>
        </div>
        <div class="security-note">
          <i class="fas fa-shield-alt" aria-hidden="true"></i>
          <span>Your reset link and password are sent securely.</span>
        </div>
      </aside>

      <div class="form-panel">
        <div v-if="isSuccess" class="state-card" role="status">
          <div class="state-icon success-icon"><i class="fas fa-check" aria-hidden="true"></i></div>
          <p class="step-label">Password updated</p>
          <h2>Your new password is ready</h2>
          <p>You can now sign in to your account using the password you just created.</p>
          <router-link to="/login" class="primary-button">Continue to login</router-link>
          <router-link to="/" class="text-link">Return to home</router-link>
        </div>

        <div v-else-if="!hasValidLink" class="state-card" role="alert">
          <div class="state-icon error-icon"><i class="fas fa-link" aria-hidden="true"></i></div>
          <p class="step-label">Reset link unavailable</p>
          <h2>This link is invalid</h2>
          <p>The reset link is incomplete or has expired. Request a new link from the login page.</p>
          <router-link to="/login" class="primary-button">Back to login</router-link>
          <router-link to="/" class="text-link">Return to home</router-link>
        </div>

        <div v-else>
          <div class="form-heading">
            <p class="step-label">Password recovery</p>
            <h2 id="reset-title">Create your new password</h2>
            <p>Resetting the password for <strong>{{ maskedEmail }}</strong></p>
          </div>

          <form class="reset-form" novalidate @submit.prevent="handleSubmit">
            <div class="field-group">
              <label for="new-password">New password</label>
              <div class="password-field" :class="{ invalid: fieldErrors.password }">
                <input
                  id="new-password"
                  v-model="formData.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  placeholder="Enter at least 8 characters"
                  :aria-invalid="Boolean(fieldErrors.password)"
                  :aria-describedby="fieldErrors.password ? 'password-error' : 'password-help'"
                  @input="clearFieldError('password')"
                >
                <button type="button" class="visibility-button" :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                </button>
              </div>
              <p v-if="fieldErrors.password" id="password-error" class="field-error">{{ fieldErrors.password }}</p>
              <p v-else id="password-help" class="field-help">
                <i :class="passwordLongEnough ? 'fas fa-check-circle valid' : 'far fa-circle'" aria-hidden="true"></i>
                At least 8 characters
              </p>
            </div>

            <div class="field-group">
              <label for="confirm-password">Confirm new password</label>
              <div class="password-field" :class="{ invalid: fieldErrors.confirmPassword }">
                <input
                  id="confirm-password"
                  v-model="formData.confirmPassword"
                  :type="showConfirmation ? 'text' : 'password'"
                  autocomplete="new-password"
                  placeholder="Enter the password again"
                  :aria-invalid="Boolean(fieldErrors.confirmPassword)"
                  :aria-describedby="fieldErrors.confirmPassword ? 'confirm-error' : undefined"
                  @input="clearFieldError('confirmPassword')"
                >
                <button type="button" class="visibility-button" :aria-label="showConfirmation ? 'Hide confirmation password' : 'Show confirmation password'" @click="showConfirmation = !showConfirmation">
                  <i :class="showConfirmation ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                </button>
              </div>
              <p v-if="fieldErrors.confirmPassword" id="confirm-error" class="field-error">{{ fieldErrors.confirmPassword }}</p>
            </div>

            <div v-if="errorMessage" class="form-alert" role="alert">
              <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
              <span>{{ errorMessage }}</span>
            </div>

            <button type="submit" class="primary-button submit-button" :disabled="isLoading">
              <i v-if="isLoading" class="fas fa-spinner fa-spin" aria-hidden="true"></i>
              <span>{{ isLoading ? 'Updating password…' : 'Update password' }}</span>
            </button>

            <router-link to="/login" class="text-link">
              <i class="fas fa-arrow-left" aria-hidden="true"></i>
              Back to login
            </router-link>
          </form>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const route = useRoute();
const { resetPassword } = useAuth();
const formData = reactive({ password: '', confirmPassword: '' });
const fieldErrors = reactive({ password: '', confirmPassword: '' });
const resetToken = ref(typeof route.query.token === 'string' ? route.query.token : '');
const resetEmail = ref(typeof route.query.email === 'string' ? route.query.email : '');
const errorMessage = ref('');
const isLoading = ref(false);
const isSuccess = ref(false);
const showPassword = ref(false);
const showConfirmation = ref(false);
const headerData = ref(null);

const logoUrl = computed(() => headerData.value?.logo ? `/storage/${headerData.value.logo}` : '/assets/img/logo.png');
const hasValidLink = computed(() => Boolean(resetToken.value && resetEmail.value));
const passwordLongEnough = computed(() => formData.password.length >= 8);
const maskedEmail = computed(() => {
  const [name, domain] = resetEmail.value.split('@');
  if (!name || !domain) return resetEmail.value;
  return `${name.slice(0, 2)}${'*'.repeat(Math.max(2, name.length - 2))}@${domain}`;
});

onMounted(async () => {
  try {
    const response = await fetch('/api/frontend/items/header?fields=logo');
    if (!response.ok) return;
    const body = await response.json();
    headerData.value = body.data?.[0] ?? null;
  } catch {
    headerData.value = null;
  }
});

const clearFieldError = (field) => {
  fieldErrors[field] = '';
  errorMessage.value = '';
};

const validate = () => {
  fieldErrors.password = '';
  fieldErrors.confirmPassword = '';
  if (!formData.password) fieldErrors.password = 'Enter your new password.';
  else if (formData.password.length < 8) fieldErrors.password = 'Password must be at least 8 characters.';
  if (!formData.confirmPassword) fieldErrors.confirmPassword = 'Confirm your new password.';
  else if (formData.password !== formData.confirmPassword) fieldErrors.confirmPassword = 'Passwords do not match.';
  return !fieldErrors.password && !fieldErrors.confirmPassword;
};

const handleSubmit = async () => {
  errorMessage.value = '';
  if (!hasValidLink.value || !validate()) return;
  isLoading.value = true;
  const result = await resetPassword(resetToken.value, formData.password, resetEmail.value);
  isLoading.value = false;
  if (result.success) {
    isSuccess.value = true;
    return;
  }
  errorMessage.value = result.error || 'The reset link is invalid or has expired. Request a new link and try again.';
};
</script>

<style scoped>
.reset-page { --ink: #092f3a; --muted: #64727a; --orange: #ff4f23; min-height: 100vh; display: grid; place-items: center; padding: 40px 20px; background: radial-gradient(circle at 12% 10%, rgba(255, 79, 35, .12), transparent 28%), linear-gradient(145deg, #f8fafb 0%, #eef3f4 100%); }
.reset-shell { width: min(960px, 100%); min-height: 590px; display: grid; grid-template-columns: minmax(300px, .86fr) minmax(400px, 1.14fr); overflow: hidden; border: 1px solid rgba(9, 47, 58, .08); border-radius: 24px; background: #fff; box-shadow: 0 24px 70px rgba(9, 47, 58, .13); }
.brand-panel { display: flex; flex-direction: column; padding: 44px; color: #fff; background: linear-gradient(160deg, rgba(255, 79, 35, .16), transparent 45%), var(--ink); }
.brand-logo { display: inline-flex; align-self: flex-start; padding-bottom: 20px; border-bottom: 2px solid var(--orange); }
.brand-logo img { width: 190px; height: 48px; object-fit: contain; object-position: left center; }
.brand-copy { margin: auto 0; }
.eyebrow, .step-label { color: var(--orange); font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.brand-copy h1 { max-width: 320px; margin: 14px 0 18px; color: #fff; font-size: clamp(34px, 4vw, 48px); line-height: 1.08; }
.brand-copy p { max-width: 330px; margin: 0; color: rgba(255, 255, 255, .72); font-size: 16px; line-height: 1.75; }
.security-note { display: flex; gap: 12px; align-items: center; color: rgba(255, 255, 255, .72); font-size: 13px; line-height: 1.45; }
.security-note i { color: var(--orange); font-size: 18px; }
.form-panel { display: grid; align-items: center; padding: 60px clamp(34px, 6vw, 72px); }
.form-heading { margin-bottom: 34px; }
.form-heading h2, .state-card h2 { margin: 9px 0 10px; color: var(--ink); font-size: clamp(27px, 3vw, 34px); line-height: 1.2; }
.form-heading > p:last-child, .state-card > p:not(.step-label) { margin: 0; color: var(--muted); line-height: 1.65; }
.form-heading strong { color: var(--ink); }
.reset-form { display: grid; gap: 21px; }
.field-group label { display: block; margin-bottom: 8px; color: var(--ink); font-size: 14px; font-weight: 700; }
.password-field { position: relative; border: 1px solid #d8e0e3; border-radius: 10px; background: #fff; transition: border-color .2s, box-shadow .2s; }
.password-field:focus-within { border-color: var(--orange); box-shadow: 0 0 0 4px rgba(255, 79, 35, .1); }
.password-field.invalid { border-color: #c93d3d; }
.password-field input { width: 100%; height: 52px; padding: 0 50px 0 15px; border: 0; outline: 0; color: var(--ink); background: transparent; font: inherit; }
.password-field input::placeholder { color: #96a1a6; }
.visibility-button { position: absolute; top: 50%; right: 8px; width: 38px; height: 38px; transform: translateY(-50%); border: 0; border-radius: 8px; color: #6e7d83; background: transparent; cursor: pointer; }
.visibility-button:hover, .visibility-button:focus-visible { color: var(--orange); background: #fff2ee; outline: none; }
.field-help, .field-error { margin: 7px 0 0; font-size: 12px; }
.field-help { color: #7c898e; }
.field-help i { margin-right: 6px; }
.field-help i.valid { color: #248553; }
.field-error { color: #b92f2f; }
.form-alert { display: flex; gap: 10px; align-items: flex-start; padding: 13px 14px; border: 1px solid #f0c4c4; border-radius: 9px; color: #a92b2b; background: #fff6f6; font-size: 13px; line-height: 1.5; }
.form-alert i { margin-top: 3px; }
.primary-button { display: inline-flex; min-height: 52px; align-items: center; justify-content: center; gap: 9px; padding: 0 24px; border: 0; border-radius: 10px; color: #fff; background: var(--orange); box-shadow: 0 9px 20px rgba(255, 79, 35, .22); font-weight: 800; text-decoration: none; cursor: pointer; transition: transform .2s, box-shadow .2s, background .2s; }
.primary-button:hover:not(:disabled) { color: #fff; background: #ec4219; transform: translateY(-1px); box-shadow: 0 12px 24px rgba(255, 79, 35, .28); }
.primary-button:disabled { opacity: .65; cursor: wait; }
.submit-button { width: 100%; }
.text-link { display: inline-flex; justify-self: center; align-items: center; gap: 8px; color: var(--ink); font-size: 14px; font-weight: 700; text-decoration: none; }
.text-link:hover { color: var(--orange); }
.state-card { display: grid; justify-items: center; text-align: center; }
.state-icon { display: grid; width: 76px; height: 76px; place-items: center; margin-bottom: 24px; border-radius: 50%; font-size: 28px; }
.success-icon { color: #23794d; background: #e6f6ed; }
.error-icon { color: #b53535; background: #fff0f0; }
.state-card .primary-button { width: 100%; margin: 30px 0 20px; }
@media (max-width: 760px) {
  .reset-page { display: block; padding: 0; background: #fff; }
  .reset-shell { min-height: 100vh; grid-template-columns: 1fr; border: 0; border-radius: 0; box-shadow: none; }
  .brand-panel { min-height: auto; padding: 25px 24px; }
  .brand-logo { padding-bottom: 0; border: 0; }
  .brand-logo img { width: 158px; height: 40px; }
  .brand-copy, .security-note { display: none; }
  .form-panel { padding: 42px 24px 50px; }
}
@media (max-width: 380px) { .form-panel { padding-inline: 18px; } }
</style>
