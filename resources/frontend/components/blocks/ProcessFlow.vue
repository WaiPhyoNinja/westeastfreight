<template>
    <section class="process-one" v-if="translatedContent">
        <div class="container">
            <div class="section-title text-center sec-title-animation animation-style2">
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
                <h3 class="section-title__title title-animation" v-html="translatedContent.header"></h3>
            </div>
            <div class="process-one__inner">
                <ul class="process-one__list list-unstyled">
                    <li v-for="(item, index) in processItems" :key="index">
                        <div class="process-one__icon">
                            <span :class="item.icon"></span>
                            <div class="process-one__count"></div>
                        </div>
                        <h4 class="process-one__title">{{ item.title }}</h4>
                        <div class="process-one__text" v-html="item.content"></div>
                        <div class="process-one__shpae-1" v-if="index < processItems.length - 1"></div>
                    </li>
                </ul>
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
    required: true
  }
});

const { currentLanguage, getTranslation } = useLanguage();

// Get translated content based on current language
const translatedContent = computed(() => {
  return getTranslation(props.data?.translations);
});

// Get process items from the repeater content field
const processItems = computed(() => {
  if (!translatedContent.value?.content || !Array.isArray(translatedContent.value.content)) {
    return [];
  }
  
  return translatedContent.value.content.map((item, index) => ({
    icon: getIconClass(item.number || String(index + 1).padStart(2, '0')),
    title: item.title || '',
    content: item.description || '',
    number: item.number || String(index + 1).padStart(2, '0')
  }));
});

// Map process numbers to icon classes
const getIconClass = (number) => {
  const iconMap = {
    '01': 'icon-phone-call',
    '02': 'icon-box1',
    '03': 'icon-packaging',
    '04': 'icon-truck'
  };
  return iconMap[number] || 'icon-phone-call';
};

</script>
<style scoped>
/* Myanmar Language Font Sizes */
body.myanmar-lang .section-title__tagline {
  font-size: 20px;
}

body.myanmar-lang .section-title__title {
  font-size: 32px;
  line-height: 1.3;
}

body.myanmar-lang .process-one__title {
  font-size: 16px;
}

body.myanmar-lang .process-one__text {
  font-size: 13px;
  line-height: 1.6;
}

/* Thai Language Font Sizes */
body.thai-lang .section-title__tagline {
  font-size: 13px;
}

body.thai-lang .section-title__title {
  font-size: 32px;
  line-height: 1.3;
}

body.thai-lang .process-one__title {
  font-size: 16px;
}

body.thai-lang .process-one__text {
  font-size: 13px;
  line-height: 1.6;
}
</style>