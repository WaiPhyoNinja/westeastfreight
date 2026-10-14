<template>
    <div class="page-wrapper">
        <Header />

        <!-- Login Section -->
        <section class="login-one">
            <div class="container">
                <div class="login-one__form">
                    <div class="inner-title text-center">
                        <h3>{{ isLoginMode ? t.loginTitle : t.signUpTitle }}</h3>
                    </div>
                    <form id="login-one__form" name="Login-one_form" @submit.prevent="handleSubmit">
                        <div class="row">
                            <!-- Registration Fields -->
                            <div v-if="!isLoginMode" class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="text" v-model="formData.firstName" :placeholder="t.firstName" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="!isLoginMode" class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="text" v-model="formData.lastName" :placeholder="t.lastName" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="email" v-model="formData.email" id="formEmail" :placeholder="t.email"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="password" v-model="formData.password" id="formPassword"
                                            :placeholder="t.password" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirm Password (Register only) -->
                            <div v-if="!isLoginMode" class="col-xl-12">
                                <div class="form-group">
                                    <div class="input-box">
                                        <input type="password" v-model="formData.confirmPassword" :placeholder="t.confirmPassword" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="form-group">
                                    <button class="thm-btn" type="submit" :disabled="isSubmitting" :data-loading-text="isSubmitting ? t.pleaseWait : ''">
                                        {{ isSubmitting ? t.pleaseWait : (isLoginMode ? t.loginButton : t.signUpButton) }}
                                        <span><i class="icon-right-arrow"></i></span>
                                    </button>
                                </div>
                            </div>
                            <div v-if="isLoginMode" class="remember-forget">
                                <div class="checked-box1">
                                    <input type="checkbox" v-model="rememberMe" id="saveinfo">
                                    <label for="saveinfo">
                                        <span></span>
                                        {{ t.rememberMe }}
                                    </label>
                                </div>
                                <div class="forget">
                                    <a href="#" @click.prevent="showForgotPassword">{{ t.forgetPassword }}</a>
                                </div>
                            </div>

                            <div class="create-account text-center">
                                <p v-if="isLoginMode">{{ t.notRegistered }} <a href="#" @click.prevent="toggleMode">{{ t.createAccount }}</a></p>
                                <p v-else>{{ t.alreadyHaveAccount }} <a href="#" @click.prevent="toggleMode">{{ t.loginHere }}</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <Footer />
    </div>
    <MobileSlider />
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useLanguage } from '@/composables/useLanguage';
import { useAuthTranslation } from '@/locales/auth';
import MobileSlider from '../MobileSlider.vue';
import Swal from 'sweetalert2';
import Header from '../../layouts/Header.vue';
import Footer from '../../layouts/Footer.vue';

const router = useRouter();
const { currentLanguage } = useLanguage();
const t = computed(() => useAuthTranslation(currentLanguage.value));
const { login, register, requestPasswordReset } = useAuth();

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

    // Validate passwords match for registration
    if (!isLoginMode.value && formData.value.password !== formData.value.confirmPassword) {
        errorMessage.value = t.value.passwordMismatch;
        return;
    }

    isSubmitting.value = true;

    try {
        let result;

        if (isLoginMode.value) {
            result = await login(formData.value.email, formData.value.password);
        } else {
            result = await register({ ...formData.value, locale: currentLanguage.value });
        }

        if (result.success) {
            Swal.fire({
                title: t.value.success,
                text: isLoginMode.value ? t.value.loginSuccess : t.value.registerSuccess,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#e03e2d'
            });

            // Redirect to home or dashboard
            router.push(sessionStorage.getItem('pendingQuoteRequest') ? '/request-quote' : '/my-orders');
        } else {
            // Show error alert
            Swal.fire({
                title: t.value.error || 'Error',
                text: result.error || 'An error occurred. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#e03e2d'
            });
            errorMessage.value = result.error;
        }
    } catch (error) {
        Swal.fire({
            title: t.value.error || 'Error',
            text: 'An unexpected error occurred. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#e03e2d'
        });
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

<style scoped></style>
