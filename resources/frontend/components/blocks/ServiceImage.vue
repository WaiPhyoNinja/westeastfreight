<template>
   <section class="services-one services-page">
    <div class="container">
        <div class="row">
            <!--Services One Single Start-->
            <div class="col-xl-3 col-lg-6 col-md-6" v-for="(service, index) in services" :key="service.id">
                <div class="services-one__single">
                    <div class="services-one__img-box">
                        <div class="services-one__img">
                            <img v-if="service.masthead_image" :src="getImageUrl(service.masthead_image)" width="300" height="250" :alt="getTranslation(service)?.title || ''">
                        </div>
                        <div class="services-one__icon">
                            <span :class="getServiceIcon(service.services_type)"></span>
                        </div>
                    </div>
                    <div class="services-one__content">
                        <div class="services-one__count">{{ String(index + 1).padStart(2, '0') }}</div>
                        <h4 class="services-one__title">
                            <a :href="service.Permalink || '#'">{{ getTranslation(service)?.title }}</a>
                        </h4>
                        <p class="services-one__text" v-html="getTranslation(service)?.desription_seo"></p>
                        <div class="services-one__btn-box">
                            <a :href="service.Permalink || '#'">Read More <span class="icon-right-arrow"></span></a>
                        </div>
                    </div>
                </div>
            </div>
            <!--Services One Single End-->
        </div>
    </div>
</section>
</template>
<script setup>
import { computed, onMounted, watch, ref } from 'vue';
import { useLanguage } from '@/composables/useLanguage';

const props = defineProps({
    data: {
        type: Object,
        required: false,
        default: () => ({})
    }
});

const { currentLanguage } = useLanguage();
const services = computed(() => props.data?.services || []);
const loading = ref(true);

const getTranslation = (service) => {
    if (!service || !service.translations) return null;
    
    const trans = service.translations.find(
        t => t.languages_code === currentLanguage.value
    );
    
    return trans || service.translations[0];
};

const getImageUrl = (imageId) => {
    if (!imageId) return '';
    const baseUrl = '/api/frontend';
    return `/storage/${imageId}`;
};

const getServiceIcon = (serviceType) => {
    const iconMap = {
        'Personal': 'icon-delivery-man',
        'Emergency': 'icon-delivery-man',
        'Sea': 'icon-shipment',
        'Local': 'icon-worldwide-shipping',
        'International': 'icon-worldwide-shipping',
        'Ocean': 'icon-shipment',
        'Rail': 'icon-delivery-man',
        'Road': 'icon-truck',
        'Air': 'icon-plane'
    };
    
    return iconMap[serviceType] || 'icon-truck';
};

</script>