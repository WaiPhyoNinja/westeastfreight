<template>
    <div class="page-wrapper">
        <Header />
        
        <section class="my-account-section">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!-- Profile Tab -->
                        <div v-if="activeTab === 'profile'" class="account-content-box">
                            <h3>{{ t.profileInfo }}</h3>
                            <form @submit.prevent="updateProfile">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-first-name">{{ t.firstName.replace('...', '') }}</label>
                                            <input id="profile-first-name" maxlength="100" autocomplete="given-name" type="text" v-model="profileData.first_name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-last-name">{{ t.lastName.replace('...', '') }}</label>
                                            <input id="profile-last-name" maxlength="100" autocomplete="family-name" type="text" v-model="profileData.last_name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="profile-email">{{ t.email.replace('...', '') }}</label>
                                            <input id="profile-email" type="email" v-model="profileData.email" class="form-control" disabled>
                                        </div>
                                    </div>
                                    <div v-for="field in profileFields" :key="field.key" :class="field.wide ? 'col-md-12' : 'col-md-6'">
                                        <div class="form-group">
                                            <label :for="'profile-' + field.key">{{ field.label }}</label>
                                            <textarea v-if="field.key === 'address'" :id="'profile-' + field.key" v-model="profileData.profile_details[field.key]" class="form-control profile-address" rows="3" :maxlength="field.max" :autocomplete="field.autocomplete"></textarea>
                                            <template v-else-if="field.key === 'country'">
                                                <select id="profile-country" v-model="profileData.profile_details.country" class="form-control" autocomplete="country-name" :disabled="countriesLoading || !!countriesError">
                                                    <option value="">{{ q.selectCountry }}</option>
                                                    <option v-if="savedCountry && !profileCountries.some(item => item.country_name === savedCountry)" :value="savedCountry">{{ savedCountry }}</option>
                                                    <option v-for="country in profileCountries" :key="country.id" :value="country.country_name">{{ country.country_name }}</option>
                                                </select>
                                                <div v-if="countriesError" role="alert" class="profile-country-error">{{ countriesError }} <button type="button" @click="fetchProfileCountries">{{ countryText.retry }}</button></div>
                                            </template>
                                            <input v-else :id="'profile-' + field.key" v-model="profileData.profile_details[field.key]" class="form-control" :type="field.type || 'text'" :maxlength="field.max" :autocomplete="field.autocomplete">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="thm-btn" :disabled="isUpdating">
                                            {{ isUpdating ? t.updating : t.updateProfile }}
                                            <span><i class="icon-right-arrow"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                    <!-- Orders Tab -->
                    <div v-if="activeTab === 'orders'" class="account-content-box">
                        <h3>{{ t.myRequestQuote }}</h3>
                            <div class="quote-requests-section" style="margin-bottom: 40px;">
                                <h4 style="margin-top: 0; margin-bottom: 20px; color: #e03e2d;">
                                    <i class="fas fa-file-invoice"></i> {{ t.quoteRequests }}
                                </h4>
                                
                                <div class="quote-status-filters" role="group" aria-label="Filter quotes by status">
                                    <button v-for="status in quoteStatuses" :key="status" type="button" :class="{ active: selectedQuoteStatus === status }" :aria-pressed="selectedQuoteStatus === status" @click="selectedQuoteStatus = status">
                                        {{ statusLabels[status] }} <span>{{ status === 'all' ? quoteRequests.length : quoteRequests.filter(quote => quote.status === status).length }}</span>
                                    </button>
                                </div>
                                <div v-if="loadingQuotes" class="loading-state">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: #e03e2d;"></i>
                                    <p>{{ t.loadingQuotes }}</p>
                                </div>
                                
                                <div v-else-if="filteredQuotes.length === 0" class="empty-state">
                                    <i class="fas fa-file-invoice" style="font-size: 50px; color: #ccc;"></i>
                                    <p>{{ t.noQuotesFound }}</p>
                                    <button @click="router.push('/request-quote')" class="thm-btn" style="margin-top: 15px;">
                                        {{ t.requestQuote }}
                                        <span><i class="icon-right-arrow"></i></span>
                                    </button>
                                </div>
                                
                                <div v-else class="quotes-list">
                                    <div v-for="quote in paginatedQuotes" :key="quote.id" class="quote-item">
                                        <div class="quote-header">
                                            <div class="quote-id">
                                                <strong>Quote #{{ String(quote.id) }}</strong>
                                                <span :class="['status-badge', `status-${quote.status}`]">
                                                    {{ statusLabels[quote.status] || quote.status }}
                                                </span>
                                            </div>
                                            <div class="quote-date">
                                                <i class="fas fa-calendar"></i>
                                                {{ formatDate(quote.date_created) }}
                                            </div>
                                        </div>
                                        
                                        <div class="quote-details">
                                            <div class="quote-route">
                                                <div class="route-info">
                                                    <i class="fas fa-map-marker-alt text-success"></i>
                                                    <div>
                                                        <strong>{{ t.from }}:</strong>
                                                        <p>{{ quote.sender_city }}, {{ quote.sender_country }}</p>
                                                    </div>
                                                </div>
                                                <div class="route-arrow">
                                                    <i class="fas fa-arrow-right"></i>
                                                </div>
                                                <div class="route-info">
                                                    <i class="fas fa-map-pin text-danger"></i>
                                                    <div>
                                                        <strong>{{ t.to }}:</strong>
                                                        <p>{{ quote.receiver_city }}, {{ quote.receiver_country }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="quote-info-grid">
                                                <div class="info-item">
                                                    <i class="fas fa-box"></i>
                                                    <span>{{ quote.shipment_type }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="fas fa-shipping-fast"></i>
                                                    <span>{{ quote.service_type }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="fas fa-weight"></i>
                                                    <span>{{ quote.weight }} kg</span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="fas fa-boxes"></i>
                                                    <span>{{ quote.quantity }} package(s)</span>
                                                </div>
                                            </div>
                                            
                                            <div class="quote-actions">
                                                <button @click="viewQuoteDetails(quote)" class="btn-view-details">
                                                    <i class="fas fa-eye"></i> {{ t.viewDetails }}
                                                </button>
                                                <button 
                                                    v-if="quote.status === 'pending'" 
                                                    @click="editQuote(quote)" 
                                                    class="btn-edit-quote"
                                                >
                                                    <i class="fas fa-edit"></i> {{ t.editQuote }}
                                                </button>
                                                <button 
                                                    v-if="quote.status === 'approved'" 
                                                    @click="downloadPDF(quote)" 
                                                    class="btn-download-pdf"
                                                >
                                                    <i class="fas fa-download"></i> {{ t.downloadPDF }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pagination -->
                                <div v-if="filteredQuotes.length > itemsPerPage" class="pagination-container">
                                    <div class="pagination">
                                        <button 
                                            @click="goToPage(currentPage - 1)" 
                                            :disabled="currentPage === 1"
                                            class="pagination-btn"
                                        >
                                            <i class="fas fa-chevron-left"></i>
                                            Previous
                                        </button>
                                        
                                        <div class="pagination-numbers">
                                            <button
                                                v-for="page in totalPages"
                                                :key="page"
                                                @click="goToPage(page)"
                                                :class="['pagination-number', { active: currentPage === page }]"
                                            >
                                                {{ page }}
                                            </button>
                                        </div>
                                        
                                        <button 
                                            @click="goToPage(currentPage + 1)" 
                                            :disabled="currentPage === totalPages"
                                            class="pagination-btn"
                                        >
                                            Next
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                    <div class="pagination-info">
                                        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredQuotes.length) }} of {{ filteredQuotes.length }} quotes
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Regular Orders Section -->
                            <!-- <div class="orders-section" style="margin-top: 40px;">
                                <h4 style="margin-bottom: 20px; color: #e03e2d;">
                                    <i class="fas fa-box"></i> Orders
                                </h4>
                                
                                <div v-if="orders.length === 0" class="empty-state">
                                    <i class="fas fa-box-open" style="font-size: 50px; color: #ccc;"></i>
                                    <p>{{ t.noOrders }}</p>
                                </div>
                                <div v-else class="orders-list">
                                    <div v-for="order in orders" :key="order.id" class="order-item">
                                        <h4>{{ t.orderNumber }} #{{ order.id }}</h4>
                                        <p>{{ t.orderDate }}: {{ new Date(order.date_created).toLocaleDateString() }}</p>
                                        <p>{{ t.orderStatus }}: <span class="badge">{{ order.status }}</span></p>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        
                        <!-- Password Tab -->
                        <div v-if="activeTab === 'password'" class="account-content-box">
                            <h3>{{ t.changePassword }}</h3>
                            <form @submit.prevent="updatePassword">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{ t.currentPassword }}</label>
                                            <input type="password" v-model="passwordData.current" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{ t.newPassword }}</label>
                                            <input type="password" v-model="passwordData.new" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{ t.confirmNewPassword }}</label>
                                            <input type="password" v-model="passwordData.confirm" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="thm-btn" :disabled="isUpdating">
                                            {{ isUpdating ? t.updating : t.updatePassword }}
                                            <span><i class="icon-right-arrow"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <Footer />
    </div>
    <MobileSlider />
</template>

<script setup>
import { downloadQuotePdf } from '@/utils/quotePdf';
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useLanguage } from '@/composables/useLanguage';
import { useAuthTranslation } from '@/locales/auth';
import { quoteTranslations } from '@/locales/quote';
import { apiRequest } from '@/services/http';
import Swal from 'sweetalert2';
import Header from '../layouts/Header.vue';
import Footer from '../layouts/Footer.vue';
import MobileSlider from './MobileSlider.vue';

const router = useRouter();
const route = useRoute();
const { user, logout } = useAuth();
const { currentLanguage } = useLanguage();
const t = computed(() => useAuthTranslation(currentLanguage.value));

const q = computed(() => quoteTranslations[currentLanguage.value] || quoteTranslations['en-US']);
const profileFields = computed(() => [
    { key: 'phone', label: t.value.phone, type: 'tel', max: 40, autocomplete: 'tel' },
    { key: 'company', label: t.value.company, max: 255, autocomplete: 'organization' },
    { key: 'job_title', label: currentLanguage.value === 'mm' ? 'ရာထူး' : currentLanguage.value === 'th' ? 'ตำแหน่งงาน' : 'Job title', max: 100, autocomplete: 'organization-title' },
    { key: 'address', label: t.value.address, max: 1000, autocomplete: 'street-address', wide: true },
    { key: 'city', label: q.value.city, max: 100, autocomplete: 'address-level2' },
    { key: 'state', label: q.value.state, max: 100, autocomplete: 'address-level1' },
    { key: 'country', label: q.value.country, max: 100, autocomplete: 'country-name' },
    { key: 'postal_code', label: q.value.postalCode, max: 20, autocomplete: 'postal-code' },
]);
const profileCountries = ref([]);
const countriesLoading = ref(false);
const countriesError = ref('');
const savedCountry = ref('');
const countryText = computed(() => currentLanguage.value === 'mm'
    ? { error: 'နိုင်ငံစာရင်း ရယူ၍မရပါ။', retry: 'ပြန်ကြိုးစားရန်' }
    : currentLanguage.value === 'th' ? { error: 'ไม่สามารถโหลดรายชื่อประเทศได้', retry: 'ลองอีกครั้ง' }
    : { error: 'Unable to load countries.', retry: 'Try again' });
const fetchProfileCountries = async () => {
    countriesLoading.value = true;
    countriesError.value = '';
    try {
        const response = await apiRequest('items/quote_country', undefined, 'GET');
        if (!Array.isArray(response.data)) throw new Error('Invalid country list');
        profileCountries.value = response.data;
    } catch { countriesError.value = countryText.value.error; }
    finally { countriesLoading.value = false; }
};
const blankProfileDetails = () => Object.fromEntries(profileFields.value.map(field => [field.key, '']));
const activeTab = ref('profile');
watch(() => [route.path, route.query.tab], () => {
    activeTab.value = ['/my-orders', '/orders'].includes(route.path) || route.query.tab === 'orders' ? 'orders' : route.query.tab === 'password' ? 'password' : 'profile';
}, { immediate: true });
const isUpdating = ref(false);
const orders = ref([]);
const quoteRequests = ref([]);
const loadingQuotes = ref(false);
const currentPage = ref(1);
const itemsPerPage = 5;
const selectedQuoteStatus = ref('all');
const quoteStatuses = ['all', 'pending', 'reviewed', 'quoted', 'approved', 'rejected'];
const statusLabels = computed(() => ({
    'en-US': { all: 'All', pending: 'Pending', reviewed: 'Reviewed', quoted: 'Quoted', approved: 'Approved', rejected: 'Rejected' },
    mm: { all: 'အားလုံး', pending: 'စောင့်ဆိုင်းဆဲ', reviewed: 'စစ်ဆေးပြီး', quoted: 'ဈေးနှုန်းပေးပြီး', approved: 'အတည်ပြုပြီး', rejected: 'ငြင်းပယ်ထား' },
    th: { all: 'ทั้งหมด', pending: 'รอดำเนินการ', reviewed: 'ตรวจสอบแล้ว', quoted: 'เสนอราคาแล้ว', approved: 'อนุมัติแล้ว', rejected: 'ปฏิเสธ' },
}[currentLanguage.value] || { all: 'All', pending: 'Pending', reviewed: 'Reviewed', quoted: 'Quoted', approved: 'Approved', rejected: 'Rejected' }));
const filteredQuotes = computed(() => selectedQuoteStatus.value === 'all' ? quoteRequests.value : quoteRequests.value.filter(quote => quote.status === selectedQuoteStatus.value));
watch(selectedQuoteStatus, () => { currentPage.value = 1; });
watch(() => filteredQuotes.value.length, () => { currentPage.value = Math.min(currentPage.value, Math.max(1, Math.ceil(filteredQuotes.value.length / itemsPerPage))); });

const profileData = ref({
    profile_details: blankProfileDetails(),
    first_name: '',
    last_name: '',
    email: ''
});

const passwordData = ref({
    current: '',
    new: '',
    confirm: ''
});

// Pagination computed properties
const totalPages = computed(() => Math.ceil(filteredQuotes.value.length / itemsPerPage));
const paginatedQuotes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredQuotes.value.slice(start, end);
});

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

onMounted(async () => {
    if (user.value) {
        profileData.value = {
            profile_details: { ...blankProfileDetails(), ...(user.value.profile_details || {}) },
            first_name: user.value.first_name || '',
            last_name: user.value.last_name || '',
            email: user.value.email || ''
        };
        
        savedCountry.value = user.value.profile_details?.country || '';
        await Promise.all([fetchQuoteRequests(), fetchProfileCountries()]);
    }
});

const fetchQuoteRequests = async () => {
    loadingQuotes.value = true;
    try {
        if (!user.value?.id) {
            quoteRequests.value = [];
            return;
        }


        const response = await apiRequest('items/quote_requests', undefined, 'GET');
        quoteRequests.value = response.data || [];

        // Reset to first page when data changes
        currentPage.value = 1;
    } catch (error) {
        console.error('Error fetching quote requests:', error);
        quoteRequests.value = [];
        if (error.response?.status !== 403) {
            Swal.fire({
                title: 'Error',
                text: 'Failed to load quote requests',
                icon: 'error',
                confirmButtonColor: '#e03e2d'
            });
        }
    } finally {
        loadingQuotes.value = false;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const viewQuoteDetails = quote => router.push(`/my-orders/${quote.id}`);

const editQuote = quote => {
    sessionStorage.removeItem('editQuoteData');
    router.push({ path: '/request-quote', query: { edit: quote.id } });
};

const downloadPDF = async quote => {
    try { await downloadQuotePdf(quote); }
    catch (error) {
        await Swal.fire({ title: t.value.error, text: error.message || 'Unable to download PDF. Please try again.', icon: 'error', confirmButtonColor: '#fd5523' });
    }
};

const updateProfile = async () => {
    if (isUpdating.value) return;
    isUpdating.value = true;
    
    try {
        const response = await apiRequest('auth/profile', profileData.value, 'PATCH');
        user.value = response.data;
        savedCountry.value = response.data.profile_details?.country || '';
        
        Swal.fire({
            title: t.value.success,
            text: t.value.profileUpdated,
            icon: 'success',
            confirmButtonColor: '#e03e2d'
        });
    } catch (error) {
        Swal.fire({
            title: t.value.error,
            text: error.message || 'Failed to update profile',
            icon: 'error',
            confirmButtonColor: '#e03e2d'
        });
    } finally {
        isUpdating.value = false;
    }
};

const updatePassword = async () => {
    if (passwordData.value.new !== passwordData.value.confirm) {
        Swal.fire({
            title: t.value.error,
            text: t.value.newPasswordMismatch,
            icon: 'error',
            confirmButtonColor: '#e03e2d'
        });
        return;
    }
    
    isUpdating.value = true;
    
    try {
        await apiRequest('auth/password', { current_password: passwordData.value.current, password: passwordData.value.new, password_confirmation: passwordData.value.confirm });
        
        passwordData.value = { current: '', new: '', confirm: '' };
        
        Swal.fire({
            title: t.value.success,
            text: t.value.passwordUpdated,
            icon: 'success',
            confirmButtonColor: '#e03e2d'
        });
    } catch (error) {
        Swal.fire({
            title: t.value.error,
            text: error.message || 'Failed to update password',
            icon: 'error',
            confirmButtonColor: '#e03e2d'
        });
    } finally {
        isUpdating.value = false;
    }
};

const handleLogout = async () => {
    const result = await Swal.fire({
        title: t.value.logout,
        text: t.value.logoutConfirm,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#e03e2d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: t.value.yes,
        cancelButtonText: t.value.cancel
    });

    if (result.isConfirmed) {
        await logout();
        router.push('/');
    }
};
</script>

<style scoped>
.profile-country-error { color: #b42318; font-size: 13px; margin-top: 8px; }
.profile-country-error button { background: none; border: 0; color: inherit; text-decoration: underline; }
.profile-address { height: auto !important; min-height: 110px; resize: vertical; padding: 12px 15px; }
.my-account-section {
    padding: 80px 0;
    background-color: #f8f9fa;
}

.page-header h2 {
    font-size: 42px;
    color: #1a3353;
    margin-bottom: 10px;
}

.account-sidebar {
    background: #ffffff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
}

.user-info-box {
    text-align: center;
    padding-bottom: 30px;
    border-bottom: 1px solid #e5e5e5;
    margin-bottom: 30px;
}

.user-info-box h3 {
    margin-top: 15px;
    color: #1a3353;
    font-size: 20px;
}

.user-info-box p {
    color: #6b7280;
    margin: 5px 0 0 0;
}

.account-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.account-menu li {
    margin-bottom: 10px;
}

.account-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #1a3353;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s;
}

.account-menu li a i {
    margin-right: 10px;
    width: 20px;
}

.account-menu li:hover a,
.account-menu li.active a {
    background-color: #e03e2d;
    color: #ffffff;
}

.account-content-box {
    background: #ffffff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
}

.account-content-box h3 {
    color: #1a3353;
    font-size: 28px;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e03e2d;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: #1a3353;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    height: 50px;
    border: 1px solid #e5e5e5;
    border-radius: 5px;
    padding: 0 20px;
    font-size: 16px;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #e03e2d;
}

.form-control:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state p {
    color: #6b7280;
    font-size: 18px;
    margin-top: 20px;
}

.order-item {
    padding: 20px;
    border: 1px solid #e5e5e5;
    border-radius: 5px;
    margin-bottom: 15px;
}

.badge {
    background-color: #e03e2d;
    color: #ffffff;
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 12px;
}

/* Quote Requests Styles */
.loading-state {
    text-align: center;
    padding: 40px;
}

.loading-state p {
    color: #666;
    margin-top: 15px;
    font-size: 16px;
}

.quotes-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.quote-item {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.quote-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.quote-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.quote-id {
    display: flex;
    align-items: center;
    gap: 15px;
}

.quote-id strong {
    font-size: 16px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
    background: rgba(255, 255, 255, 0.2);
}

.status-badge.status-pending {
    background: #fbbf24;
    color: #92400e;
}

.status-badge.status-reviewed {
    background: #60a5fa;
    color: #1e3a8a;
}

.status-badge.status-quoted {
    background: #34d399;
    color: #065f46;
}

.status-badge.status-approved {
    background: #10b981;
    color: #064e3b;
}

.status-badge.status-rejected {
    background: #f87171;
    color: #7f1d1d;
}

.quote-date {
    font-size: 14px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 5px;
}

.quote-details {
    padding: 20px;
}

.quote-route {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
}

.route-info {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    flex: 1;
}

.route-info i {
    font-size: 20px;
    margin-top: 3px;
}

.route-info strong {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 3px;
}

.route-info p {
    margin: 0;
    color: #111827;
    font-size: 14px;
    font-weight: 500;
}

.route-arrow {
    color: #9ca3af;
    font-size: 18px;
}

.quote-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f9fafb;
    border-radius: 6px;
}

.info-item i {
    color: #e03e2d;
    font-size: 18px;
}

.info-item span {
    font-size: 14px;
    color: #374151;
    font-weight: 500;
}

.quote-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.btn-view-details {
    background: #e03e2d;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-view-details:hover {
    background: #c02d1f;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(224, 62, 45, 0.3);
}

.btn-view-details i {
    font-size: 16px;
}

.btn-download-pdf {
    background: #10b981;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-download-pdf:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.btn-download-pdf i {
    font-size: 16px;
}

.btn-edit-quote {
    background: #3b82f6;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-edit-quote:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

.btn-edit-quote i {
    font-size: 16px;
}

/* Pagination Styles */
.pagination-container {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.pagination-btn {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #374151;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #e03e2d;
    color: #e03e2d;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-numbers {
    display: flex;
    gap: 5px;
}

.pagination-number {
    min-width: 40px;
    height: 40px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
}

.pagination-number:hover {
    background: #f9fafb;
    border-color: #e03e2d;
    color: #e03e2d;
}

.pagination-number.active {
    background: #e03e2d;
    border-color: #e03e2d;
    color: #fff;
}

.pagination-info {
    text-align: center;
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .quote-route {
        flex-direction: column;
        gap: 10px;
    }
    
    .route-arrow {
        transform: rotate(90deg);
    }
    
    .quote-info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quote-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .pagination {
        flex-wrap: wrap;
    }
    
    .pagination-btn {
        padding: 8px 15px;
        font-size: 13px;
    }
    
    .pagination-number {
        min-width: 35px;
        height: 35px;
        font-size: 13px;
    }
    
    .pagination-info {
        font-size: 12px;
    }
    
    /* Mobile-specific quote card improvements */
    .my-account-section {
        padding: 40px 0;
    }
    
    .account-content-box {
        padding: 20px;
    }
    
    .account-content-box h3 {
        font-size: 22px;
    }
    
    .quote-item {
        margin-bottom: 15px;
    }
    
    .quote-header {
        padding: 12px 15px;
    }
    
    .quote-id strong {
        font-size: 14px;
    }
    
    .status-badge {
        font-size: 11px;
        padding: 3px 10px;
    }
    
    .quote-date {
        font-size: 12px;
    }
    
    .quote-details {
        padding: 15px;
    }
    
    .quote-route {
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .route-info strong {
        font-size: 11px;
    }
    
    .route-info p {
        font-size: 13px;
    }
    
    .info-item {
        padding: 8px;
    }
    
    .info-item i {
        font-size: 16px;
    }
    
    .info-item span {
        font-size: 12px;
    }
    
    .quote-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-view-details,
    .btn-edit-quote,
    .btn-download-pdf {
        width: 100%;
        justify-content: center;
        padding: 10px 15px;
        font-size: 13px;
    }
    
    .account-sidebar {
        margin-bottom: 30px;
    }
    
    .pagination-container {
        margin-top: 20px;
    }
}

@media (max-width: 576px) {
    .my-account-section {
        padding: 30px 0;
    }
    
    .account-content-box {
        padding: 15px;
    }
    
    .account-content-box h3 {
        font-size: 20px;
        margin-bottom: 20px;
    }
    
    .quote-requests-section h4 {
        font-size: 16px !important;
        margin-bottom: 15px !important;
    }
    
    .quote-item {
        border-radius: 8px;
    }
    
    .quote-header {
        padding: 10px 12px;
    }
    
    .quote-id {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        width: 100%;
    }
    
    .quote-id strong {
        font-size: 13px;
    }
    
    .status-badge {
        font-size: 10px;
        padding: 2px 8px;
    }
    
    .quote-date {
        font-size: 11px;
        margin-top: 5px;
    }
    
    .quote-details {
        padding: 12px;
    }
    
    .quote-route {
        padding: 10px;
        margin-bottom: 12px;
    }
    
    .route-info {
        gap: 8px;
    }
    
    .route-info i {
        font-size: 16px;
    }
    
    .route-info strong {
        font-size: 10px;
    }
    
    .route-info p {
        font-size: 12px;
    }
    
    .route-arrow {
        font-size: 16px;
        padding: 5px 0;
    }
    
    .quote-info-grid {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .info-item {
        padding: 8px 6px;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 5px;
    }
    
    .info-item i {
        font-size: 14px;
    }
    
    .info-item span {
        font-size: 11px;
        line-height: 1.3;
    }
    
    .quote-actions {
        padding-top: 12px;
        gap: 6px;
    }
    
    .btn-view-details,
    .btn-edit-quote,
    .btn-download-pdf {
        padding: 10px 12px;
        font-size: 12px;
    }
    
    .btn-view-details i,
    .btn-edit-quote i,
    .btn-download-pdf i {
        font-size: 14px;
    }
    
    .pagination {
        gap: 5px;
    }
    
    .pagination-btn {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    .pagination-btn i {
        font-size: 10px;
    }
    
    .pagination-numbers {
        gap: 3px;
    }
    
    .pagination-number {
        min-width: 32px;
        height: 32px;
        font-size: 12px;
    }
    
    .pagination-info {
        font-size: 11px;
        padding: 0 10px;
        line-height: 1.4;
    }
    
    .empty-state {
        padding: 40px 15px;
    }
    
    .empty-state i {
        font-size: 40px !important;
    }
    
    .empty-state p {
        font-size: 14px;
    }
    
    .loading-state i {
        font-size: 30px !important;
    }
    
    .loading-state p {
        font-size: 14px;
    }
}

@media (max-width: 390px) {
    .quote-info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: row;
        justify-content: flex-start;
        text-align: left;
    }
    
    .pagination-numbers {
        display: flex;
        overflow-x: auto;
        max-width: 100%;
        padding: 2px;
    }
    
    .pagination-btn span {
        display: none;
    }
    
    .pagination-btn i {
        margin: 0;
    }
}
.quote-status-filters{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 24px}
.quote-status-filters button{display:flex;align-items:center;gap:8px;border:1px solid #e2e7e9;border-radius:8px;padding:10px 14px;background:#fff;color:#062f3a;font-size:13px;font-weight:600;line-height:1.5}
.quote-status-filters button.active{background:#062f3a;border-color:#062f3a;color:#fff}
.quote-status-filters button span{padding:1px 7px;border-radius:12px;background:#f0f3f4;color:#062f3a;font-size:12px}
.quote-status-filters button.active span{background:#fd5523;color:#fff}
.quote-status-filters button:focus-visible{outline:3px solid #ffad8e;outline-offset:2px}

</style>
