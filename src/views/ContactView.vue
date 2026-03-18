<script setup lang="ts">
import { ref } from 'vue'
import { Phone, Mail, MapPin, Send } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
import AppCardHeader from '@/components/ui/AppCardHeader.vue'
import AppCardTitle from '@/components/ui/AppCardTitle.vue'
import { env } from '@/lib/env'

const formData = ref({
  name: '',
  email: '',
  phone: '',
  message: '',
})

const isSubmitting = ref(false)

const handleSubmit = async () => {
  isSubmitting.value = true
  // TODO: подключить отправку формы (backend/API)

  // Имитация отправки
  await new Promise(resolve => setTimeout(resolve, 1000))
  
  alert('Спасибо за сообщение! Я свяжусь с вами в ближайшее время.')
  formData.value = {
    name: '',
    email: '',
    phone: '',
    message: '',
  }
  isSubmitting.value = false
}
</script>

<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <section class="section-padding gradient-bg">
      <div class="container-custom">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-gradient mb-4">
            Свяжитесь со мной
          </h1>
          <p class="text-lg text-text-secondary max-w-2xl mx-auto">
            Я всегда рада ответить на ваши вопросы и помочь с выбором уникального украшения
          </p>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Contact Info -->
          <div class="space-y-6">
            <AppCard class="card-modern">
              <AppCardHeader>
                <AppCardTitle>Контактная информация</AppCardTitle>
              </AppCardHeader>
              <AppCardContent>
                <div class="space-y-4">
                  <a
                    :href="`tel:${env.contactPhone}`"
                    class="flex items-center space-x-4 text-text-secondary hover:text-primary-500 transition-colors group"
                  >
                    <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                      <Phone class="w-5 h-5 text-primary-500" />
                    </div>
                    <div>
                      <p class="text-sm text-text-secondary font-medium">Телефон</p>
                      <p class="font-bold text-text-primary">{{ env.contactPhone }}</p>
                    </div>
                  </a>
                  <a
                    :href="`mailto:${env.contactEmail}`"
                    class="flex items-center space-x-4 text-text-secondary hover:text-primary-500 transition-colors group"
                  >
                    <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                      <Mail class="w-5 h-5 text-primary-500" />
                    </div>
                    <div>
                      <p class="text-sm text-text-secondary font-medium">Email</p>
                      <p class="font-bold text-text-primary">{{ env.contactEmail }}</p>
                    </div>
                  </a>
                  <div class="flex items-center space-x-4 text-text-secondary">
                    <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center">
                      <MapPin class="w-5 h-5 text-primary-500" />
                    </div>
                    <div>
                      <p class="text-sm text-text-secondary font-medium">Адрес</p>
                      <p class="font-bold text-text-primary">Россия, доставка по всей стране</p>
                    </div>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>

            <AppCard class="card-modern">
              <AppCardHeader>
                <AppCardTitle>Режим работы</AppCardTitle>
              </AppCardHeader>
              <AppCardContent>
                <div class="space-y-3">
                  <div class="flex justify-between py-2 border-b border-surface-100">
                    <span class="text-text-secondary font-medium">Понедельник - Пятница</span>
                    <span class="font-bold text-text-primary">9:00 - 18:00</span>
                  </div>
                  <div class="flex justify-between py-2 border-b border-surface-100">
                    <span class="text-text-secondary font-medium">Суббота</span>
                    <span class="font-bold text-text-primary">10:00 - 16:00</span>
                  </div>
                  <div class="flex justify-between py-2">
                    <span class="text-text-secondary font-medium">Воскресенье</span>
                    <span class="font-bold text-text-primary">Выходной</span>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>
          </div>

          <!-- Contact Form -->
          <AppCard class="card-modern">
            <AppCardHeader>
              <AppCardTitle>Напишите мне</AppCardTitle>
            </AppCardHeader>
            <AppCardContent>
              <form @submit.prevent="handleSubmit" class="space-y-5">
                <div>
                  <label for="name" class="block text-sm font-bold text-text-primary mb-2">
                    Ваше имя *
                  </label>
                  <input
                    id="name"
                    v-model="formData.name"
                    type="text"
                    required
                    class="form-input w-full"
                    placeholder="Введите ваше имя"
                  />
                </div>
                <div>
                  <label for="email" class="block text-sm font-bold text-text-primary mb-2">
                    Email *
                  </label>
                  <input
                    id="email"
                    v-model="formData.email"
                    type="email"
                    required
                    class="form-input w-full"
                    placeholder="example@mail.com"
                  />
                </div>
                <div>
                  <label for="phone" class="block text-sm font-bold text-text-primary mb-2">
                    Телефон
                  </label>
                  <input
                    id="phone"
                    v-model="formData.phone"
                    type="tel"
                    class="form-input w-full"
                    placeholder="+7 (999) 123-45-67"
                  />
                </div>
                <div>
                  <label for="message" class="block text-sm font-bold text-text-primary mb-2">
                    Сообщение *
                  </label>
                  <textarea
                    id="message"
                    v-model="formData.message"
                    required
                    rows="5"
                    class="form-input w-full resize-none"
                    placeholder="Опишите ваш вопрос или заказ"
                  ></textarea>
                </div>
                <AppButton 
                  type="submit" 
                  size="lg" 
                  variant="modern"
                  class="w-full"
                  :disabled="isSubmitting"
                >
                  <Send v-if="!isSubmitting" class="mr-2 w-5 h-5" />
                  {{ isSubmitting ? 'Отправка...' : 'Отправить сообщение' }}
                </AppButton>
              </form>
            </AppCardContent>
          </AppCard>
        </div>
      </div>
    </section>
  </div>
</template>
