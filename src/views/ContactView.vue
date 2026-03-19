<script setup>
import { ref } from 'vue'
import { Phone, Mail, MapPin, Send } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppCardHeader from '@/components/ui/AppCardHeader.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
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
  await new Promise(resolve => setTimeout(resolve, 1000))

  alert('Спасибо за сообщение! Я свяжусь с вами в ближайшее время.')
  formData.value = { name: '', email: '', phone: '', message: '' }
  isSubmitting.value = false
}
</script>

<template>
  <div>
    <!-- Page Header -->
    <section class="py-14 md:py-20 gradient-bg">
      <div class="container-custom text-center">
        <h1 class="text-4xl md:text-5xl font-display font-bold text-gradient mb-3">
          Свяжитесь со мной
        </h1>
        <p class="text-text-secondary max-w-lg mx-auto">
          Расскажите о вашей идее — и мы создадим ваше бисерное чудо вместе
        </p>
      </div>
    </section>

    <!-- Contact -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
          <!-- Info -->
          <div class="space-y-6">
            <AppCard class="shadow-card">
              <AppCardHeader>
                <AppCardTitle>Контактная информация</AppCardTitle>
              </AppCardHeader>
              <AppCardContent>
                <div class="space-y-4">
                  <a
                    :href="`tel:${env.contactPhone}`"
                    class="flex items-center gap-4 text-text-secondary hover:text-primary-600 transition-colors group"
                  >
                    <div class="w-11 h-11 bg-primary-50 rounded-xl flex items-center justify-center group-hover:bg-primary-100 transition-colors flex-shrink-0">
                      <Phone class="w-5 h-5 text-primary-500" />
                    </div>
                    <div>
                      <p class="text-xs text-text-muted font-medium">Телефон</p>
                      <p class="font-semibold text-text-primary text-sm">{{ env.contactPhone }}</p>
                    </div>
                  </a>
                  <a
                    :href="`mailto:${env.contactEmail}`"
                    class="flex items-center gap-4 text-text-secondary hover:text-primary-600 transition-colors group"
                  >
                    <div class="w-11 h-11 bg-primary-50 rounded-xl flex items-center justify-center group-hover:bg-primary-100 transition-colors flex-shrink-0">
                      <Mail class="w-5 h-5 text-primary-500" />
                    </div>
                    <div>
                      <p class="text-xs text-text-muted font-medium">Email</p>
                      <p class="font-semibold text-text-primary text-sm">{{ env.contactEmail }}</p>
                    </div>
                  </a>
                  <div class="flex items-center gap-4">
                    <div class="w-11 h-11 bg-primary-50 rounded-xl flex items-center justify-center flex-shrink-0">
                      <MapPin class="w-5 h-5 text-primary-500" />
                    </div>
                    <div>
                      <p class="text-xs text-text-muted font-medium">Адрес</p>
                      <p class="font-semibold text-text-primary text-sm">Россия, доставка по всей стране</p>
                    </div>
                  </div>
                </div>
              </AppCardContent>
            </AppCard>
          </div>

          <!-- Form -->
          <AppCard class="shadow-card">
            <AppCardHeader>
              <AppCardTitle>Напишите мне</AppCardTitle>
            </AppCardHeader>
            <AppCardContent>
              <form @submit.prevent="handleSubmit" class="space-y-5">
                <div>
                  <label for="name" class="block text-sm font-semibold text-text-primary mb-1.5">
                    Ваше имя *
                  </label>
                  <input
                    id="name"
                    v-model="formData.name"
                    type="text"
                    required
                    class="form-input"
                    placeholder="Введите ваше имя"
                  />
                </div>
                <div>
                  <label for="email" class="block text-sm font-semibold text-text-primary mb-1.5">
                    Email *
                  </label>
                  <input
                    id="email"
                    v-model="formData.email"
                    type="email"
                    required
                    class="form-input"
                    placeholder="example@mail.com"
                  />
                </div>
                <div>
                  <label for="phone" class="block text-sm font-semibold text-text-primary mb-1.5">
                    Телефон
                  </label>
                  <input
                    id="phone"
                    v-model="formData.phone"
                    type="tel"
                    class="form-input"
                    placeholder="+7 (999) 123-45-67"
                  />
                </div>
                <div>
                  <label for="message" class="block text-sm font-semibold text-text-primary mb-1.5">
                    Сообщение *
                  </label>
                  <textarea
                    id="message"
                    v-model="formData.message"
                    required
                    rows="5"
                    class="form-input resize-none"
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
                  <Send v-if="!isSubmitting" class="mr-2 w-4 h-4" />
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
