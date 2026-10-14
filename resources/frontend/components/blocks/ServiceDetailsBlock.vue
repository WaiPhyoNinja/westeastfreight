<template>
  <section class="service-details">
    <div class="container">
      <div class="row">
        <div class="col-xl-4 col-lg-5">
          <aside class="service-details__sidebar">
            <div v-if="serviceLinks.length" class="service-details__services-box">
              <h3 class="service-details__services-title">{{ t.ourServices }}</h3>
              <ul class="service-details__services-list list-unstyled">
                <li v-for="item in serviceLinks" :key="item.id" :class="{ active: item.url === route.path }">
                  <a v-if="isExternal(item.url)" :href="item.url" target="_blank" rel="noopener noreferrer">{{ getTranslation(item.translations)?.title }}<span class="icon-next"></span></a>
                  <router-link v-else :to="item.url || '/services'">{{ getTranslation(item.translations)?.title }}<span class="icon-next"></span></router-link>
                </li>
              </ul>
            </div>
            <div v-if="location" class="service-details__sidebar-contact">
              <div class="service-details__sidebar-contact-img">
                <div class="inner"><img src="/assets/img/service-details-sidebar-img.png" alt=""></div>
              </div>
              <div class="service-details__sidebar-contact-content">
                <p class="help-text">{{ t.ifYouNeedHelp }}<br>{{ t.contactWithUs }}</p>
                <div v-if="location.phone" class="contact-info-item"><div class="content"><h2><a :href="`tel:${location.phone}`">{{ location.phone }}</a></h2></div></div>
                <div v-if="location.address" class="contact-info-item"><div class="content"><p>{{ location.address }}</p></div></div>
                <div v-if="location.opening_hours" class="contact-info-item"><div class="content"><p>{{ location.opening_hours }}</p></div></div>
              </div>
            </div>
          </aside>
        </div>
        <div class="col-xl-8 col-lg-7">
          <div class="service-details__left">
            <div v-if="data.image" class="service-details__img"><img :src="imageUrl(data.image)" :alt="content?.title || ''" :style="{ objectPosition: imagePosition }"></div>
            <h2 v-if="content?.title" class="service-details__title-1">{{ content.title }}</h2>
            <div v-if="content?.description" class="service-details__body" v-html="content.description"></div>
            <ul v-if="content?.bullets?.length" class="service-details__points-list list-unstyled">
              <li v-for="(bullet, index) in content.bullets" :key="index"><div class="icon"><span class="icon-right-arrow"></span></div><p>{{ bullet }}</p></li>
            </ul>
            <div v-if="features.length" class="service-details__img-box">
              <div class="row">
                <div v-for="(feature, index) in features" :key="feature.id ?? index" class="col-xl-6 col-md-6">
                  <div class="service-details__img-box-single">
                    <div v-if="feature.image" class="service-details__img-box-img"><img :src="imageUrl(feature.image)" :alt="getTranslation(feature.translations)?.title || ''" :style="{ objectPosition: imagePosition }"></div>
                    <div class="service-details__img-box-content-icon-and-title">
                      <div class="service-details__img-box-content-icon"><span :class="index === 0 ? 'icon-new-product' : 'icon-customer-loyalty'"></span></div>
                      <h3 class="service-details__img-box-content-title">{{ getTranslation(feature.translations)?.title }}</h3>
                    </div>
                    <div v-if="getTranslation(feature.translations)?.description" class="service-details__img-box-content-text" v-html="getTranslation(feature.translations).description"></div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="content?.faq?.length" class="service-details__faq-box">
              <div v-for="(item, index) in content.faq" :key="index" class="service-details__faq-item">
                <button type="button" class="service-details__faq-question" :aria-expanded="activeFaq === index" @click="activeFaq = activeFaq === index ? -1 : index">
                  {{ item.title }}<span aria-hidden="true">{{ activeFaq === index ? '−' : '+' }}</span>
                </button>
                <div v-if="activeFaq === index" class="service-details__faq-answer" v-html="item.description"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useLanguage } from '@/composables/useLanguage';
import { commonTranslations } from '@/locales/common';
import directus, { readItems } from '@/services/directus';

const props = defineProps({ data: { type: Object, required: true } });
const route = useRoute();
const { currentLanguage, getTranslation } = useLanguage();
const t = computed(() => commonTranslations[currentLanguage.value] || commonTranslations['en-US']);
const content = computed(() => getTranslation(props.data.translations));
const features = computed(() => props.data.features || []);
const imagePosition = computed(() => ({ center: 'center center', left: 'left center', right: 'right center', top: 'center top' })[props.data.image_alignment] || 'center center');
const serviceLinks = ref([]);
const locationData = ref(null);
const location = computed(() => getTranslation(locationData.value?.translations));
const activeFaq = ref(0);
const imageUrl = (path) => !path ? '' : /^(https?:\/\/|\/)/i.test(path) ? path : `/storage/${path}`;
const isExternal = (url) => /^https?:\/\//i.test(url || '');

onMounted(async () => {
  const [menuResult, locationResult] = await Promise.allSettled([
    directus.request(readItems('menu', { filter: { menu_id: { _eq: 'solutions' } } })),
    directus.request(readItems('locations')),
  ]);
  if (menuResult.status === 'fulfilled') serviceLinks.value = menuResult.value?.[0]?.items || [];
  if (locationResult.status === 'fulfilled') locationData.value = locationResult.value?.[0] || null;
});
</script>

<style scoped>
.service-details__img img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
.service-details__img-box-img img { aspect-ratio: 4 / 3; object-fit: cover; }
.service-details__body :deep(p + p) { margin-top: 1rem; }
.service-details__points-list { margin-top: 1.5rem; }
.service-details__sidebar-contact { padding: 0; background: #f4f4f6; }
.service-details__sidebar-contact::before, .service-details__sidebar-contact::after { display: none; }
.service-details__sidebar-contact-img { clip-path: none; }
.service-details__sidebar-contact-img img { display: block; width: 100%; }
.service-details__sidebar-contact-content { margin: 0; padding: 28px 24px; background: #e63e2e; text-align: left; }
.service-details__sidebar-contact-content .help-text,
.service-details__sidebar-contact-content .contact-info-item h2 a,
.service-details__sidebar-contact-content .contact-info-item p { color: #fff; }
.service-details__sidebar-contact-content .help-text { margin-bottom: 20px; padding-bottom: 20px; }
.service-details__sidebar-contact-content .contact-info-item { margin-bottom: 14px; }
.service-details__faq-item { border: 1px solid #e3e8eb; border-radius: 12px; margin-bottom: 12px; overflow: hidden; }
.service-details__faq-question { display: flex; width: 100%; justify-content: space-between; gap: 1rem; padding: 18px 20px; border: 0; color: #092f3a; background: #fff; font-weight: 700; text-align: left; }
.service-details__faq-question[aria-expanded="true"] { color: var(--tanspot-base); }
.service-details__faq-answer { padding: 0 20px 18px; }
@media (max-width: 991px) { .service-details__sidebar { margin-bottom: 40px; } }
</style>
