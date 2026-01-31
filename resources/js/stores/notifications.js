import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref([])
  
  let nextId = 1
  
  const add = (notification) => {
    const id = `notification-${nextId++}`
    const newNotification = {
      id,
      type: 'info',
      duration: 5000,
      createdAt: Date.now(),
      ...notification
    }
    
    notifications.value.push(newNotification)
    
    // Auto-remove after duration
    setTimeout(() => {
      remove(id)
    }, newNotification.duration)
    
    return id
  }
  
  const remove = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }
  
  const clear = () => {
    notifications.value = []
  }
  
  // Métodos de conveniência para diferentes tipos
  const success = (title, message = '', duration = 5000) => {
    return add({ type: 'success', title, message, duration })
  }
  
  const error = (title, message = '', duration = 7000) => {
    return add({ type: 'error', title, message, duration })
  }
  
  const warning = (title, message = '', duration = 6000) => {
    return add({ type: 'warning', title, message, duration })
  }
  
  const info = (title, message = '', duration = 5000) => {
    return add({ type: 'info', title, message, duration })
  }
  
  return {
    notifications,
    add,
    remove,
    clear,
    success,
    error,
    warning,
    info
  }
})
