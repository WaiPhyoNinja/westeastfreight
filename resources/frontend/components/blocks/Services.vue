<template>
    <section class="services-three">
        <div class="container">
            <div class="section-title text-center sec-title-animation animation-style1">
                <div class="section-title__tagline-box">
                    <span class="section-title__tagline-border"></span>
                    <div class="section-title__shape-1">
                        <i class="fas fa-plane"></i>
                    </div>
                    <h6 class="section-title__tagline">{{ translatedContent?.title || 'Our Solutions' }}</h6>
                    <span class="section-title__tagline-border"></span>
                    <div class="section-title__shape-2">
                        <i class="fas fa-plane"></i>
                    </div>
                </div>
                <h3 class="section-title__title title-animation" style="perspective: 400px;" v-if="translatedContent?.header" v-html="translatedContent.header"></h3>
                <h3 v-else class="section-title__title title-animation" style="perspective: 400px;">
                    <div class="split-line" style="display: block; text-align: center; position: relative;">
                        <div style="position:relative;display:inline-block;">
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                E</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                f</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                f</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                i</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                c</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                i</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                e</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                n</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                t</div>
                        </div>
                        <div style="position:relative;display:inline-block;">
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                L</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                o</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                g</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                i</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                s</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                t</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                i</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                c</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                s</div>
                        </div>
                        <div style="position:relative;display:inline-block;">
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                S</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                e</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                r</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                v</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                i</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                c</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                e</div>
                            <div
                                style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                s</div>
                        </div>
                    </div>
                    <div class="split-line" style="display: block; text-align: center; position: relative;"><span>
                            <div style="position:relative;display:inline-block;">
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    f</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    o</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    r</div>
                            </div>
                            <div style="position:relative;display:inline-block;">
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    Y</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    o</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    u</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    r</div>
                            </div>
                            <div style="position:relative;display:inline-block;">
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    B</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    u</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    s</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    i</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    n</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    e</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    s</div>
                                <div
                                    style="position: relative; display: inline-block; transform: translate(0px, 0px); opacity: 1;">
                                    s</div>
                            </div>
                        </span>
                    </div>
                </h3>
            </div>
            <div class="row">
                <div v-for="(service, index) in servicesItems" :key="service.id"
                    class="col-xl-4 col-lg-6 col-md-6 wow animated" :class="getAnimationClass(index)"
                    :data-wow-delay="`${(index + 1) * 100}ms`" style="visibility: visible;">
                    <div class="services-three__single">
                        <div class="services-three__single-inner text-center">
                            <div class="services-three__single-icon">
                                <span :class="service.icon"></span>
                            </div>
                            <h3><a :href="service.link">{{ service.title }}</a></h3>
                            <p v-html="service.description"></p>

                            <div class="services-three__btn-box">
                                <a :href="service.link">Read More <span class="icon-right-arrow"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
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
        required: true
    }
});

const { currentLanguage, getTranslation } = useLanguage();

// Get translated content for block itself
const translatedContent = computed(() => {
  return getTranslation(props.data?.translations);
});

// Get services items with translations
const servicesItems = computed(() => {
    if (!props.data?.services || !Array.isArray(props.data.services)) {
        return [];
    }

    return props.data.services.map(serviceItem => {
        const service = serviceItem.services_id;
        if (!service) return null;

        const translation = getTranslation(service.translations);

        return {
            id: service.id,
            type: service.services_type,
            title: translation?.title_seo || translation?.title || '',
            description: translation?.desription_seo || '',
            icon: getIconClass(service.services_type),
            link: service.Permalink
        };
    }).filter(item => item !== null);
});

// Map service type to icon class
const getIconClass = (serviceType) => {
    const iconMap = {
        'Air': 'icon-plane',
        'Sea': 'icon-shipment',
        'Emergency': 'icon-delivery-man',
        'International': 'icon-worldwide-shipping',
        'Ocean': 'icon-shipment',
        'Rail': 'icon-delivery-man',
        'Road': 'icon-truck',
        'Local': 'icon-shipment',
        'Personal': 'icon-delivery-man'
    };
    return iconMap[serviceType] || 'icon-worldwide-shipping';
};

// Get animation class based on index
const getAnimationClass = (index) => {
    const classes = ['fadeInLeft', 'fadeInUp', 'fadeInRight'];
    return classes[index % 3];
};
</script>

<style scoped>
.services-three {
  margin-top: 10px;
  margin-bottom: 10px;
}
</style>