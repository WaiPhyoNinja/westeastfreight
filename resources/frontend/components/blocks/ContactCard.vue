<template>
    <section class="contact-info">
        <div class="container">
            <div class="row">
                <!--Contact Card Single Start-->
                <div 
                    v-for="(card, index) in contactCards" 
                    :key="card.id"
                    class="col-xl-3 col-lg-6 wow fadeInLeft animated" 
                    :data-wow-delay="`${(index + 1) * 100}ms`"
                    :style="`visibility: visible; animation-delay: ${(index + 1) * 100}ms; animation-name: fadeInLeft;`"
                >
                    <div class="contact-info__single">
                        <div class="contact-info__icon">
                            <img v-if="card.imageUrl" :src="card.imageUrl" alt="" style="width: 50px; height: 50px;">
                        </div>
                        <p>{{ card.title }}</p>
                        <div v-html="card.description"></div>
                    </div>
                </div>
                <!--Contact Card Single End-->
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
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
const contactData = ref(null);

const contactCards = computed(() => {
    if (!props.data?.card || props.data.card.length === 0) {
        return [];
    }

    return props.data.card.map(item => {
        // Check if contact_card_id exists
        if (!item || !item.contact_card_id) {
            console.warn('Missing contact_card_id in item:', item);
            return null;
        }

        const cardData = item.contact_card_id;
        
        // Check if translations exist
        if (!cardData.translations || !Array.isArray(cardData.translations)) {
            console.warn('Missing or invalid translations in cardData:', cardData);
            return null;
        }

        // Find translation for current language or fallback to English
        const translation = cardData.translations.find(
            t => t.languages_code === currentLanguage.value
        ) || cardData.translations.find(
            t => t.languages_code === 'en-US'
        );

        if (!translation) {
            console.warn('No translation found for languages:', currentLanguage.value, cardData.translations);
            return null;
        }

        return {
            id: item.id,
            title: translation.title || '',
            description: translation.description || '',
            imageUrl: translation.image ? `/storage/${translation.image}` : null
        };
    }).filter(card => card !== null);
});
</script>