<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({ data: { type: Object, required: true } });
const images = computed(() => (props.data.images || []).map(image => {
  const path = typeof image === 'object' ? image.directus_files_id : image;
  return !path ? null : /^(https?:\/\/|\/)/i.test(path) ? path : `/storage/${path}`;
}).filter(Boolean));

const track = ref(null);
let autoplay = null;

function scrollLogos(direction) {
  const element = track.value;
  if (!element) return;

  const item = element.querySelector('.brand-one__single');
  if (!item) return;

  const gap = parseFloat(getComputedStyle(element).columnGap) || 0;
  const distance = item.getBoundingClientRect().width + gap;
  const atEnd = element.scrollLeft >= element.scrollWidth - element.clientWidth - 2;

  if (direction > 0 && atEnd) {
    element.scrollTo({ left: 0, behavior: 'smooth' });
  } else {
    element.scrollBy({ left: distance * direction, behavior: 'smooth' });
  }
}

function stopAutoplay() {
  if (autoplay) clearInterval(autoplay);
  autoplay = null;
}

function startAutoplay() {
  stopAutoplay();
  if (images.value.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  autoplay = window.setInterval(() => scrollLogos(1), 4500);
}

onMounted(startAutoplay);
onBeforeUnmount(stopAutoplay);
</script>

<template>
  <section v-if="images.length" class="brand-one">
    <div class="container brand-carousel"
      @mouseenter="stopAutoplay" @mouseleave="startAutoplay"
      @focusin="stopAutoplay" @focusout="startAutoplay"
      @touchstart.passive="stopAutoplay">
      <button v-if="images.length > 1" type="button" class="brand-carousel__button" aria-label="Previous brand logos" @click="scrollLogos(-1)">‹</button>
      <div ref="track" class="brand-track" role="region" aria-label="Brand logos" tabindex="0">
        <div v-for="(image, index) in images" :key="index" class="brand-one__single">
          <img :src="image" :alt="`Brand logo ${index + 1}`" width="150" height="105" loading="lazy">
        </div>
      </div>
      <button v-if="images.length > 1" type="button" class="brand-carousel__button" aria-label="Next brand logos" @click="scrollLogos(1)">›</button>
    </div>
  </section>
</template>

<style scoped>
.brand-carousel { display: flex; align-items: center; gap: 12px; }
.brand-track { display: flex; flex: 1; gap: 24px; min-width: 0; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.5) transparent; }
.brand-track:focus-visible, .brand-carousel__button:focus-visible { outline: 2px solid currentColor; outline-offset: 3px; }
.brand-one__single { display: flex; flex: 0 0 clamp(180px, 22vw, 240px); align-items: center; justify-content: center; min-height: 110px; scroll-snap-align: start; }
.brand-one__single img { display: block; max-width: 100%; max-height: 105px; width: auto; height: auto; object-fit: contain; }
.brand-carousel__button { display: grid; flex: 0 0 36px; place-items: center; width: 36px; height: 36px; border: 0; border-radius: 50%; color: #092f3a; background: #fff; font-size: 30px; line-height: 1; cursor: pointer; }
@media (max-width: 600px) {
  .brand-carousel { gap: 5px; }
  .brand-track { gap: 12px; }
  .brand-one__single { flex-basis: 130px; }
  .brand-carousel__button { flex-basis: 30px; width: 30px; height: 30px; }
}
</style>
