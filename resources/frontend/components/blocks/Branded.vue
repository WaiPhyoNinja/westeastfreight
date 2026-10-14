<script setup>
import { computed } from 'vue';
const props = defineProps({ data: { type: Object, required: true } });
const images = computed(() => (props.data.images || []).map(image => {
  const path = typeof image === 'object' ? image.directus_files_id : image;
  return !path ? null : /^(https?:\/\/|\/)/i.test(path) ? path : `/storage/${path}`;
}).filter(Boolean));
</script>
<template>
  <section v-if="images.length" class="brand-one">
    <div class="container brand-grid">
      <div v-for="(image, index) in images" :key="index" class="brand-one__single">
        <img :src="image" alt="" width="115" height="105" loading="lazy">
      </div>
    </div>
  </section>
</template>
<style scoped>
.brand-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; }
.brand-grid img { max-width: 100%; object-fit: contain; }
</style>
