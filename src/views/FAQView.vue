<script setup lang="ts">
import { ref, computed } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import AppCard from '@/components/ui/AppCard.vue'
import AppCardContent from '@/components/ui/AppCardContent.vue'
import AppCardHeader from '@/components/ui/AppCardHeader.vue'
import AppCardTitle from '@/components/ui/AppCardTitle.vue'
import { faqs } from '@/lib/data'

const selectedCategory = ref<string>('all')

const categories = [
  { id: 'all', name: 'Все вопросы' },
  { id: 'order', name: 'Заказ и оплата' },
  { id: 'shipping', name: 'Доставка' },
  { id: 'care', name: 'Уход за изделиями' },
  { id: 'materials', name: 'Материалы' },
]

const filteredFAQs = computed(() => {
  if (selectedCategory.value === 'all') return faqs
  return faqs.filter(faq => faq.category === selectedCategory.value)
})

const openFAQs = ref<Set<string>>(new Set())

const toggleFAQ = (id: string) => {
  const next = new Set(openFAQs.value)
  if (next.has(id)) next.delete(id)
  else next.add(id)
  openFAQs.value = next
}
</script>

<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <section class="section-padding gradient-bg">
      <div class="container-custom">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-gradient mb-4">
            Часто задаваемые вопросы
          </h1>
          <p class="text-lg text-text-secondary max-w-2xl mx-auto">
            Здесь вы найдете ответы на самые популярные вопросы о моих изделиях
          </p>
        </div>
      </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 bg-white border-b border-surface-200">
      <div class="container-custom">
        <div class="flex flex-wrap justify-center gap-3">
          <button
            v-for="category in categories"
            :key="category.id"
            @click="selectedCategory = category.id"
            class="px-6 py-2.5 rounded-full transition-all duration-300 font-medium"
            :class="selectedCategory === category.id ? 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-glow' : 'bg-surface-100 text-text-secondary hover:bg-primary-50 hover:text-primary-500'"
          >
            {{ category.name }}
          </button>
        </div>
      </div>
    </section>

    <!-- FAQ List -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="max-w-3xl mx-auto space-y-4">
          <AppCard
            v-for="faq in filteredFAQs"
            :key="faq.id"
            class="card-modern overflow-hidden"
          >
            <button
              @click="toggleFAQ(faq.id)"
              class="w-full text-left p-6 flex items-center justify-between hover:bg-surface-50 transition-colors"
            >
              <h3 class="font-display text-lg font-bold text-text-primary pr-4">
                {{ faq.question }}
              </h3>
              <ChevronDown
                class="w-5 h-5 text-primary-500 transition-transform duration-300 flex-shrink-0"
                :class="openFAQs.has(faq.id) ? 'rotate-180' : ''"
              />
            </button>
            <div
              v-if="openFAQs.has(faq.id)"
              class="px-6 pb-6 border-t border-surface-100 pt-4"
            >
              <p class="text-text-secondary leading-relaxed">
                {{ faq.answer }}
              </p>
            </div>
          </AppCard>
        </div>

        <div v-if="filteredFAQs.length === 0" class="text-center py-12">
          <p class="text-text-secondary text-lg">В этой категории пока нет вопросов</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-gradient-to-r from-primary-500 to-accent-500 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-accent-600 opacity-50"></div>
      <div class="container-custom text-center relative z-10">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-display font-bold mb-6">
          Не нашли ответ на свой вопрос?
        </h2>
        <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto opacity-90">
          Свяжитесь со мной, и я с радостью отвечу на все ваши вопросы
        </p>
        <RouterLink to="/contact" custom v-slot="{ navigate }">
          <AppButton 
            size="lg" 
            variant="secondary"
            class="bg-white text-primary-500 hover:bg-surface-50 shadow-glow hover:shadow-glow-lg"
            @click="navigate"
          >
            Задать вопрос
          </AppButton>
        </RouterLink>
      </div>
    </section>
  </div>
</template>
