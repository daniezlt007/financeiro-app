<template>
  <div :class="cardClasses">
    <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200">
      <slot name="header">
        <h3 v-if="title" :class="titleClasses">{{ title }}</h3>
      </slot>
    </div>
    <div :class="bodyClasses">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'default', // default, elevated, outlined
    validator: (value) => ['default', 'elevated', 'outlined'].includes(value)
  },
  padding: {
    type: String,
    default: 'default', // none, sm, default, lg
    validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value)
  }
})

const cardClasses = computed(() => {
  const baseClasses = 'bg-white rounded-lg border'
  
  const variantClasses = {
    default: 'border-gray-200 shadow-sm',
    elevated: 'border-gray-200 shadow-lg',
    outlined: 'border-gray-300 shadow-none'
  }
  
  return `${baseClasses} ${variantClasses[props.variant]}`
})

const titleClasses = computed(() => {
  return 'text-lg font-semibold text-gray-900'
})

const bodyClasses = computed(() => {
  const paddingClasses = {
    none: '',
    sm: 'px-4 py-3',
    default: 'px-6 py-4',
    lg: 'px-8 py-6'
  }
  
  return paddingClasses[props.padding]
})
</script>
