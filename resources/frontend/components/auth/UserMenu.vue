<template>
  <div class="user-menu">
    <!-- Logged Out State -->
    <div v-if="!isAuthenticated" class="auth-buttons">
      <button @click="openAuthModal" class="btn-login">
        <span class="icon-user-1"></span> Login
      </button>
    </div>

    <!-- Logged In State -->
    <div v-else class="user-dropdown dropdown">
      <button 
        class="user-dropdown__toggle dropdown-toggle" 
        type="button" 
        id="userDropdown" 
        data-bs-toggle="dropdown" 
        aria-expanded="false"
      >
        <span class="icon-user-1"></span>
        <span class="user-name">{{ userName }}</span>
      </button>
      
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li>
          <router-link to="/profile" class="dropdown-item">
            <span class="icon-user-1"></span> My Profile
          </router-link>
        </li>
        <li>
          <router-link to="/orders" class="dropdown-item">
            <span class="icon-box1"></span> My Orders
          </router-link>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a href="#" @click.prevent="handleLogout" class="dropdown-item text-danger">
            <span class="icon-logout"></span> Logout
          </a>
        </li>
      </ul>
    </div>

    <!-- Auth Modal -->
    <AuthModal v-model="showAuthModal" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import AuthModal from './AuthModal.vue';
import Swal from 'sweetalert2';

const router = useRouter();
const { user, isAuthenticated, logout, checkAuth } = useAuth();
const showAuthModal = ref(false);

const userName = computed(() => {
  if (!user.value) return '';
  return user.value.first_name || user.value.email;
});

const openAuthModal = () => {
  showAuthModal.value = true;
};

const handleLogout = async () => {
  const result = await Swal.fire({
    title: 'Logout',
    text: 'Are you sure you want to logout?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Logout',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#e03e2d',
    cancelButtonColor: '#6c757d'
  });

  if (result.isConfirmed) {
    await logout();
    
    Swal.fire({
      title: 'Logged Out!',
      text: 'You have been logged out successfully',
      icon: 'success',
      confirmButtonColor: '#e03e2d',
      timer: 2000
    });

    router.push('/');
  }
};

// Check auth on mount
onMounted(() => {
  checkAuth();
});
</script>

<style scoped>
.user-menu {
  display: flex;
  align-items: center;
}

.btn-login {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #e03e2d;
  color: #fff;
  border: none;
  border-radius: 5px;
  text-decoration: none;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-login:hover {
  background: #c02d1d;
  color: #fff;
}

.user-dropdown__toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: transparent;
  border: 2px solid #e03e2d;
  color: #e03e2d;
  border-radius: 5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.user-dropdown__toggle:hover,
.user-dropdown__toggle:focus {
  background: #e03e2d;
  color: #fff;
}

.dropdown-menu {
  min-width: 200px;
  border: none;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  border-radius: 5px;
  padding: 10px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 20px;
  color: #333;
  transition: all 0.3s ease;
}

.dropdown-item:hover {
  background: #f5f5f5;
  color: #e03e2d;
}

.dropdown-item.text-danger {
  color: #dc3545 !important;
}

.dropdown-item.text-danger:hover {
  background: #fff5f5;
  color: #dc3545 !important;
}
</style>
