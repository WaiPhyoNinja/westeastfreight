import { apiRequest } from './http';

// Adapter for the original Vue component contract, backed only by Laravel.
export const readItems = (collection, query = {}) => ({ collection, query });
export const createItem = (collection, data) => ({ collection, data, method: 'POST' });
export default {
  async request({ collection, query = {}, data, method }) {
    if (method === 'POST') return apiRequest(`items/${collection}`, data);
    const params = new URLSearchParams();
    for (const [field, condition] of Object.entries(query.filter || {})) {
      if (condition._eq !== undefined) params.set(`filter[${field}][_eq]`, condition._eq);
    }
    const response = await fetch(`/api/frontend/items/${collection}?${params}`, { headers: { Accept: 'application/json' } });
    const body = await response.json();
    if (!response.ok) throw new Error(body.message || 'Unable to load content');
    return body.data;
  }
};
