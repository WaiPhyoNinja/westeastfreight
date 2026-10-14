<template>
    <!--Chat Popup-->
  <div id="chat-popup" class="chat-popup">
    <div class="popup-inner">
      <div class="close-chat"><i class="fa fa-times"></i></div>
      <div class="chat-form">
        <p>Please fill out the form below and we will get back to you as soon as possible.</p>
        <form @submit.prevent="submit"
          class="chat-contact-form">
          <input type="hidden" name="_token" value="">

          <div class="form-group">
            <input type="text" name="name" placeholder="Your Name"  required>
          </div>
          <div class="form-group">
            <input type="email" name="email" placeholder="Your Email"  required>
          </div>
          <div class="form-group">
            <textarea name="message" placeholder="Your Text" required></textarea>
          </div>
          <div class="form-group message-btn">
            <button type="submit" class="thm-btn"> Submit Now
              <span><i class="icon-right-arrow"></i></span>
            </button>
          </div>
          <div class="result"></div>
        </form>
      </div>
    </div>
  </div>
</template>
<script setup>
import { apiRequest } from '@/services/http';
import Swal from 'sweetalert2';
const submit = async (event) => {
  const form = event.target;
  try {
    await apiRequest('items/contact_us', Object.fromEntries(new FormData(form)));
    form.reset();
    await Swal.fire({ icon: 'success', text: 'Your message has been received.' });
  } catch (error) { await Swal.fire({ icon: 'error', text: error.message }); }
};
</script>
