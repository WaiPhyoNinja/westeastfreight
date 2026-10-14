import { ref, computed } from 'vue';

// Global language state
const currentLanguage = ref('en-US');

export function useLanguage() {
  // Map short codes to full language codes
  const langMap = {
    'en': 'en-US',
    'mm': 'mm',
    'th': 'th'
  };

  const setLanguage = (lang) => {
    const mappedLang = langMap[lang] || lang;
    currentLanguage.value = mappedLang;
    localStorage.setItem('language', mappedLang);
  };

  const initLanguage = () => {
    const savedLang = localStorage.getItem('language');
    if (savedLang) {
      currentLanguage.value = savedLang;
    }
  };

  const getTranslation = (translations) => {
    if (!translations || !Array.isArray(translations)) return null;
    
    // Try to find the current language
    let translation = translations.find(
      t => t.languages_code === currentLanguage.value
    );
    
    // Fallback to English if current language not found
    if (!translation) {
      translation = translations.find(t => t.languages_code === 'en-US');
    }
    
    // Fallback to first translation if English not found
    if (!translation && translations.length > 0) {
      translation = translations[0];
    }
    
    return translation;
  };

  return {
    currentLanguage: computed(() => currentLanguage.value),
    setLanguage,
    initLanguage,
    getTranslation
  };
}
