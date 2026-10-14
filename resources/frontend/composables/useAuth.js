import { ref, computed } from 'vue';
import { apiRequest } from '@/services/http';
const user = ref(null);
const isAuthenticated = computed(() => !!user.value);
let pendingCheck;

export function useAuth() {
  const getCurrentUser = async () => {
    const body = await apiRequest('auth/user', undefined, 'GET');
    user.value = body.data;
    return user.value;
  };
  const checkAuth = async () => {
    if (!pendingCheck) pendingCheck = getCurrentUser().finally(() => { pendingCheck = null; });
    try { return await pendingCheck; } catch { user.value = null; return null; }
  };
  const authenticate = async (path, data) => {
    try {
      const body = await apiRequest(path, data);
      user.value = body.data;
      return { success: true, user: user.value };
    } catch (error) { return { success: false, error: error.message }; }
  };
  const passwordAction = async (path, data) => {
    try { await apiRequest(path, data); return { success: true }; }
    catch (error) { return { success: false, error: error.message }; }
  };
  const logout = async () => {
    await apiRequest('auth/logout', {});
    user.value = null;
    sessionStorage.removeItem('editQuoteData');
  };
  return {
    user, isAuthenticated, checkAuth, getCurrentUser, logout,
    login: (email, password) => authenticate('auth/login', { email, password }),
    register: (data) => authenticate('auth/register', data),
    requestPasswordReset: (email) => passwordAction('auth/password/request', { email }),
    resetPassword: (token, password, email) => passwordAction('auth/password/reset', {
      token, password, email,
    }),
  };
}
