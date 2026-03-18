<script setup lang="ts">
import { ref, computed } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import AppCard, { AppCardContent, AppCardHeader, AppCardTitle } from '@/components/ui/AppCard.vue'
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
  if (openFAQs.value.has(id)) {
    openFAQs.value.delete(id)
  } else {
    openFAQs.value.add(id)
  }
}
</script>

<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <section class="section-padding bg-gradient-to-br from-rose-light via-white to-lavender/20">
      <div class="container-custom">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-playfair font-bold text-gradient mb-4">
            Часто задаваемые вопросы
          </h1>
          <p class="text-lg text-text-medium max-w-2xl mx-auto">
            Здесь вы найдете ответы на самые популярные вопросы о моих изделиях
          </p>
        </div>
      </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 bg-white border-b border-lavender/20">
      <div class="container-custom">
        <div class="flex flex-wrap justify-center gap-4">
          <button
            v-for="category in categories"
            :key="category.id"
            @click="selectedCategory = category.id"
            class="px-6 py-2 rounded-full transition-all duration-300"
            :class="selectedCategory === category.id ? 'bg-dusty-rose text-white shadow-soft' : 'bg-rose-light text-text-medium hover:bg-dusty-rose hover:text-white'"
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
            class="card-romantic overflow-hidden"
          >
            <button
              @click="toggleFAQ(faq.id)"
              class="w-full text-left p-6 flex items-center justify-between hover:bg-rose-light/50 transition-colors"
            >
              <h3 class="font-playfair text-lg text-text-rose pr-4">
                {{ faq.question }}
              </h3>
              <ChevronDown
                class="w-5 h-5 text-text-rose transition-transform duration-300 flex-shrink-0"
                :class="openFAQs.has(faq.id) ? 'rotate-180' : ''"
              />
            </button>
            <div
              v-if="openFAQs.has(faq.id)"
              class="px-6 pb-6 border-t border-lavender/20 pt-4"
            >
              <p class="text-text-medium leading-relaxed">
                {{ faq.answer }}
              </p>
            </div>
          </AppCard>
        </div>

        <div v-if="filteredFAQs.length === 0" class="text-center py-12">
          <p class="text-text-medium text-lg">В этой категории пока нет вопросов</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-gradient-to-r from-dusty-rose to-pastel-purple text-white">
      <div class="container-custom text-center">
        <h2 class="text-3xl md:text-4xl font-playfair font-bold mb-6">
          Не нашли ответ на свой вопрос?
        </h2>
        <p class="text-lg mb-8 max-w-2xl mx-auto">
          Свяжитесь со мной, и я с радостью отвечу на все ваши вопросы
        </p>
        <RouterLink to="/contact" custom v-slot="{ navigate }">
          <AppButton 
            size="lg" 
            variant="secondary"
            class="bg-white text-dusty-rose hover:bg-ghost-white"
            @click="navigate"
          >
            Задать вопрос
          </AppButton>
        </RouterLink>
      </div>
    </section>
  </div>
</template>
