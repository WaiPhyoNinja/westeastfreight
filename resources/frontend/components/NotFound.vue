<template>
    <section class="error-page">
        <div class="container">
            <div class="error-page__inner text-center">
                <div class="error-page__img float-bob-y">
                    <img src="/assets/img/error-page-img1.png" alt="">
                </div>

                <div class="error-page__content">
                    <h2>{{ title }}</h2>
                    <p>{{ message }}</p>
                    <div class="btn-box">
                        <a href="/" class="thm-btn">{{ backToHome }}<span><i class="icon-right-arrow"></i></span></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { useLanguage } from '@/composables/useLanguage';

const { currentLanguage } = useLanguage();

const title = computed(() => {
    const translations = {
        'en-US': 'Oops! Page Not Found!',
        'mm': 'စာမျက်နှာ ရှာမတွေ့ပါ!',
        'th': 'ไม่พบหน้า!'
    };
    return translations[currentLanguage.value] || translations['en-US'];
});

const message = computed(() => {
    const translations = {
        'en-US': 'The page you are looking for does not exist. It might have been moved or deleted.',
        'mm': 'သင်ရှာနေသော စာမျက်နှာမရှိပါ။ ၎င်းကို ရွှေ့ခြင်း သို့မဟုတ် ဖျက်ခြင်းခံရခြင်း ဖြစ်နိုင်သည်။',
        'th': 'ไม่พบหน้าที่คุณกำลังค้นหา อาจถูกย้ายหรือลบไปแล้ว'
    };
    return translations[currentLanguage.value] || translations['en-US'];
});

const backToHome = computed(() => {
    const translations = {
        'en-US': 'Back To Home',
        'mm': 'ပင်မစာမျက်နှာသို့',
        'th': 'กลับหน้าแรก'
    };
    return translations[currentLanguage.value] || translations['en-US'];
});
</script>

<style scoped>
.error-page {
    padding: 120px 0;
}

.error-page__inner {
    text-align: center;
}

.error-page__img {
    margin-bottom: 50px;
}

.error-page__img img {
    max-width: 100%;
    height: auto;
}

.error-page__content h2 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1a1a1a;
}

.error-page__content p {
    font-size: 18px;
    margin-bottom: 40px;
    color: #666;
}

.btn-box {
    margin-top: 30px;
}

/* Myanmar Language Font Sizes */
body.myanmar-lang .error-page__content h2 {
    font-size: 38px;
}

body.myanmar-lang .error-page__content p {
    font-size: 16px;
}

/* Thai Language Font Sizes */
body.thai-lang .error-page__content h2 {
    font-size: 42px;
}

body.thai-lang .error-page__content p {
    font-size: 17px;
}
</style>
