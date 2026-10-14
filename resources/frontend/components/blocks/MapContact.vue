<template>
    <section class="contact-page">
        <div class="container">
            <div class="contact-page__inner">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="contact-page__left">
                            <iframe v-if="mapSrc" :src="mapSrc" title="Office location map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="contact-page__right">
                            <h3 class="contact-page__form-title">{{ title }}</h3>
                            <form class="contact-form-validated contact-page__form" @submit.prevent="handleSubmit">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6">
                                        <div class="contact-page__input-box">
                                            <input type="text" v-model="formData.name" placeholder="Name" required>
                                            <div class="icon"><span class="icon-user-1"></span></div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6">
                                        <div class="contact-page__input-box">
                                            <input type="email" v-model="formData.email" placeholder="Email" required>
                                            <div class="icon"><span class="icon-email"></span></div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6">
                                        <div class="contact-page__input-box">
                                            <input type="text" v-model="formData.phone" placeholder="Phone" required>
                                            <div class="icon"><span class="icon-phone"></span></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-6 col-lg-6 col-md-6">
                                        <div class="contact-page__input-box">
                                            <input type="text" v-model="formData.subject" placeholder="Subject" required>
                                            <div class="icon"><span class="icon-email"></span></div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="contact-page__input-box">
                                            <input type="text" v-model="formData.transport_id" placeholder="Transport ID (Optional)">
                                            <div class="icon"><span class="icon-box1"></span></div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="contact-page__input-box text-message-box">
                                            <textarea v-model="formData.message" placeholder="Message" required></textarea>
                                            <div class="icon style2"><span class="icon-location"></span></div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="contact-page__btn-box">
                                            <button type="submit" class="footer-widget__newsletter-btn thm-btn" :disabled="isSubmitting">
                                                {{ isSubmitting ? 'Sending...' : 'Submit Contact' }}
                                                <span><i class="icon-right-arrow"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="result">
                                    <p v-if="submitMessage" :class="submitStatus">{{ submitMessage }}</p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useLanguage } from '@/composables/useLanguage';
import directus from '@/services/directus';
import { createItem } from '@/services/directus';
import Swal from 'sweetalert2';

const props = defineProps({
    data: {
        type: Object,
        required: false,
        default: () => ({})
    }
});

const { currentLanguage } = useLanguage();

// Form data
const formData = ref({
    name: '',
    email: '',
    phone: '',
    subject: '',
    transport_id: '',
    message: ''
});

const isSubmitting = ref(false);
const submitMessage = ref('');
const submitStatus = ref('');

// Get current translation
const currentTranslation = computed(() => {
    if (!props.data?.translations) return null;
    return props.data.translations.find(
        t => t.languages_code === currentLanguage.value
    ) || props.data.translations.find(
        t => t.languages_code === 'en-US'
    );
});

const title = computed(() => {
    return currentTranslation.value?.title || 'Get A Contact';
});

const mapSrc = computed(() => {
    const embed = currentTranslation.value?.map_link || '';
    const source = embed.match(/<iframe\b[^>]*\bsrc\s*=\s*["']([^"']+)["']/i)?.[1] || embed.trim();

    try {
        const url = new URL(source.replaceAll('&amp;', '&'));
        return url.protocol === 'https:'
            && ['www.google.com', 'maps.google.com'].includes(url.hostname)
            && url.pathname.startsWith('/maps/embed') ? url.toString() : '';
    } catch {
        return '';
    }
});

// Handle form submission
const handleSubmit = async () => {
    isSubmitting.value = true;
    submitMessage.value = '';
    submitStatus.value = '';
    
    try {
        const result = await directus.request(
            createItem('contact_us', {
                name: formData.value.name,
                email: formData.value.email,
                phone: formData.value.phone,
                subject: formData.value.subject,
                message: formData.value.message,
                transport_id: formData.value.transport_id || null,
            })
        );
        
        // Show success with SweetAlert
        Swal.fire({
            title: 'Message received',
            text: result.email_status === 'sent'
                ? 'Your message was received and a confirmation email has been sent.'
                : 'Your message was received. A confirmation email could not be sent, but our team can review and reply to your request.',
            icon: result.email_status === 'sent' ? 'success' : 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#e03e2d'
        });
        
        // Reset form
        formData.value = {
            name: '',
            email: '',
            phone: '',
            subject: '',
            transport_id: '',
            message: ''
        };
    } catch (error) {
        // Show error with SweetAlert
        Swal.fire({
            title: 'Error!',
            text: 'Failed to submit your contact. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#e03e2d'
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
.result {
    margin-top: 20px;
}

.result p {
    padding: 15px;
    border-radius: 5px;
    margin: 0;
}

.result .success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.result .error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.thm-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.contact-page__input-box {
    position: relative;
}

.contact-page__input-box .icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #e03e2d;
}

.contact-page__input-box.text-message-box .icon {
    top: 20px;
    transform: none;
}

/* Map iframe styling */
.contact-page__left :deep(iframe),
.contact-page__left :deep(.google-map__one) {
    width: 100%;
    height: 540px;
    border: none;
}
</style>
