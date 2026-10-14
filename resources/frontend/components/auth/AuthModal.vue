<template>
  <teleport to="body">
    <div v-if="isOpen" class="auth-modal-overlay" @click="closeModal">
      <div class="auth-modal" @click.stop>
        <button class="auth-modal__close" @click="closeModal">
          <i class="fas fa-times"></i>
        </button>

        <div class="auth-modal__content">
          <!-- Login Form -->
          <div v-if="isLoginMode" class="login-page__inner">
            <div class="login-page__title-box">
              <h2 class="login-page__title">Login Here</h2>
            </div>

            <form @submit.prevent="handleSubmit" class="login-page__form">
              <div class="login-page__input-box">
                <input 
                  type="email" 
                  v-model="formData.email" 
                  placeholder="Enter Your Email" 
                  required
                >
              </div>

              <div class="login-page__input-box">
                <input 
                  type="password" 
                  v-model="formData.password" 
                  placeholder="Enter Your Password" 
                  required
                >
              </div>

              <div v-if="errorMessage" class="login-page__alert">
                {{ errorMessage }}
              </div>

              <div class="login-page__checked-box">
                <div class="login-page__checked">
                  <input type="checkbox" id="remember" v-model="rememberMe">
                  <label for="remember"><span></span>Remember me</label>
                </div>
                <div class="login-page__forget-password">
                  <a href="#" @click.prevent="showForgotPassword">Forget password?</a>
                </div>
              </div>

              <div class="login-page__btn-box">
                <button type="submit" class="thm-btn login-page__btn" :disabled="isSubmitting">
                  {{ isSubmitting ? 'Please wait...' : 'Login Here' }}
                  <span><i class="icon-right-arrow"></i></span>
                </button>
              </div>

              <p class="login-page__bottom-text">
                Not registered yet? 
                <a href="#" @click.prevent="toggleMode">Create an Account</a>
              </p>
            </form>
          </div>

          <!-- Register Form -->
          <div v-else class="sign-up__inner">
            <div class="sign-up__title-box">
              <h2 class="sign-up__title">Sign Up</h2>
            </div>

            <form @submit.prevent="handleSubmit" class="sign-up__form">
              <div class="sign-up__input-box">
                <input 
                  type="text" 
                  v-model="formData.firstName" 
                  placeholder="First Name" 
                  required
                >
              </div>

              <div class="sign-up__input-box">
                <input 
                  type="text" 
                  v-model="formData.lastName" 
                  placeholder="Last Name" 
                  required
                >
              </div>

              <div class="sign-up__input-box">
                <input 
                  type="email" 
                  v-model="formData.email" 
                  placeholder="Enter Your Email" 
                  required
                >
              </div>

              <div class="sign-up__input-box">
                <input 
                  type="password" 
                  v-model="formData.password" 
                  placeholder="Enter Your Password" 
                  required
                >
              </div>

              <div class="sign-up__input-box">
                <input 
                  type="password" 
                  v-model="formData.confirmPassword" 
                  placeholder="Confirm Password" 
                  required
                >
              </div>

              <div v-if="errorMessage" class="sign-up__alert">
                {{ errorMessage }}
              </div>

              <div class="sign-up__btn-box">
                <button type="submit" class="thm-btn sign-up__btn" :disabled="isSubmitting">
                  {{ isSubmitting ? 'Please wait...' : 'Sign UP' }}
                  <span><i class="icon-right-arrow"></i></span>
                </button>
              </div>

              <div class="sign-up__social-login">
                <a href="#" class="sign-up__social-btn">
                  <i class="fab fa-google"></i>
                  <span>Continue with Google</span>
                </a>
                <a href="#" class="sign-up__social-btn">
                  <i class="fab fa-facebook-f"></i>
                  <span>Continue with Facebook</span>
                </a>
              </div>

              <p class="sign-up__bottom-text">
                Already have an account? 
                <a href="#" @click.prevent="toggleMode">Login Here</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import Swal from 'sweetalert2';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const router = useRouter();
const { login, register, requestPasswordReset } = useAuth();

const isOpen = ref(props.modelValue);
const isLoginMode = ref(true);
const isSubmitting = ref(false);
const errorMessage = ref('');
const rememberMe = ref(false);

const formData = ref({
  email: '',
  password: '',
  firstName: '',
  lastName: '',
  confirmPassword: ''
});

watch(() => props.modelValue, (newVal) => {
  isOpen.value = newVal;
  if (newVal) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

const closeModal = () => {
  isOpen.value = false;
  emit('update:modelValue', false);
  document.body.style.overflow = '';
  resetForm();
};

const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value;
  errorMessage.value = '';
  resetForm();
};

const resetForm = () => {
  formData.value = {
    email: '',
    password: '',
    firstName: '',
    lastName: '',
    confirmPassword: ''
  };
};

const handleSubmit = async () => {
  errorMessage.value = '';
  
  if (!isLoginMode.value && formData.value.password !== formData.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match';
    return;
  }

  isSubmitting.value = true;

  try {
    let result;
    
    if (isLoginMode.value) {
      result = await login(formData.value.email, formData.value.password);
    } else {
      result = await register(formData.value);
    }

    if (result.success) {
      closeModal();
      
      Swal.fire({
        title: 'Success!',
        text: isLoginMode.value ? 'Logged in successfully!' : 'Registration successful!',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#e03e2d',
        timer: 2000
      });
      
      window.location.reload();
    } else {
      errorMessage.value = result.error;
    }
  } catch (error) {
    errorMessage.value = 'An unexpected error occurred';
    console.error('Auth error:', error);
  } finally {
    isSubmitting.value = false;
  }
};

const showForgotPassword = () => {
  Swal.fire({
    title: 'Reset Password',
    input: 'email',
    inputLabel: 'Enter your email address',
    inputPlaceholder: 'your@email.com',
    showCancelButton: true,
    confirmButtonText: 'Send Reset Link',
    confirmButtonColor: '#e03e2d'
  }).then(async (result) => {
    if (result.isConfirmed) {
      const response = await requestPasswordReset(result.value);
      
      if (response.success) {
        Swal.fire({
          title: 'Email Sent!',
          text: 'Check your email for password reset instructions',
          icon: 'success',
          confirmButtonColor: '#e03e2d'
        });
      } else {
        Swal.fire({
          title: 'Error!',
          text: response.error,
          icon: 'error',
          confirmButtonColor: '#e03e2d'
        });
      }
    }
  });
};
</script>

<style scoped>

</style>
