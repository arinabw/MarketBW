<script setup>
import { ref, onMounted } from 'vue'
import { useAdminStore } from '@/stores/useAdminStore'
import { Plus, Trash2, Edit2, X, Upload } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'

const adminStore = useAdminStore()
const isModalOpen = ref(false)
const editingCategory = ref(null)
const imageInputRef = ref(null)
const formData = ref({
  name: '',
  description: '',
  image: ''
})

onMounted(async () => {
  await adminStore.loadCategories()
})

const openModal = (category) => {
  if (category) {
    editingCategory.value = category
    formData.value = {
      name: category.name,
      description: category.description,
      image: category.image
    }
  } else {
    editingCategory.value = null
    formData.value = {
      name: '',
      description: '',
      image: ''
    }
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingCategory.value = null
  formData.value = {
    name: '',
    description: '',
    image: ''
  }
}

const handleSubmit = async () => {
  if (!formData.value.name || !formData.value.image) {
    alert('Заполните обязательные поля')
    return
  }

  if (editingCategory.value) {
    await adminStore.updateCategory(editingCategory.value.id, formData.value)
  } else {
    const newCategory = {
      id: Date.now().toString(),
      ...formData.value
    }
    await adminStore.createCategory(newCategory)
  }

  closeModal()
}

const handleDelete = async (id) => {
  if (confirm('Удалить эту категорию?')) {
    await adminStore.deleteCategory(id)
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      formData.value.image = e.target.result
    }
    reader.readAsDataURL(file)
  }
  // Сброс input для возможности загрузки того же файла
  event.target.value = ''
}
</script>

<template>
  <div class="min-h-screen" style="background-color: #FFD9D9">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-xl shadow-soft border border-white/20">
      <div class="container-custom">
        <div class="flex items-center justify-between py-4">
          <div class="flex items-center gap-4">
            <button @click="$router.back()" class="flex items-center gap-2 text-sm" style="color: #A24C61">
              <X class="w-4 h-4" />
              Назад
            </button>
            <h1 class="text-2xl font-bold" style="color: #A24C61">Категории</h1>
          </div>
          <button @click="openModal()" class="flex items-center gap-2 text-sm" style="color: #A24C61">
            <Plus class="w-4 h-4" />
            Добавить категорию
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="container-custom py-8">
      <div v-if="adminStore.isLoading" class="text-center py-12" style="color: #A24C61">
        Загрузка...
      </div>

      <div v-else-if="adminStore.categories.length === 0" class="text-center py-12" style="color: #A24C61">
        Категории не найдены
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="category in adminStore.categories"
          :key="category.id"
          class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft p-6 border border-white/20"
        >
          <div class="aspect-square rounded-xl overflow-hidden mb-4">
            <img :src="category.image" :alt="category.name" class="w-full h-full object-cover" />
          </div>
          <h3 class="font-bold text-lg mb-2" style="color: #A24C61">{{ category.name }}</h3>
          <p class="text-sm mb-4" style="color: #611820">{{ category.description }}</p>
          <div class="flex gap-2">
            <button
              @click="openModal(category)"
              class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm"
              style="background-color: #FFD9D9; color: #A24C61"
            >
              <Edit2 class="w-4 h-4" />
              Редактировать
            </button>
            <button
              @click="handleDelete(category.id)"
              class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm"
              style="background-color: #BD7880; color: white"
            >
              <Trash2 class="w-4 h-4" />
              Удалить
            </button>
          </div>
        </div>
        <!-- Плейсхолдеры для симметрии -->
        <div
          v-for="i in (3 - (adminStore.categories.length % 3)) % 3"
          :key="`placeholder-${i}`"
          class="hidden lg:block"
          aria-hidden="true"
        ></div>
      </div>
    </main>

    <!-- Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-soft-lg p-6 w-full max-w-md border border-white/20">
        <h2 class="text-xl font-bold mb-6" style="color: #A24C61">
          {{ editingCategory ? 'Редактирование категории' : 'Добавление категории' }}
        </h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2" style="color: #A24C61">Название *</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #BD7880"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #A24C61">Описание</label>
            <textarea
              v-model="formData.description"
              rows="3"
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #BD7880"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #A24C61">Изображение *</label>
            <div class="flex gap-2 mb-2">
              <input
                v-model="formData.image"
                type="text"
                required
                placeholder="URL изображения"
                class="flex-1 px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
                style="border-color: #BD7880"
              />
              <button
                type="button"
                @click="imageInputRef?.click()"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm"
                style="background-color: #FFD9D9; color: #A24C61"
              >
                <Upload class="w-4 h-4" />
                Загрузить
              </button>
              <input
                ref="imageInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleImageUpload"
              />
            </div>
            <div v-if="formData.image" class="mt-2">
              <img :src="formData.image" class="w-32 h-32 object-cover rounded-lg" />
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 py-3 rounded-lg text-sm"
              style="background-color: #BD7880; color: white"
            >
              Отмена
            </button>
            <button
              type="submit"
              class="flex-1 py-3 rounded-lg text-sm"
              style="background-color: #FFD9D9; color: #A24C61"
            >
              {{ editingCategory ? 'Сохранить' : 'Добавить' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
