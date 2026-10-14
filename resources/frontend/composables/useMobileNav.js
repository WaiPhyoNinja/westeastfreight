import { ref } from 'vue';

const isMobileNavOpen = ref(false);

export function useMobileNav() {
  const toggleMobileNav = () => {
    isMobileNavOpen.value = !isMobileNavOpen.value;
    
    // Toggle body lock class
    if (isMobileNavOpen.value) {
      document.body.classList.add('locked');
    } else {
      document.body.classList.remove('locked');
    }
  };

  const closeMobileNav = () => {
    isMobileNavOpen.value = false;
    document.body.classList.remove('locked');
  };

  const openMobileNav = () => {
    isMobileNavOpen.value = true;
    document.body.classList.add('locked');
  };

  return {
    isMobileNavOpen,
    toggleMobileNav,
    closeMobileNav,
    openMobileNav
  };
}
