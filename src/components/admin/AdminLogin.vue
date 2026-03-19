<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/useAdminStore'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const adminStore = useAdminStore()

const username = ref('')
const password = ref('')
const errorMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  const success = await adminStore.login(username.value, password.value)
  if (success) {
    router.push('/admin/dashboard')
  } else {
    errorMessage.value = adminStore.error || 'Ошибка входа'
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-4" style="background-color: #FFD9D9">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold mb-2" style="color: #A24C61">Админ-панель</h1>
        <p class="text-sm" style="color: #611820">Введите учетные данные для входа</p>
      </div>

      <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft-lg p-8 border border-white/20">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium mb-2" style="color: #A24C61">Имя пользователя</label>
            <input
              v-model="username"
              type="text"
              required
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #BD7880"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #A24C61">Пароль</label>
            <input
              v-model="password"
              type="password"
              required
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #BD7880"
            />
          </div>

          <div v-if="errorMessage" class="text-red-500 text-sm text-center">
            {{ errorMessage }}
          </div>

          <AppButton type="submit" class="w-full" :disabled="adminStore.isLoading">
            {{ adminStore.isLoading ? 'Вход...' : 'Войти' }}
          </AppButton>
        </form>
      </div>

      <p class="text-center text-xs mt-6" style="color: #611820">
        По умолчанию: admin / admin123
      </p>
    </div>
  </div>
</template>
