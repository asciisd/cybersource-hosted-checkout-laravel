<template>
  <form :action="endpoint" method="POST">
    <input
      v-for="(value, name) in signedFields"
      :key="name"
      type="hidden"
      :name="name"
      :value="value"
    />
    <slot>
      <button type="submit">Pay with Cybersource</button>
    </slot>
  </form>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  endpoint: {
    type: String,
    required: true,
  },
  signedFields: {
    type: Object,
    required: true,
  },
});
</script> 