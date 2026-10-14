<template>
    <div class="page-wrapper">
        <Header />
        <section class="freight-heading"><div class="container"><p>FREIGHT QUOTATION</p><h1>{{ editQuoteId ? 'Edit Quote Request' : t.pageTitle }}</h1></div></section>
        <main class="freight-page container">
            <div class="freight-intro"><h2>{{ f.heading }}</h2><p>{{ f.intro }}</p></div>
            <ol class="freight-progress" aria-label="Quote request progress">
                <li v-for="(step, index) in steps" :key="index" :class="{ active: currentStep === index + 1, complete: currentStep > index + 1 }" :aria-current="currentStep === index + 1 ? 'step' : undefined"><span>{{ currentStep > index + 1 ? '✓' : index + 1 }}</span>{{ step.label }}</li>
            </ol>
            <p v-if="loadingEdit" role="status">Loading saved quote…</p>
            <div v-else-if="editError" role="alert"><p>{{ editError }}</p><button type="button" @click="router.go(0)">Try again</button></div>
            <form v-else ref="quoteForm" class="freight-form" @submit.prevent="currentStep < 3 ? nextStep() : handleSubmit()">
                <fieldset v-if="currentStep === 1" class="freight-card">
                    <legend><span>1</span>{{ f.routing }}</legend>
                    <div class="freight-grid">
                        <label>{{ f.mode }} *<select v-model="formData.freight.transport_mode" required><option value="multimodal">{{ f.multimodal }}</option><option value="sea">{{ f.sea }}</option><option value="air">{{ f.air }}</option><option value="road">{{ f.road }}</option></select></label>
                        <label>Incoterms<select v-model="formData.freight.incoterms"><option value="unknown">{{ f.unknown }}</option><option v-for="term in incoterms" :key="term" :value="term">{{ term }}</option></select></label>
                    </div>
                    <div class="route-grid">
                        <div class="route-panel"><h3><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ f.origin }}</h3><div class="freight-grid">
                            <label>{{ t.country }} *<select v-model="formData.sender.country" required><option value="">{{ t.selectCountry }}</option><option v-for="country in countries" :key="country.id" :value="country.country_name">{{ country.country_name }}</option></select></label>
                            <label>{{ t.city }} *<input v-model="formData.sender.city" required maxlength="255" :placeholder="t.enterCity" /></label>
                            <label class="full">{{ f.departure }}<select v-model="formData.freight.departure_type"><option v-for="place in locations" :key="place.value" :value="place.value">{{ place.label }}</option></select></label>
                        </div></div>
                        <div class="route-panel"><h3><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ f.destination }}</h3><div class="freight-grid">
                            <label>{{ t.country }} *<select v-model="formData.receiver.country" required><option value="">{{ t.selectCountry }}</option><option v-for="country in countries" :key="country.id" :value="country.country_name">{{ country.country_name }}</option></select></label>
                            <label>{{ t.city }} *<input v-model="formData.receiver.city" required maxlength="255" :placeholder="t.enterCity" /></label>
                            <label class="full">{{ f.arrival }}<select v-model="formData.freight.arrival_type"><option v-for="place in locations" :key="place.value" :value="place.value">{{ place.label }}</option></select></label>
                        </div></div>
                    </div>
                    <div v-if="countryError" class="form-error" role="alert">{{ f.countryError }} <button type="button" @click="fetchCountries">{{ f.retry }}</button></div>
                    <div class="freight-grid"><label>{{ f.arrivalDate }} *<input type="date" v-model="formData.freight.arrival_date" required /></label></div>
                </fieldset>
                <template v-if="currentStep === 2">
                    <fieldset class="freight-card"><legend><span>2</span>{{ f.commodity }}</legend><div class="freight-grid">
                        <label>{{ f.cargoType }} *<select v-model="formData.freight.commodity_type" required><option value="general">{{ f.general }}</option><option value="hazardous">{{ f.hazardous }}</option><option value="perishable">{{ f.perishable }}</option></select></label>
                        <label v-if="formData.freight.commodity_type === 'hazardous'">UN / IMO<input v-model="formData.freight.hazardous_details" maxlength="255" /></label>
                        <label v-if="formData.freight.commodity_type === 'perishable'">{{ f.temperature }} *<input v-model="formData.freight.temperature_range" required maxlength="255" placeholder="2–8 °C" /></label>
                        <label class="full">{{ t.packageDescription }} *<textarea v-model="formData.shipment.description" required maxlength="5000" rows="3" :placeholder="t.describePackage"></textarea></label>
                    </div></fieldset>
                    <fieldset class="freight-card"><legend><span>3</span>{{ f.specifications }}</legend><p class="field-hint">{{ f.metric }}</p><div class="freight-grid">
                        <label>{{ f.packaging }} *<select v-model="formData.freight.packaging" required><option value="">{{ f.select }}</option><option v-for="item in packagingOptions" :key="item.value" :value="item.value">{{ item.label }}</option></select></label>
                        <label>{{ t.quantity }} *<input type="number" v-model="formData.shipment.quantity" min="1" max="1000000" step="1" required /></label>
                        <label>{{ t.weight }} *<input type="number" v-model="formData.shipment.weight" min="0.001" max="100000000" step="any" required /></label>
                        <label>{{ t.serviceType }} *<select v-model="formData.shipment.service" required><option value="">{{ t.selectServiceType }}</option><option value="standard">{{ t.standardDelivery }}</option><option value="express">{{ t.expressDelivery }}</option><option value="overnight">{{ t.overnight }}</option><option value="economy">{{ t.economy }}</option></select></label>
                        <label v-if="formData.freight.packaging === 'container'" class="full">{{ f.container }} *<select v-model="formData.freight.container_type" required><option value="">{{ f.select }}</option><option v-for="item in containers" :key="item" :value="item">{{ item }}</option></select></label>
                    </div><div class="dimensions"><label v-for="dimension in ['length', 'width', 'height']" :key="dimension">{{ t[dimension] }}<input type="number" v-model="formData.shipment[dimension]" min="0.001" max="1000000" step="any" /></label></div>
                    <label class="check"><input type="checkbox" v-model="formData.freight.stackable" />{{ f.stackable }}</label>
                    </fieldset>
                    <fieldset class="freight-card"><legend><span>4</span>{{ f.notes }}</legend><label class="check"><input type="checkbox" v-model="formData.shipment.insurance" />{{ t.insurance }}</label><label>{{ t.specialInstructions }}<textarea v-model="formData.shipment.specialInstructions" maxlength="5000" rows="3" :placeholder="t.specialHandling"></textarea></label><label class="value-field">{{ t.declaredValue }}<input type="number" v-model="formData.shipment.declaredValue" min="0" max="1000000000000" step="any" /></label></fieldset>
                </template>
                <template v-if="currentStep === 3">
                    <fieldset v-for="party in ['sender', 'receiver']" :key="party" class="freight-card"><legend><span><i class="fas fa-user" aria-hidden="true"></i></span>{{ party === 'sender' ? t.senderTitle : t.receiverTitle }}</legend><p class="field-hint">{{ formData[party].city }}, {{ formData[party].country }}</p><div class="freight-grid">
                        <label v-for="field in contactFields" :key="field.key" :class="{ full: field.key === 'address' }">{{ t[field.label] }}{{ field.required ? ' *' : '' }}<input v-model="formData[party][field.key]" :type="field.type || 'text'" :required="field.required" maxlength="255" /></label>
                    </div></fieldset>
                    <div class="account-notice"><i class="fas fa-lock" aria-hidden="true"></i> {{ isAuthenticated ? user?.email : t.loginRequiredText }}</div>
                </template>
                <div class="freight-actions"><button v-if="currentStep > 1" type="button" class="back-button" @click="prevStep" :disabled="isSubmitting">← {{ t.previous }}</button><span class="required-note">* {{ f.required }}</span><button type="submit" class="next-button thm-btn" :disabled="isSubmitting || countries.length === 0">{{ currentStep < 3 ? t.nextStep : (isSubmitting ? t.submitting : t.submitQuote) }}<span aria-hidden="true"><i class="icon-right-arrow"></i></span></button></div>
            </form>
            <div class="freight-help"><h2>{{ t.whatHappens }}</h2><div><p><b>01</b>{{ t.step1 }}</p><p><b>02</b>{{ t.step2 }}</p><p><b>03</b>{{ t.step4 }}</p></div></div>

        </main><Footer />
    </div><MobileSlider />
</template>
<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Footer from '../layouts/Footer.vue'
import Header from '../layouts/Header.vue'
import MobileSlider from './MobileSlider.vue'
import { useAuth } from '@/composables/useAuth'
import { useLanguage } from '@/composables/useLanguage'
import { quoteTranslations } from '@/locales/quote'
import { apiRequest } from '@/services/http'
import Swal from 'sweetalert2'
import { freightTranslations } from '@/locales/freight'

const router = useRouter()
const route = useRoute()
const loadingEdit = ref(false)
const editError = ref('')
const { isAuthenticated, user, checkAuth } = useAuth()
const { currentLanguage } = useLanguage()
const isSubmitting = ref(false)
const currentStep = ref(1)
const editQuoteId = ref(null)
const countries = ref([]);

// Translation helper
const useQuoteTranslation = (lang) => {
    return quoteTranslations[lang] || quoteTranslations['en-US']
}

// Computed translation object
const t = computed(() => useQuoteTranslation(currentLanguage.value))

const f = computed(() => freightTranslations[currentLanguage.value] || freightTranslations['en-US'])
const steps = computed(() => [ { label: f.value.routing }, { label: f.value.cargo }, { label: f.value.contacts } ])
const quoteForm = ref(null)
const countryError = ref(false)
const incoterms = ['EXW', 'FCA', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP', 'FAS', 'FOB', 'CFR', 'CIF']
const containers = ["20' Standard", "20' Reefer", "20' Flat Rack", "20' Open Top", "40' Standard", "40' High Cube", "40' Reefer", "40' Flat Rack", "40' Open Top"]
const locations = computed(() => ['door', 'port', 'airport', 'warehouse'].map(value => ({ value, label: f.value[value] })))
const packagingOptions = computed(() => ['pallet', 'boxes', 'drums', 'bags', 'vehicle', 'unpacked', 'container'].map(value => ({ value, label: f.value[value] })))
const contactFields = [
    { key: 'firstName', label: 'firstName', required: true }, { key: 'lastName', label: 'lastName', required: true },
    { key: 'email', label: 'email', type: 'email', required: true }, { key: 'phone', label: 'phone', type: 'tel', required: true },
    { key: 'company', label: 'companyName' }, { key: 'postalCode', label: 'postalCode' },
    { key: 'address', label: 'address', required: true }, { key: 'state', label: 'state' },
]
const freightDefaults = () => ({ transport_mode: 'multimodal', incoterms: 'unknown', departure_type: 'door', arrival_type: 'door', arrival_date: '', commodity_type: 'general', hazardous_details: '', temperature_range: '', packaging: '', container_type: 'freight', stackable: false })

const formData = ref({
    freight: freightDefaults(),
    sender: {
        firstName: '',
        lastName: '',
        company: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        country: '',
        postalCode: ''
    },
    receiver: {
        firstName: '',
        lastName: '',
        company: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        country: '',
        postalCode: ''
    },
    shipment: {
        type: 'freight',
        service: '',
        weight: '',
        length: '',
        width: '',
        height: '',
        quantity: 1,
        declaredValue: '',
        description: '',
        specialInstructions: '',
        insurance: false
    }
})

const fetchCountries = async () => {
    countryError.value = false
    try {
        const baseUrl = '/api/frontend';
        const response = await fetch(`${baseUrl}/items/quote_country?fields=id,country_name,value`);
        if (!response.ok) throw new Error('Country list unavailable')
        const json = await response.json();
        if (!Array.isArray(json.data) || json.data.length === 0) throw new Error('Country list unavailable')
        countries.value = json.data;
    } catch (error) {
        countryError.value = true
    }
};


// Vue escapes displayed text; keep stored customer input unencoded.
const sanitizeInput = value => typeof value === 'string' ? value.trim() : value

const handleSubmit = async () => {
    if (isSubmitting.value) return
    for (const step of [1, 2, 3]) {
        currentStep.value = step
        await nextTick()
        if (!quoteForm.value.reportValidity()) return
    }
    // Check if user is authenticated before submitting
    await checkAuth()
    
    if (!isAuthenticated.value) {
        const result = await Swal.fire({
            title: t.value.loginTitle,
            text: t.value.loginText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e03e2d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: t.value.yesLogin,
            cancelButtonText: t.value.cancel
        })

        if (result.isConfirmed) {
            // Save form data to sessionStorage before redirecting
            sessionStorage.setItem('pendingQuoteRequest', JSON.stringify(formData.value))
            sessionStorage.setItem('quoteRequestStep', currentStep.value.toString())
            
            // Redirect to login page
            router.push('/login')
        }
        return
    }

    isSubmitting.value = true

    try {
        // Prepare the quotation payload.
        const quoteData = {
            ...formData.value.freight,
            hazardous_details: formData.value.freight.commodity_type === 'hazardous' ? formData.value.freight.hazardous_details : null,
            temperature_range: formData.value.freight.commodity_type === 'perishable' ? formData.value.freight.temperature_range : null,
            container_type: formData.value.freight.packaging === 'container' ? formData.value.freight.container_type : null,
            // Sender information
            sender_first_name: sanitizeInput(formData.value.sender.firstName),
            sender_last_name: sanitizeInput(formData.value.sender.lastName),
            sender_company: sanitizeInput(formData.value.sender.company) || null,
            sender_email: sanitizeInput(formData.value.sender.email),
            sender_phone: sanitizeInput(formData.value.sender.phone),
            sender_address: sanitizeInput(formData.value.sender.address),
            sender_city: sanitizeInput(formData.value.sender.city),
            sender_state: sanitizeInput(formData.value.sender.state),
            sender_country: sanitizeInput(formData.value.sender.country),
            sender_postal_code: sanitizeInput(formData.value.sender.postalCode) || null,
            
            // Receiver information
            receiver_first_name: sanitizeInput(formData.value.receiver.firstName),
            receiver_last_name: sanitizeInput(formData.value.receiver.lastName),
            receiver_company: sanitizeInput(formData.value.receiver.company) || null,
            receiver_email: sanitizeInput(formData.value.receiver.email),
            receiver_phone: sanitizeInput(formData.value.receiver.phone),
            receiver_address: sanitizeInput(formData.value.receiver.address),
            receiver_city: sanitizeInput(formData.value.receiver.city),
            receiver_state: sanitizeInput(formData.value.receiver.state),
            receiver_country: sanitizeInput(formData.value.receiver.country),
            receiver_postal_code: sanitizeInput(formData.value.receiver.postalCode) || null,
            
            // Shipment information
            shipment_type: sanitizeInput(formData.value.shipment.type),
            service_type: sanitizeInput(formData.value.shipment.service),
            weight: parseFloat(formData.value.shipment.weight),
            length: formData.value.shipment.length ? parseFloat(sanitizeInput(formData.value.shipment.length)) : null,
            width: formData.value.shipment.width ? parseFloat(sanitizeInput(formData.value.shipment.width)) : null,
            height: formData.value.shipment.height ? parseFloat(sanitizeInput(formData.value.shipment.height)) : null,
            quantity: parseInt(sanitizeInput(formData.value.shipment.quantity)),
            declared_value: formData.value.shipment.declaredValue !== '' && formData.value.shipment.declaredValue != null ? parseFloat(sanitizeInput(formData.value.shipment.declaredValue)) : null,
            description: sanitizeInput(formData.value.shipment.description),
            special_instructions: sanitizeInput(formData.value.shipment.specialInstructions) || null,
            insurance: sanitizeInput(formData.value.shipment.insurance),
            status: 'pending'
        }

        await apiRequest(
            editQuoteId.value ? `items/quote_requests/${editQuoteId.value}` : 'items/quote_requests',
            quoteData,
            editQuoteId.value ? 'PATCH' : 'POST'
        )

        await Swal.fire({
            title: t.value.successTitle,
            text: editQuoteId.value ? 'Quote request updated successfully!' : t.value.successText,
            icon: 'success',
            confirmButtonColor: '#e03e2d',
            timer: 3000
        })

        // Clear saved form data
        sessionStorage.removeItem('pendingQuoteRequest')
        sessionStorage.removeItem('quoteRequestStep')
        sessionStorage.removeItem('editQuoteData')
        
        resetForm()
        currentStep.value = 1
        
        // Redirect to My Account if updating
        if (editQuoteId.value) {
            editQuoteId.value = null
            router.push('/my-orders')
            return
        }
        
        editQuoteId.value = null
    } catch (error) {
        console.error('Error submitting quote:', error)
        
        let errorMessage = error.message || t.value.errorText
        
        // Handle specific error cases
        if (error.response?.status === 401) {
            errorMessage = 'Authentication failed. Please log in again.'
        } else if (error.response?.status === 403) {
            errorMessage = 'You do not have permission to submit quote requests.'
        } else if (error.errors) {
            errorMessage = error.errors.map(e => e.message).join(', ')
        }
        
        await Swal.fire({
            title: t.value.errorTitle,
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#e03e2d'
        })
    } finally {
        isSubmitting.value = false
    }
}

const nextStep = () => {
    if (validateCurrentStep()) {
        if (currentStep.value < 3) {
            currentStep.value++
            window.scrollTo({ top: 0, behavior: 'smooth' })
        }
    }
}

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }
}

const validateCurrentStep = () => quoteForm.value?.reportValidity() ?? false

const resetForm = () => {
    currentStep.value = 1
    formData.value = {
        freight: freightDefaults(),
        sender: {
            firstName: '',
            lastName: '',
            company: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            state: '',
            country: '',
            postalCode: ''
        },
        receiver: {
            firstName: '',
            lastName: '',
            company: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            state: '',
            country: '',
            postalCode: ''
        },
        shipment: {
            type: 'freight',
            service: '',
            weight: '',
            length: '',
            width: '',
            height: '',
            quantity: 1,
            declaredValue: '',
            description: '',
            specialInstructions: '',
            insurance: false
        }
    }
}

// Restore form data if user returns from login
onMounted(async () => {
    loadingEdit.value = !!route.query.edit
    await checkAuth()

    await fetchCountries();

    // Check for edit mode first
    const editData = sessionStorage.getItem('editQuoteData')
    if ((route.query.edit || editData) && isAuthenticated.value) {
        loadingEdit.value = true
        try {
            const cached = editData ? JSON.parse(editData) : null
            const id = route.query.edit || cached?.id
            const response = await apiRequest(`items/quote_requests/${encodeURIComponent(id)}`, undefined, 'GET')
            const quote = response.data
            if (quote.status !== 'pending') throw new Error('Only pending quotes can be edited.')
            editQuoteId.value = quote.id
            
            // Populate form with edit data
            formData.value = {
                freight: { ...freightDefaults(), ...Object.fromEntries(Object.keys(freightDefaults()).filter(key => quote[key] != null).map(key => [key, quote[key]])) },
                sender: {
                    firstName: quote.sender_first_name ?? '',
                    lastName: quote.sender_last_name ?? '',
                    company: quote.sender_company ?? '',
                    email: quote.sender_email ?? '',
                    phone: quote.sender_phone ?? '',
                    address: quote.sender_address ?? '',
                    city: quote.sender_city ?? '',
                    state: quote.sender_state ?? '',
                    country: quote.sender_country ?? '',
                    postalCode: quote.sender_postal_code ?? quote.sender_zip ?? ''
                },
                receiver: {
                    firstName: quote.receiver_first_name ?? '',
                    lastName: quote.receiver_last_name ?? '',
                    company: quote.receiver_company ?? '',
                    email: quote.receiver_email ?? '',
                    phone: quote.receiver_phone ?? '',
                    address: quote.receiver_address ?? '',
                    city: quote.receiver_city ?? '',
                    state: quote.receiver_state ?? '',
                    country: quote.receiver_country ?? '',
                    postalCode: quote.receiver_postal_code ?? quote.receiver_zip ?? ''
                },
                shipment: {
                    type: quote.shipment_type ?? '',
                    service: quote.service_type ?? '',
                    weight: quote.weight ?? '',
                    length: quote.length ?? '',
                    width: quote.width ?? '',
                    height: quote.height ?? '',
                    quantity: quote.quantity ?? 1,
                    declaredValue: quote.declared_value ?? '',
                    description: quote.description ?? '',
                    specialInstructions: quote.special_instructions ?? '',
                    insurance: quote.insurance ?? false
                }
            }
            
            loadingEdit.value = false
            return
        } catch (error) {
            editError.value = error.message || 'Unable to load the saved quote.'
            loadingEdit.value = false
            return
        }
    }
    loadingEdit.value = false

    // Otherwise check for pending quote data
    const savedFormData = sessionStorage.getItem('pendingQuoteRequest')
    const savedStep = sessionStorage.getItem('quoteRequestStep')
    
    if (savedFormData && isAuthenticated.value) {
        try {
            const saved = JSON.parse(savedFormData)
            formData.value = { ...saved, freight: { ...freightDefaults(), ...saved.freight } }
            if (savedStep) {
                currentStep.value = Math.min(3, Math.max(1, parseInt(savedStep) || 1))
            }
            
            await Swal.fire({
                title: t.value.welcomeBack,
                text: t.value.welcomeBackText,
                icon: 'info',
                confirmButtonColor: '#e03e2d',
                timer: 3000
            })
        } catch (error) {
            console.error('Error restoring form data:', error)
        }
    }
})
</script>

<style scoped>
.freight-heading{background:var(--tanspot-black);color:#fff;padding:44px 0;border-bottom:4px solid var(--tanspot-base)}
.freight-heading p{font-size:12px;letter-spacing:2px;margin:0 0 8px;color:rgba(var(--tanspot-white-rgb), .75)}
.freight-heading h1{color:#fff;font-size:32px;margin:0}
.freight-page{max-width:1200px;padding-top:48px;padding-bottom:80px}
.freight-intro h2{font-size:26px;margin-bottom:10px;color:var(--tanspot-black)}
.freight-intro p{color:var(--tanspot-gray);max-width:760px;line-height:1.7}
.freight-progress{background:white;list-style:none;display:flex;padding:0;margin:28px 0;border:1px solid var(--tanspot-bdr-color);border-radius:var(--tanspot-bdr-radius);overflow:hidden;background:white}
.freight-progress li{display:flex;align-items:center;gap:12px;flex:1;padding:18px;font-size:14px;font-weight:600;color:var(--tanspot-gray)}
.freight-progress li.active{background:rgba(var(--tanspot-base-rgb), .08);color:var(--tanspot-black)}
.freight-progress span{display:grid;place-items:center;width:28px;height:28px;border-radius:50%;background:var(--tanspot-primary);flex-shrink:0}
.freight-progress .active span,.freight-progress .complete span{background:var(--tanspot-base);color:white}
.freight-card{min-width:0;border:1px solid var(--tanspot-bdr-color);border-radius:var(--tanspot-bdr-radius);background:white;padding:26px;margin:0 0 24px}
.freight-card legend{float:none;width:auto;padding:0 10px;font-size:19px;font-weight:700;color:var(--tanspot-black);margin:0 0 12px}
.freight-card legend span{display:inline-grid;place-items:center;background:rgba(var(--tanspot-base-rgb), .08);width:28px;height:28px;border-radius:var(--tanspot-bdr-radius);margin-right:10px;font-size:14px}
.freight-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
.freight-form label{display:flex;flex-direction:column;gap:8px;color:var(--tanspot-black);font-size:14px;font-weight:600;min-width:0}
.freight-form input,.freight-form select,.freight-form textarea{width:100%;min-width:0;border:1px solid var(--tanspot-bdr-color);border-radius:var(--tanspot-bdr-radius);background:var(--tanspot-primary);color:var(--tanspot-black);padding:13px 16px;font:inherit;font-weight:400;line-height:1.5;min-height:52px}
.freight-form textarea{resize:vertical}
.freight-form input:focus,.freight-form select:focus,.freight-form textarea:focus{outline:3px solid rgba(var(--tanspot-base-rgb), .18);border-color:var(--tanspot-base)}
.full{grid-column:1/-1}
.route-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:26px 0}
.route-panel{padding:20px;background:var(--tanspot-primary);border:1px solid var(--tanspot-bdr-color);border-radius:var(--tanspot-bdr-radius)}
.route-panel h3{font-size:17px;margin-bottom:18px;color:var(--tanspot-black)}
.route-panel h3 i{margin-right:8px;color:var(--tanspot-base)}
.dimensions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin:20px 0}
.freight-form .check{flex-direction:row;align-items:center;margin:8px 0 20px;gap:10px}
.freight-form .check input{width:18px;height:18px;min-height:0;accent-color:var(--tanspot-base)}
.field-hint{color:var(--tanspot-gray);font-size:13px;margin:0 0 20px}
.value-field{max-width:320px;margin-top:20px}
.freight-actions{display:flex;align-items:center;gap:20px;border-top:1px solid var(--tanspot-bdr-color);padding-top:24px}
.freight-actions button{border-radius:var(--tanspot-bdr-radius);padding:12px 24px;font-weight:600;border:1px solid var(--tanspot-bdr-color)}
.freight-actions .next-button{background:var(--tanspot-base);color:var(--tanspot-white);margin-left:auto;border:0;border-radius:30px;padding:5px 8px 5px 26px;gap:16px;font-size:15px;min-height:54px}
.freight-actions .next-button:hover{background:var(--tanspot-black)}
.freight-actions .back-button{background:#fff;color:var(--tanspot-black);border-radius:30px;min-height:50px}
.back-button:hover{border-color:var(--tanspot-base);color:var(--tanspot-base)}
.freight-actions button:focus-visible{outline:3px solid rgba(var(--tanspot-base-rgb), .5);outline-offset:4px}
.freight-actions button:disabled{opacity:.55;cursor:not-allowed}
.required-note{color:var(--tanspot-gray);font-size:12px}
.account-notice{padding:18px;border-radius:var(--tanspot-bdr-radius);background:var(--tanspot-primary);color:var(--tanspot-black);margin-bottom:24px}
.freight-help{border-top:1px solid var(--tanspot-bdr-color);margin-top:48px;padding-top:30px}
.freight-help h2{font-size:21px;text-align:center}
.freight-help>div{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:22px}
.freight-help p{font-size:14px;color:var(--tanspot-gray);text-align:center}
.freight-help b{display:block;color:var(--tanspot-base);font-size:22px;margin-bottom:8px}
.form-error{color:#b42318;padding:12px 0}
.form-error button{border:0;background:none;text-decoration:underline;color:inherit}
@media(max-width:767px){.freight-heading{padding:24px 0}
.freight-heading h1{font-size:26px}
.freight-page{padding-top:24px}
.freight-intro h2{font-size:22px}
.freight-progress li{flex-direction:column;padding:12px 6px;gap:6px;text-align:center;font-size:12px}
.freight-card{padding:18px 14px}
.freight-card legend{font-size:16px}
.freight-grid,.route-grid{grid-template-columns:1fr}
.route-panel{padding:16px}
.dimensions{gap:10px}
.freight-actions{gap:10px;flex-wrap:wrap}
.freight-actions button{padding:12px 16px}
.required-note{display:none}
.freight-help>div{grid-template-columns:1fr;gap:8px}}
</style>
