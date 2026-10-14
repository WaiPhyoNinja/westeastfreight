<template>
  <section class="banner-one">
            <div class="banner-one__pattern"
                style="background-image: url(../../assets/img/banner-ong-pattern.png);"></div>
            <div class="banner-one__img aos-init aos-animate" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="0">
                <img :src="getImageUrl(blockData?.masthead_image)" alt="">
            </div>
            <div class="banner-one__shape-3 float-bob-y">
                <img src="../../assets/img/shapes/banner-one-shape-3.png" alt="">
            </div>
            <div class="banner-one__line-shpae1"></div>
            <div class="banner-one__line-shpae2"></div>
            <div class="container">
                <div class="banner-one__inner">
                    <div class="banner-one__content-box">
                        <p class="banner-one__sub-title">{{ translation?.sub_title ?? '' }}</p>
                        <h2 class="banner-one__title">{{ translation?.title ?? '' }} <br>
                            <span class="typed-effect" ref="typedElement"
                                :data-strings="typedStrings">{{ translation?.type_effect ?? '' }}</span>
                        </h2>
                        <p class="banner-one__text" v-html="translation?.content ?? ''"></p>
                        <div class="banner-one__btn-and-review-box">
                            <div class="banner-one__btn-box">
                                <router-link :to="translation?.link ?? '/about'" class="thm-btn">{{ translation?.link_text ?? 'Discover More' }}
                                    <span><i class="icon-right-arrow"></i></span>
                                </router-link>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, watch, ref } from 'vue';
import { useLanguage } from '@/composables/useLanguage';

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

const { getTranslation } = useLanguage();

const blockData = computed(() => props.data);
const translation = computed(() => {
  if (!blockData.value?.translations) return null;
  return getTranslation(blockData.value.translations);
});

const typedElement = ref(null);
let typedInstance = null;
const typedStrings = computed(() => translation.value?.type_effect || '');
const initTypedEffect = () => {
  typedInstance?.destroy();
  typedInstance = null;
  const words = typedStrings.value.split(',').map(word => word.trim()).filter(Boolean);
  if (window.Typed && typedElement.value && words.length) {
    typedInstance = new window.Typed(typedElement.value, {
      strings: words, typeSpeed: 80, backSpeed: 50, backDelay: 1500, loop: true,
    });
  }
};
onBeforeUnmount(() => typedInstance?.destroy());

const getImageUrl = (imageId) => {
  if (!imageId) return '';
  if (imageId.startsWith('http')) return imageId;
  const baseUrl = '/api/frontend';
  const url = `/storage/${imageId}`;
  return url;
};

onMounted(() => {
  initTypedEffect();
});

watch(typedStrings, initTypedEffect, { flush: 'post' });
</script>

<style>
/* Myanmar language font size adjustments */
body.myanmar-lang .banner-one__sub-title {
  font-size: 20px !important;
}

body.myanmar-lang .banner-one__title {
  font-size: 60px !important;
}

body.myanmar-lang .banner-one__title .typed-effect,
body.myanmar-lang .banner-one__title .typed-cursor {
  font-size: 40px !important;
}

body.myanmar-lang .banner-one__text,
body.myanmar-lang .thm-btn {
  font-size: 0.9em !important;
}

/* Thai language font size adjustments */
body.thai-lang .banner-one__sub-title {
  font-size:  20px !important;
}

body.thai-lang .banner-one__title {
  font-size: 60px !important;
}

body.thai-lang .banner-one__title .typed-effect,
body.thai-lang .banner-one__title .typed-cursor {
  font-size: 45px !important;
}

body.thai-lang .banner-one__text,
body.thai-lang .thm-btn {
  font-size: 0.9em !important;
}
</style>
