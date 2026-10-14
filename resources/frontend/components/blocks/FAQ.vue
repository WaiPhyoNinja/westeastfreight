<template>
    <section class="faq-one" v-if="translatedContent">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 col-lg-5">
                    <div class="faq-one__left">
                        <div class="section-title text-left sec-title-animation animation-style2">
                            <div class="section-title__tagline-box">
                                <span class="section-title__tagline-border"></span>
                                <div class="section-title__shape-1">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <h6 class="section-title__tagline">{{ translatedContent.title }}</h6>
                                <span class="section-title__tagline-border"></span>
                                <div class="section-title__shape-2">
                                    <i class="fas fa-plane"></i>
                                </div>
                            </div>
                            <h3 class="section-title__title title-animation" style="perspective: 400px;"
                                v-html="translatedContent.header"></h3>
                        </div>
                        <div class="faq-one__img-box">
                            <div class="faq-one__img reveal"
                                style="transform: translate(0px, 0px); opacity: 1; visibility: inherit;">
                                <img v-if="imageUrl" :src="imageUrl" alt="" style="transform: translate(0px, 0px);">
                            </div>
                            <div class="faq-one__experience-box">
                                <div class="faq-one__experience-year">
                                    <h2 class="odometer odometer-auto-theme" :data-count="translatedContent.year">
                                        <div class="odometer-inside">
                                            <span class="odometer-digit"
                                                v-for="(digit, index) in translatedContent.year?.toString().split('')"
                                                :key="index">
                                                <span class="odometer-digit-spacer">8</span>
                                                <span class="odometer-digit-inner">
                                                    <span class="odometer-ribbon">
                                                        <span class="odometer-ribbon-inner">
                                                            <span class="odometer-value">{{ digit }}</span>
                                                        </span>
                                                    </span>
                                                </span>
                                            </span>
                                        </div>
                                    </h2>
                                </div>
                                <p class="faq-one__experience-text" v-html="translatedContent.year_text"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-7">
                    <div class="faq-one__right">
                        <div class="accrodion-grp faq-one-accrodion" data-grp-name="faq-one-accrodion">
                            
                            <div 
                                v-for="(item, index) in faqItems"  
                                :key="index" 
                                class="accrodion wow animated" 
                                :class="[getAnimationClass(index), { active: activeIndex === index }]"
                                :data-wow-delay="`${index * 100}ms`"
                                data-wow-duration="1500ms"
                                style="visibility: visible;">
                                <div class="accrodion-title" role="button" tabindex="0" :aria-expanded="activeIndex === index" @click="toggleAccordion(index)" @keydown.enter="toggleAccordion(index)" @keydown.space.prevent="toggleAccordion(index)">
                                    <h4>{{ item.title }}</h4>
                                </div>
                                <div class="accrodion-content" :style="{ display: activeIndex === index ? '' : 'none' }">
                                    <div class="inner" v-html="item.content"></div>
                                </div>
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

// Active accordion index
const activeIndex = ref(null);

// Get translated content based on current language
const translatedContent = computed(() => {
    return getTranslation(props.data?.translations);
});

// Get FAQ items from the repeater field
const faqItems = computed(() => {
    if (!translatedContent.value?.faq_content || !Array.isArray(translatedContent.value.faq_content)) {
        return [];
    }

    return translatedContent.value.faq_content.map(item => ({
        title: item.accordion_title || '',
        content: item.accordion_content || ''
    }));
});

// Get image URL from Directus
const imageUrl = computed(() => {
    if (!props.data?.image) return '';
    const baseUrl = '/api/frontend';
    return `/storage/${props.data.image}`;
});

// Alternate animation classes for accordion items
const getAnimationClass = (index) => {
    return index % 2 === 0 ? 'fadeInLeft' : 'fadeInRight';
};

// Toggle accordion item
const toggleAccordion = (index) => {
    activeIndex.value = activeIndex.value === index ? null : index;
};
</script>

<style scoped>
/* Myanmar Language Font Sizes */
body.myanmar-lang .section-title__tagline {
    font-size: 13px;
}

body.myanmar-lang .section-title__title {
    font-size: 32px;
    line-height: 1.3;
}

body.myanmar-lang .accrodion-title h4 {
    font-size: 16px;
}

body.myanmar-lang .accrodion-content p,
body.myanmar-lang .accrodion-content .inner {
    font-size: 13px;
    line-height: 1.6;
}

body.myanmar-lang .faq-one__experience-text {
    font-size: 13px;
}

body.myanmar-lang .odometer-value {
     font-size: 20px;
}

/* Thai Language Font Sizes */
body.thai-lang .section-title__tagline {
    font-size: 13px;
}

body.thai-lang .section-title__title {
    font-size: 32px;
    line-height: 1.3;
}

body.thai-lang .accrodion-title h4 {
    font-size: 16px;
}

body.thai-lang .accrodion-content p,
body.thai-lang .accrodion-content .inner {
    font-size: 13px;
    line-height: 1.6;
}

body.thai-lang .faq-one__experience-text {
    font-size: 13px;
}
</style>