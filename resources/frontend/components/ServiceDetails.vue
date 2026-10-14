<template>
    <div class="page-wrapper">
        <Header />

        <section class="page-header" v-if="service">
            <div class="page-header__bg" :style="`background-image: url(${getImageUrl(service.masthead_image)});`">
            </div>
            <div class="container">
                <div class="page-header__inner">
                    <div class="page-header__img-1">
                        <img src="/assets/img/page-header-img-1.png" alt="">
                    </div>
                    <div class="page-header__shape-1 float-bob-y">
                        <img src="/assets/img/shapes/banner-one-shape-3.png" alt="">
                    </div>
                    <h3 :style="titleStyle">{{ translation?.title }}</h3>
                    <div class="thm-breadcrumb__inner">
                        <ul class="thm-breadcrumb list-unstyled">
                            <li><a href="/">{{ t.home }}</a></li>
                            <li><span class="fas fa-angle-right"></span></li>
                            <li><a href="/services">{{ t.services }}</a></li>
                            <li><span class="fas fa-angle-right"></span></li>
                            <li>{{ translation?.title }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <section class="service-details" v-if="service">
            <div class="container">
                <div class="row">
                    <div class="col-xl-4 col-lg-5">
                        <div class="service-details__sidebar">
                            <div class="service-details__services-box">
                                <h3 class="service-details__services-title">{{ t.ourServices }}</h3>
                                <ul class="service-details__services-list list-unstyled">
                                    <li v-for="svc in allServices" :key="svc.id"
                                        :class="{ active: svc.id === service.id }">
                                        <a :href="svc.Permalink">{{ getTranslation(svc)?.title }}<span
                                                class="icon-next"></span></a>
                                    </li>
                                </ul>
                            </div>

                             <div class="service-details__sidebar-contact" v-if="locationData">
                                <div class="service-details__sidebar-contact-img">
                                    <div class="inner">
                                        <img src="/assets/img/service-details-sidebar-img.png"
                                            :alt="getLocationTranslation(locationData)?.title">
                                    </div>
                                </div>

                                <div class="service-details__sidebar-contact-content">
                                    <p class="help-text">{{ t.ifYouNeedHelp }} <br>
                                        {{ t.contactWithUs }}</p>

                                    <div class="contact-info-item">
                                        <div class="content">
                                            <h2 v-if="getLocationTranslation(locationData)?.phone">
                                                <a :href="`tel:${getLocationTranslation(locationData).phone}`">
                                                    {{ getLocationTranslation(locationData).phone }}
                                                </a>
                                            </h2>
                                        </div>
                                    </div>

                                    <div class="contact-info-item" v-if="getLocationTranslation(locationData)?.address">
                                        <div class="content">
                                            <p>{{ getLocationTranslation(locationData).address }}</p>
                                        </div>
                                    </div>

                                    <div class="contact-info-item" v-if="getLocationTranslation(locationData)?.opening_hours">
                                        <div class="content">
                                            <p>{{ getLocationTranslation(locationData).opening_hours }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           
                        </div>
                    </div>
                    <div class="col-xl-8 col-lg-7">
                        <div class="service-details__left">
                            <div class="service-details__img">
                                <img :src="getImageUrl(service.masthead_image)" :alt="translation?.title">
                            </div>
                            <h3 class="service-details__title-1">{{ translation?.title }}</h3>
                            <div v-html="translation?.description"></div>

                            <ul class="service-details__points-list list-unstyled"
                                v-if="translation?.lable && translation.lable.length > 0">
                                <li v-for="(label, index) in translation.lable" :key="index">
                                    <div class="icon">
                                        <span class="icon-right-arrow"></span>
                                    </div>
                                    <p>{{ label }}</p>
                                </li>
                            </ul>

                            <div class="service-details__img-box"
                                v-if="translation?.image_card && translation.image_card.length > 0">
                                <div class="row">
                                    <div class="col-xl-6"
                                        v-for="(cardItem, index) in translation.image_card.slice(0, 2)" :key="index">
                                        <div class="service-details__img-box-single">
                                            <div class="service-details__img-box-img">
                                                <img :src="getImageUrl(cardItem.block_cards_id?.image)" alt="">
                                            </div>
                                            <div class="service-details__img-box-content">
                                                <div class="service-details__img-box-content-icon-and-title">
                                                    <div class="service-details__img-box-content-icon">
                                                        <span
                                                            :class="index === 0 ? 'icon-new-product' : 'icon-customer-loyalty'"></span>
                                                    </div>
                                                    <h3 class="service-details__img-box-content-title">
                                                        {{ getCardTranslation(cardItem.block_cards_id)?.title }}
                                                    </h3>
                                                </div>
                                                <div
                                                    v-html="getCardTranslation(cardItem.block_cards_id)?.card_description">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-page__single" v-if="translation?.faq && translation.faq.length > 0">
                                <div class="accrodion-grp faq-one-accrodion" data-grp-name="faq-one-accrodion">
                                    <div class="accrodion" v-for="(faqItem, index) in translation.faq" :key="index"
                                        :class="{ active: activeFaq === index }">
                                        <div class="accrodion-title" @click="toggleFaq(index)">
                                            <h4>{{ faqItem.title }}</h4>
                                        </div>
                                        <div class="accrodion-content"
                                            :style="{ display: activeFaq === index ? 'block' : 'none' }">
                                            <div class="inner">
                                                <p>{{ faqItem.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useLanguage } from '@/composables/useLanguage';
import { commonTranslations } from '@/locales/common';
import Header from '../layouts/Header.vue';
import Footer from '../layouts/Footer.vue';
import MobileSlider from './MobileSlider.vue';

const route = useRoute();
const { currentLanguage } = useLanguage();

// Get common translations for current language
const t = computed(() => {
    return commonTranslations[currentLanguage.value] || commonTranslations['en-US'];
});

// Dynamic title styling for different languages
const titleStyle = computed(() => {
    if (currentLanguage.value === 'mm') {
        return { fontSize: '32px', lineHeight: '1.4' };
    }
    if (currentLanguage.value === 'th') {
        return { fontSize: '36px', lineHeight: '1.3' };
    }
    return { fontSize: '42px', lineHeight: '1.2' };
});

const service = ref(null);
const allServices = ref([]);
const locationData = ref(null);
const loading = ref(true);
const activeFaq = ref(0);

const translation = computed(() => {
    if (!service.value || !service.value.translations) return null;

    const trans = service.value.translations.find(
        t => t.languages_code === currentLanguage.value
    );

    return trans || service.value.translations[0];
});

const getTranslation = (svc) => {
    if (!svc || !svc.translations) return null;

    const trans = svc.translations.find(
        t => t.languages_code === currentLanguage.value
    );

    return trans || svc.translations[0];
};

const getCardTranslation = (card) => {
    if (!card || !card.translations) return null;

    const trans = card.translations.find(
        t => t.languages_code === currentLanguage.value
    );

    return trans || card.translations[0];
};

const getLocationTranslation = (location) => {
    if (!location || !location.translations) return null;

    const trans = location.translations.find(
        t => t.languages_code === currentLanguage.value
    );

    return trans || location.translations[0];
};

const getImageUrl = (imageId) => {
    if (!imageId) return '';
    const baseUrl = '/api/frontend';
    return `/storage/${imageId}`;
};

const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? -1 : index;
};

const fetchServiceByPermalink = async (permalink) => {
    try {
        loading.value = true;
        const baseUrl = '/api/frontend';
        const response = await fetch(`${baseUrl}/items/services?fields=*,translations.*,translations.image_card.*,translations.image_card.block_cards_id.*,translations.image_card.block_cards_id.translations.*&filter[Permalink][_eq]=${permalink}&filter[status][_eq]=published`);
        const data = await response.json();

        if (data.data && data.data.length > 0) {
            service.value = data.data[0];
        }
    } catch (error) {
        console.error('Error fetching service:', error);
    } finally {
        loading.value = false;
    }
};

const fetchAllServices = async () => {
    try {
        const baseUrl = '/api/frontend';
        const response = await fetch(`${baseUrl}/items/services?fields=*,translations.*&filter[status][_eq]=published&sort=date_created`);
        const data = await response.json();

        if (data.data) {
            allServices.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching services:', error);
    }
};

const fetchLocation = async () => {
    try {
        const baseUrl = '/api/frontend';
        const response = await fetch(`${baseUrl}/items/locations?fields=*,translations.*&filter[status][_eq]=published&limit=1`);
        const data = await response.json();
        if (data.data && data.data.length > 0) {
            locationData.value = data.data[0];
        }
    } catch (error) {
        console.error('Error fetching location:', error);
    }
};

onMounted(() => {
    const permalink = route.path;
    fetchServiceByPermalink(permalink);
    fetchAllServices();
    fetchLocation();
});

watch(() => route.path, (newPath) => {
    fetchServiceByPermalink(newPath);
});
</script>

<style scoped>
.service-details {
    padding: 100px 0;
    position: relative;
    display: block;
}

/* Sidebar Styles */
.service-details__sidebar {
    position: relative;
    display: block;
}

.service-details__services-box {
    position: relative;
    display: block;
    background-color: #f5f5f5;
    padding: 40px 30px 40px;
    margin-bottom: 30px;
}

.service-details__services-title {
    font-size: 24px;
    font-weight: 700;
    line-height: 34px;
    margin-bottom: 20px;
}

.service-details__services-list {
    position: relative;
    display: block;
}

.service-details__services-list li {
    position: relative;
    display: block;
    margin-bottom: 10px;
}

.service-details__services-list li:last-child {
    margin-bottom: 0;
}

.service-details__services-list li a {
    position: relative;
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #292930;
    background-color: #ffffff;
    padding: 15px 20px 15px 30px;
    transition: all 500ms ease;
}

.service-details__services-list li a span {
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    font-size: 12px;
    color: #292930;
    transition: all 500ms ease;
}

.service-details__services-list li.active a,
.service-details__services-list li a:hover {
    background-color: #e03e2d;
    color: #ffffff;
}

.service-details__services-list li.active a span,
.service-details__services-list li a:hover span {
    color: #ffffff;
}

/* Sidebar Contact */
.service-details__sidebar-contact {
    position: relative;
    display: block;
    margin-bottom: 30px;
}

.service-details__sidebar-contact-img {
    position: relative;
    display: block;
    overflow: hidden;
}

.service-details__sidebar-contact-img img {
    width: 100%;
    transition: all 500ms ease;
}

.service-details__sidebar-contact:hover .service-details__sidebar-contact-img img {
    transform: scale(1.05);
}

.service-details__sidebar-contact-content {
    position: relative;
    display: block;
    background-color: #e03e2d;
    text-align: center;
    padding: 40px 30px 40px;
}

.service-details__sidebar-contact-content .icon {
    position: relative;
    display: block;
    margin-bottom: 15px;
}

.service-details__sidebar-contact-content .icon span {
    font-size: 50px;
    color: #ffffff;
}

.service-details__sidebar-contact-content h2 {
    font-size: 26px;
    font-weight: 700;
    line-height: 36px;
    margin-bottom: 5px;
}

.service-details__sidebar-contact-content h2 a {
    color: #ffffff;
    transition: all 500ms ease;
}

.service-details__sidebar-contact-content h2 a:hover {
    opacity: 0.8;
}

.service-details__sidebar-contact-content p {
    font-size: 16px;
    color: #ffffff;
    margin: 0;
}

.help-text {
    font-size: 16px;
    color: #ffffff;
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.contact-info-item {
    margin-bottom: 20px;
    text-align: left;
}

.contact-info-item:last-child {
    margin-bottom: 0;
}

.contact-info-item .icon {
    display: inline-block;
    margin-bottom: 8px;
}

.contact-info-item .icon span {
    font-size: 28px;
    color: #ffffff;
}

.contact-info-item .content h2 {
    font-size: 20px;
    font-weight: 700;
    line-height: 28px;
    margin: 0;
}

.contact-info-item .content h2 a {
    color: #ffffff;
    transition: all 500ms ease;
}

.contact-info-item .content h2 a:hover {
    opacity: 0.8;
}

.contact-info-item .content p {
    font-size: 14px;
    color: #ffffff;
    line-height: 22px;
    margin: 0;
}

.location-address {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.location-address .icon-wrapper {
    display: inline-block;
    margin-bottom: 10px;
}

.location-address .icon-wrapper span {
    font-size: 24px;
    color: #ffffff;
}

.location-address p {
    font-size: 14px;
    color: #ffffff;
    line-height: 24px;
    margin: 0;
}

/* Download Box */
.service-details__sidebar-download-box {
    position: relative;
    display: block;
    background-color: #f5f5f5;
    padding: 40px 30px 40px;
}

.service-details__sidebar-single-download ul li {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #ffffff;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 500ms ease;
}

.service-details__sidebar-single-download ul li:last-child {
    margin-bottom: 0;
}

.service-details__sidebar-single-download ul li:hover {
    background-color: #e03e2d;
}

.service-details__sidebar-single-download .content-box {
    display: flex;
    align-items: center;
}

.service-details__sidebar-single-download .icon {
    font-size: 30px;
    color: #e03e2d;
    margin-right: 15px;
    transition: all 500ms ease;
}

.service-details__sidebar-single-download ul li:hover .icon {
    color: #ffffff;
}

.service-details__sidebar-single-download .text-box h2 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 0;
}

.service-details__sidebar-single-download .text-box h2 a,
.service-details__sidebar-single-download .text-box p a {
    color: #292930;
    transition: all 500ms ease;
}

.service-details__sidebar-single-download ul li:hover .text-box h2 a,
.service-details__sidebar-single-download ul li:hover .text-box p a {
    color: #ffffff;
}

.service-details__sidebar-single-download .text-box p {
    font-size: 14px;
    margin: 0;
}

.service-details__sidebar-single-download .btn-box a {
    font-size: 20px;
    color: #292930;
    transition: all 500ms ease;
}

.service-details__sidebar-single-download ul li:hover .btn-box a {
    color: #ffffff;
}

/* Main Content */
.service-details__left {
    position: relative;
    display: block;
}

.service-details__img {
    position: relative;
    display: block;
    margin-bottom: 30px;
}

.service-details__img img {
    width: 100%;
}

.service-details__title-1 {
    font-size: 36px;
    font-weight: 700;
    line-height: 46px;
    margin-bottom: 20px;
}

.service-details__text-1,
.service-details__text-2 {
    font-size: 16px;
    line-height: 28px;
    margin-bottom: 20px;
}

/* Points List */
.service-details__points-list {
    position: relative;
    display: block;
    margin: 30px 0;
}

.service-details__points-list li {
    position: relative;
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
}

.service-details__points-list li:last-child {
    margin-bottom: 0;
}

.service-details__points-list li .icon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background-color: #e03e2d;
    border-radius: 50%;
    margin-right: 15px;
    flex-shrink: 0;
}

.service-details__points-list li .icon span {
    font-size: 12px;
    color: #ffffff;
}

.service-details__points-list li p {
    font-size: 16px;
    line-height: 28px;
    margin: 0;
}

/* Image Box */
.service-details__img-box {
    position: relative;
    display: block;
    margin: 40px 0;
}

.service-details__img-box-single {
    position: relative;
    display: block;
    margin-bottom: 30px;
}

.service-details__img-box-img {
    position: relative;
    display: block;
    overflow: hidden;
    margin-bottom: 20px;
}

.service-details__img-box-img img {
    width: 100%;
    transition: all 500ms ease;
}

.service-details__img-box-single:hover .service-details__img-box-img img {
    transform: scale(1.05);
}

.service-details__img-box-content-icon-and-title {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.service-details__img-box-content-icon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background-color: #e03e2d;
    border-radius: 50%;
    margin-right: 15px;
    flex-shrink: 0;
}

.service-details__img-box-content-icon span {
    font-size: 24px;
    color: #ffffff;
}

.service-details__img-box-content-title {
    font-size: 20px;
    font-weight: 700;
    line-height: 30px;
    margin: 0;
}

.service-details__img-box-content-text {
    font-size: 16px;
    line-height: 28px;
    margin: 0;
}

/* FAQ Accordion */
.faq-page__single {
    position: relative;
    display: block;
    margin-top: 40px;
}

.accrodion {
    position: relative;
    display: block;
    background-color: #f5f5f5;
    margin-bottom: 15px;
}

.accrodion:last-child {
    margin-bottom: 0;
}

.accrodion-title {
    position: relative;
    display: block;
    padding: 20px 60px 20px 30px;
    cursor: pointer;
    transition: all 500ms ease;
}

.accrodion-title::before {
    content: "\f067";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    top: 50%;
    right: 30px;
    transform: translateY(-50%);
    font-size: 14px;
    color: #292930;
    transition: all 500ms ease;
}

.accrodion.active .accrodion-title::before {
    content: "\f068";
    color: #e03e2d;
}

.accrodion-title h4 {
    font-size: 18px;
    font-weight: 600;
    line-height: 28px;
    margin: 0;
    transition: all 500ms ease;
}

.accrodion:hover .accrodion-title h4,
.accrodion.active .accrodion-title h4 {
    color: #e03e2d;
}

.accrodion-content {
    position: relative;
    display: none;
    padding: 0 30px 20px;
}

.accrodion-content .inner {
    position: relative;
    display: block;
}

.accrodion-content p {
    font-size: 16px;
    line-height: 28px;
    margin: 0;
}

/* Responsive */
@media (max-width: 991px) {
    .service-details__sidebar {
        margin-bottom: 50px;
    }
}

@media (max-width: 767px) {
    .service-details {
        padding: 60px 0;
    }

    .service-details__title-1 {
        font-size: 28px;
        line-height: 38px;
    }

    .service-details__services-box,
    .service-details__sidebar-download-box {
        padding: 30px 20px;
    }

    .service-details__sidebar-contact-content {
        padding: 30px 20px;
    }
}
</style>
