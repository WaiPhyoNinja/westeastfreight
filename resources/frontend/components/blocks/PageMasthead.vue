<template>
    <section class="page-header">
        <div class="page-header__bg" :style="`background-image: url(${backgroundImageUrl});`">
        </div>
        <div class="container">
            <div class="page-header__inner">
                <div class="page-header__img-1" v-if="headerImageUrl">
                    <img :src="headerImageUrl" alt="">
                </div>
                <div class="page-header__shape-1 float-bob-y" v-if="animationImageUrl">
                    <img :src="animationImageUrl" alt="">
                </div>
                <h3 :style="titleStyle">{{ pageTitle }}</h3>
                <div class="thm-breadcrumb__inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li v-for="(link, index) in breadcrumbLinks" :key="index">
                            <a v-if="link.link" :href="link.link">{{ link.link_text }}</a>
                            <span v-else>{{ link.link_text }}</span>
                            <span v-if="index < breadcrumbLinks.length - 1" class="fas fa-angle-right" style="margin: 0 10px;"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { useLanguage } from '@/composables/useLanguage';

const props = defineProps({
    data: {
        type: Object,
        required: false,
        default: () => ({})
    }
});

const { currentLanguage } = useLanguage();

const directusUrl = '/api/frontend';

// Computed properties for current language translation
const currentTranslation = computed(() => {
    if (!props.data?.translations) return null;
    return props.data.translations.find(
        t => t.languages_code === currentLanguage.value
    ) || props.data.translations.find(
        t => t.languages_code === 'en-US'
    );
});

const pageTitle = computed(() => {
    return currentTranslation.value?.title || props.data?.title || 'Page';
});

const breadcrumbLinks = computed(() => {
    if (currentTranslation.value?.link && currentTranslation.value.link.length > 0) {
        return currentTranslation.value.link;
    }
    // Default breadcrumb if no data from API
    return [
        { link: '/', link_text: 'Home' },
        { link: null, link_text: pageTitle.value }
    ];
});

const headerImageUrl = computed(() => {
    if (props.data?.header_image) {
        return `/storage/${props.data.header_image}`;
    }
    return '/assets/img/page-header-img-1.png';
});

const backgroundImageUrl = computed(() => {
    if (props.data?.header_bg_image) {
        return `/storage/${props.data.header_bg_image}`;
    }
    return '';
});

const animationImageUrl = computed(() => {
    if (props.data?.animation_image) {
        return `/storage/${props.data.animation_image}`;
    }
    return null;
});

const titleStyle = computed(() => {
    // Smaller font size for Myanmar and Thai languages
    if (currentLanguage.value === 'mm' || currentLanguage.value === 'th') {
        return { fontSize: '36px' };
    }
    // Default size for English
    return { fontSize: '42px' };
});
</script>
