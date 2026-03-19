<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { ChevronDown, ArrowRight } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'
import { getFaqs } from '@/api/public'
import type { FAQ } from '@/lib/catalog-types'

const selectedCategory = ref<string>('all')
const faqs = ref<FAQ[]>([])

const categoryOptions = [
  { id: 'all', name: 'Все вопросы' },
  { id: 'order', name: 'Заказ и оплата' },
  { id: 'shipping', name: 'Доставка' },
  { id: 'care', name: 'Уход за изделиями' },
  { id: 'materials', name: 'Материалы' },
]

const filteredFAQs = computed(() => {
  if (selectedCategory.value === 'all') return faqs.value
  return faqs.value.filter(faq => faq.category === selectedCategory.value)
})

onMounted(async () => {
  try {
    faqs.value = await getFaqs()
  } catch (e) {
    console.error(e)
    faqs.value = []
  }
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
  <div>
    <!-- Page Header -->
    <section class="py-14 md:py-20 gradient-bg">
      <div class="container-custom text-center">
        <h1 class="text-4xl md:text-5xl font-display font-bold text-gradient mb-3">
          Часто задаваемые вопросы
        </h1>
        <p class="text-text-secondary max-w-lg mx-auto">
          Здесь вы найдёте ответы на самые популярные вопросы
        </p>
      </div>
    </section>

    <!-- Filter -->
    <section class="py-6 bg-white border-b border-surface-200">
      <div class="container-custom">
        <div class="flex flex-wrap justify-center gap-2">
          <button
            v-for="cat in categoryOptions"
            :key="cat.id"
            @click="selectedCategory = cat.id"
            class="px-5 py-2 rounded-full text-sm font-medium transition-all duration-300"
            :class="selectedCategory === cat.id
              ? 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-glow'
              : 'bg-surface-100 text-text-secondary hover:bg-primary-50 hover:text-primary-600'"
          >
            {{ cat.name }}
          </button>
        </div>
      </div>
    </section>

    <!-- FAQ List -->
    <section class="section-padding bg-white">
      <div class="container-custom">
        <div class="max-w-3xl mx-auto space-y-3">
          <div
            v-for="faq in filteredFAQs"
            :key="faq.id"
            class="card-modern overflow-hidden"
          >
            <button
              @click="toggleFAQ(faq.id)"
              class="w-full text-left px-6 py-5 flex items-center justify-between hover:bg-surface-50 transition-colors"
            >
              <h3 class="font-display text-base font-semibold text-text-primary pr-4">
                {{ faq.question }}
              </h3>
              <ChevronDown
                class="w-5 h-5 text-primary-500 transition-transform duration-300 flex-shrink-0"
                :class="openFAQs.has(faq.id) ? 'rotate-180' : ''"
              />
            </button>
            <Transition
              enter-active-class="transition-all duration-300 ease-out"
              enter-from-class="max-h-0 opacity-0"
              enter-to-class="max-h-96 opacity-100"
              leave-active-class="transition-all duration-200 ease-in"
              leave-from-class="max-h-96 opacity-100"
              leave-to-class="max-h-0 opacity-0"
            >
              <div
                v-if="openFAQs.has(faq.id)"
                class="overflow-hidden"
              >
                <div class="px-6 pb-5 border-t border-surface-100 pt-4">
                  <p class="text-text-secondary text-sm leading-relaxed">
                    {{ faq.answer }}
                  </p>
                </div>
              </div>
            </Transition>
          </div>
        </div>

        <div v-if="filteredFAQs.length === 0" class="text-center py-16">
          <p class="text-text-secondary text-lg">В этой категории пока нет вопросов</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="section-padding bg-gradient-to-r from-primary-600 via-primary-500 to-accent-500 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.08),transparent_60%)]"></div>
      <div class="container-custom text-center relative z-10">
        <h2 class="text-3xl md:text-4xl font-display font-bold mb-4 text-white">
          Остались вопросы?
        </h2>
        <p class="text-lg mb-8 max-w-xl mx-auto text-white/85">
          Свяжитесь со мной, и я с радостью расскажу больше о мире Bead Wonder
        </p>
        <RouterLink to="/contact" custom v-slot="{ navigate }">
          <AppButton size="lg" variant="secondary" @click="navigate">
            Задать вопрос
            <ArrowRight class="ml-2 w-5 h-5" />
          </AppButton>
        </RouterLink>
      </div>
    </section>
  </div>
</template>
