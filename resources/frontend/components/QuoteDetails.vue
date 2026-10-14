<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { apiFetch } from '@/services/http';
const route = useRoute();
const quote = ref(null), loading = ref(true), error = ref('');
let requestId = 0;
const load = async () => {
  const id = ++requestId;
  loading.value = true; error.value = ''; quote.value = null;
  try {
    const response = await apiFetch(`/api/frontend/items/quote_requests/${route.params.id}`);
    if (!response.ok) throw new Error(response.status === 404 ? 'Quote not found.' : 'Unable to load this quote. Please try again.');
    const body = await response.json();
    if (id === requestId) quote.value = body.data;
  } catch (e) { if (id === requestId) error.value = e.message; }
  finally { if (id === requestId) loading.value = false; }
};
watch(() => route.params.id, load, { immediate: true });
const display = value => value === true ? 'Yes' : value === false ? 'No' : value === null || value === undefined || value === '' ? '—' : value;
const date = value => value && !Number.isNaN(Date.parse(value)) ? new Date(value).toLocaleDateString() : '—';
const fields = [
  ['transport_mode', 'Transport mode'], ['shipment_type', 'Shipment type'], ['service_type', 'Service type'],
  ['incoterms', 'Incoterms'], ['departure_type', 'Departure'], ['arrival_type', 'Arrival'], ['arrival_date', 'Required arrival date'],
  ['commodity_type', 'Commodity'], ['packaging', 'Packaging'], ['container_type', 'Container type'],
  ['weight', 'Weight (kg)'], ['quantity', 'Packages'], ['length', 'Length (cm)'], ['width', 'Width (cm)'], ['height', 'Height (cm)'],
  ['declared_value', 'Declared value ($)'], ['stackable', 'Stackable'], ['insurance', 'Insurance'],
];
const contactFields = [['company','Company'],['email','Email'],['phone','Phone'],['address','Address'],['city','City'],['state','State / Region'],['postal_code','Postal code'],['country','Country']];
</script>
<template>
  <div class="quote-detail-page">
    <router-link to="/my-orders" class="back-link">← My Request Quote</router-link>
    <p v-if="loading" role="status">Loading quote…</p>
    <div v-else-if="error" role="alert" class="detail-card"><p>{{ error }}</p><button type="button" @click="load">Try again</button></div>
    <template v-else-if="quote">
      <header class="detail-heading"><div><h1>Quote #{{ quote.id }}</h1><p>Submitted {{ date(quote.date_created) }}</p></div><span class="detail-status">{{ quote.status }}</span></header>
      <div class="contact-grid">
        <section v-for="party in ['sender','receiver']" :key="party" class="detail-card">
          <table class="detail-table">
            <caption>{{ party === 'sender' ? 'From — Sender' : 'To — Receiver' }}</caption>
            <tbody>
              <tr><th scope="row">Name</th><td>{{ display([quote[`${party}_first_name`], quote[`${party}_last_name`]].filter(Boolean).join(' ')) }}</td></tr>
              <tr v-for="[key,label] in contactFields" :key="key"><th scope="row">{{ label }}</th><td>{{ display(quote[`${party}_${key}`]) }}</td></tr>
            </tbody>
          </table>
        </section>
      </div>
      <section class="detail-card">
        <h2 class="section-heading">Shipment details</h2>
        <div class="shipment-grid">
          <table v-for="(group,index) in [fields.slice(0,9), fields.slice(9)]" :key="index" class="detail-table" :aria-label="index === 0 ? 'Shipment and route information' : 'Package specifications'">
            <tbody><tr v-for="[key,label] in group" :key="key"><th scope="row">{{ label }}</th><td>{{ display(quote[key]) }}</td></tr></tbody>
          </table>
        </div>
      </section>
      <section class="detail-card">
        <table class="detail-table notes-table">
          <caption>Cargo &amp; instructions</caption>
          <tbody>
            <tr><th scope="row">Description</th><td>{{ display(quote.description) }}</td></tr>
            <template v-for="[key,label] in [['special_instructions','Special instructions'],['hazardous_details','Hazardous cargo details'],['temperature_range','Temperature range']]" :key="key"><tr v-if="quote[key]"><th scope="row">{{ label }}</th><td>{{ quote[key] }}</td></tr></template>
          </tbody>
        </table>
      </section>
    </template>
  </div>
</template>
<style scoped>
.quote-detail-page{max-width:1180px;margin:0 auto;color:#062f3a}
.back-link{display:inline-flex;align-items:center;color:#56666b;font-size:13px;margin-bottom:20px;text-decoration:none}
.back-link:hover{color:#fd5523}
.detail-heading{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}
.detail-heading h1{font-size:27px;font-weight:700;margin:0 0 6px}
.detail-heading p{margin:0;color:#748086;font-size:13px}
.detail-status{background:#fff1e8;border:1px solid #ffd8c0;color:#b54718;border-radius:20px;padding:7px 16px;font-size:12px;font-weight:600;text-transform:capitalize}
.detail-card{background:white;border:1px solid #dfe6e9;border-radius:10px;overflow:hidden;margin-bottom:22px;min-width:0;box-shadow:0 2px 8px #062f3a03}
.contact-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:22px}
.detail-table{width:100%;table-layout:fixed;border-collapse:collapse;font-size:13px;line-height:1.75;margin:0}
.detail-table caption,.section-heading{caption-side:top;text-align:left;padding:16px 20px;margin:0;background:#062f3a;color:#fff;font-size:15px;font-weight:600;line-height:1.5}
.detail-table th,.detail-table td{padding:13px 18px;border-bottom:1px solid #e8edef;vertical-align:top;overflow-wrap:anywhere;word-break:normal;text-align:left}
.detail-table th{width:36%;font-weight:500;color:#65777d;background:#f6f8f9;border-right:1px solid #e8edef}
.detail-table td{color:#183c46;white-space:pre-wrap}
.detail-table tr:nth-child(even) td{background:#fcfdfd}
.detail-table tr:last-child th,.detail-table tr:last-child td{border-bottom:0}
.shipment-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr)}
.shipment-grid table+table{border-left:1px solid #dfe6e9}
.notes-table th{width:22%}
.quote-detail-page>div[role="alert"]{padding:24px}
button{border:0;border-radius:6px;padding:10px 16px;background:#062f3a;color:white}
@media(max-width:767px){.contact-grid,.shipment-grid{grid-template-columns:minmax(0,1fr);gap:0}.shipment-grid table+table{border-left:0;border-top:1px solid #e8edef}.detail-heading h1{font-size:23px}.detail-table th,.detail-table td{padding:12px;font-size:12px}.detail-table th,.notes-table th{width:37%}.detail-table caption,.section-heading{padding:14px 16px;font-size:14px}.detail-card{margin-bottom:18px}}
</style>
