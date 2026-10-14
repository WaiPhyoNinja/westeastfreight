<template>
    <section class="find-transport">
        <div class="find-transport__shape-bg" :style="`background-image: url(${bgImage});`"></div>
        <div class="container">
            <div class="find-transport__inner">
                <div class="find-transport__title-box">
                    <h3 class="find-transport__title" v-html="title"></h3>
                </div>
                <div class="find-transport__btn-and-call">
                    <div class="find-transport__call-us">
                        <div class="icon">
                           
                            <span class="icon-phone-call"></span>
                        </div>
                        <div class="content" v-html="content"></div>
                    </div>
                    <div class="find-transport__btn-box">
                        <a :href="ctaLink" class="thm-btn">{{ linkText }}<span><i class="icon-right-arrow"></i></span></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { useLanguage } from '@/composables/useLanguage';
import bgImage from '@/assets/img/banner-ong-pattern.png';

const props = defineProps({
    data: {
        type: Object,
        required: false,
        default: () => ({})
    }
});

const { currentLanguage } = useLanguage();
const directusUrl = '/api/frontend';

const currentTranslation = computed(() => {
    if (!props.data?.translations) return null;
    return props.data.translations.find(
        t => t.languages_code === currentLanguage.value
    ) || props.data.translations.find(
        t => t.languages_code === 'en-US'
    );
});

const title = computed(() => {
    return currentTranslation.value?.title || 'Looking for the Best logistics Transport Service?';
});

const content = computed(() => {
    return currentTranslation.value?.content || '';
});

const linkText = computed(() => {
    return currentTranslation.value?.link_text || 'Get a Quote';
});

const ctaLink = computed(() => {
    return currentTranslation.value?.cta_link || '/request-quote';
});

const iconUrl = computed(() => {
    if (currentTranslation.value?.icon) {
        return `/storage/${currentTranslation.value.icon}`;
    }
    return null;
});
</script>