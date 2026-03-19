<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAdminStore } from '@/stores/useAdminStore'
import { Plus, Trash2, Edit2, X, Image as ImageIcon } from 'lucide-vue-next'
import AppButton from '@/components/ui/AppButton.vue'

const adminStore = useAdminStore()
const isModalOpen = ref(false)
const editingProduct = ref<any>(null)
const formData = ref({
  name: '',
  description: '',
  price: 0,
  category: '',
  images: [] as string[],
  materials: [] as string[],
  size: '',
  technique: '',
  inStock: true,
  featured: false
})

onMounted(async () => {
  await adminStore.loadProducts()
})

const openModal = (product?: any) => {
  if (product) {
    editingProduct.value = product
    formData.value = {
      name: product.name,
      description: product.description,
      price: product.price,
      category: product.category,
      images: product.images,
      materials: product.materials,
      size: product.size || '',
      technique: product.technique,
      inStock: product.in_stock,
      featured: product.featured
    }
  } else {
    editingProduct.value = null
    formData.value = {
      name: '',
      description: '',
      price: 0,
      category: '',
      images: [],
      materials: [],
      size: '',
      technique: '',
      inStock: true,
      featured: false
    }
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingProduct.value = null
  formData.value = {
    name: '',
    description: '',
    price: 0,
    category: '',
    images: [],
    materials: [],
    size: '',
    technique: '',
    inStock: true,
    featured: false
  }
}

const handleSubmit = async () => {
  if (!formData.value.name || !formData.value.price || !formData.value.category) {
    alert('Заполните обязательные поля')
    return
  }

  const newProduct = {
    id: editingProduct.value ? editingProduct.value.id : Date.now().toString(),
    ...formData.value
  }

  if (editingProduct.value) {
    await adminStore.updateProduct(editingProduct.value.id, newProduct)
  } else {
    await adminStore.createProduct(newProduct)
  }

  closeModal()
}

const handleDelete = async (id: string) => {
  if (confirm('Удалить этот товар?')) {
    await adminStore.deleteProduct(id)
  }
}

const addImage = () => {
  const url = prompt('Введите URL изображения:')
  if (url && !formData.value.images.includes(url)) {
    formData.value.images.push(url)
  }
}

const removeImage = (index: number) => {
  formData.value.images.splice(index, 1)
}

const addMaterial = () => {
  const material = prompt('Введите материал:')
  if (material && !formData.value.materials.includes(material)) {
    formData.value.materials.push(material)
  }
}

const removeMaterial = (index: number) => {
  formData.value.materials.splice(index, 1)
}
</script>

<template>
  <div class="min-h-screen" style="background-color: #F5CEC7">
    <!-- Header -->
    <header class="bg-white shadow-soft">
      <div class="container-custom">
        <div class="flex items-center justify-between py-4">
          <div class="flex items-center gap-4">
            <button @click="$router.back()" class="flex items-center gap-2 text-sm" style="color: #E79796">
              <X class="w-4 h-4" />
              Назад
            </button>
            <h1 class="text-2xl font-bold" style="color: #E79796">Товары</h1>
          </div>
          <button @click="openModal()" class="flex items-center gap-2 text-sm" style="color: #E79796">
            <Plus class="w-4 h-4" />
            Добавить товар
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="container-custom py-8">
      <div v-if="adminStore.isLoading" class="text-center py-12" style="color: #E79796">
        Загрузка...
      </div>

      <div v-else-if="adminStore.products.length === 0" class="text-center py-12" style="color: #E79796">
        Товары не найдены
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="product in adminStore.products"
          :key="product.id"
          class="bg-white rounded-2xl shadow-soft p-6"
        >
          <div class="aspect-square rounded-xl overflow-hidden mb-4">
            <img
              v-if="product.images.length > 0"
              :src="product.images[0]"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center" style="background-color: #F5CEC7">
              <ImageIcon class="w-12 h-12" style="color: #E79796" />
            </div>
          </div>
          <h3 class="font-bold text-lg mb-1" style="color: #E79796">{{ product.name }}</h3>
          <p class="text-sm mb-2" style="color: #E79796">{{ product.price }} ₽</p>
          <p class="text-sm mb-4" style="color: #E79796">{{ product.category }}</p>
          <div class="flex gap-2">
            <button
              @click="openModal(product)"
              class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm"
              style="background-color: #F5CEC7; color: #E79796"
            >
              <Edit2 class="w-4 h-4" />
              Редактировать
            </button>
            <button
              @click="handleDelete(product.id)"
              class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm"
              style="background-color: #E79796; color: white"
            >
              <Trash2 class="w-4 h-4" />
              Удалить
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 overflow-y-auto">
      <div class="bg-white rounded-2xl shadow-soft-lg p-6 w-full max-w-2xl my-8">
        <h2 class="text-xl font-bold mb-6" style="color: #E79796">
          {{ editingProduct ? 'Редактирование товара' : 'Добавление товара' }}
        </h2>

        <form @submit.prevent="handleSubmit" class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
          <div>
            <label class="block text-sm font-medium mb-2" style="color: #E79796">Название *</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #E79796"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #E79796">Описание *</label>
            <textarea
              v-model="formData.description"
              rows="3"
              required
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #E79796"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2" style="color: #E79796">Цена *</label>
              <input
                v-model.number="formData.price"
                type="number"
                required
                min="0"
                step="0.01"
                class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
                style="border-color: #E79796"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2" style="color: #E79796">Категория *</label>
              <select
                v-model="formData.category"
                required
                class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
                style="border-color: #E79796"
              >
                <option value="">Выберите категорию</option>
                <option v-for="cat in adminStore.categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #E79796">Размер</label>
            <input
              v-model="formData.size"
              type="text"
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #E79796"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #E79796">Техника *</label>
            <input
              v-model="formData.technique"
              type="text"
              required
              class="w-full px-4 py-3 rounded-lg border-2 focus:border-primary-500 focus:outline-none transition-colors"
              style="border-color: #E79796"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
              <input
                v-model="formData.inStock"
                type="checkbox"
                id="inStock"
                class="w-5 h-5 rounded"
                style="accent-color: #E79796"
              />
              <label for="inStock" class="text-sm" style="color: #E79796">В наличии</label>
            </div>

            <div class="flex items-center gap-2">
              <input
                v-model="formData.featured"
                type="checkbox"
                id="featured"
                class="w-5 h-5 rounded"
                style="accent-color: #E79796"
              />
              <label for="featured" class="text-sm" style="color: #E79796">Хит</label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #E79796">Изображения</label>
            <div class="flex gap-2 mb-2">
              <button
                type="button"
                @click="addImage"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm"
                style="background-color: #F5CEC7; color: #E79796"
              >
                <Plus class="w-4 h-4" />
                Добавить
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              <div
                v-for="(img, index) in formData.images"
                :key="index"
                class="relative"
              >
                <img :src="img" class="w-20 h-20 object-cover rounded-lg" />
                <button
                  type="button"
                  @click="removeImage(index)"
                  class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-white text-xs"
                  style="background-color: #E79796"
                >
                  <X class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #E79796">Материалы</label>
            <div class="flex gap-2 mb-2">
              <button
                type="button"
                @click="addMaterial"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm"
                style="background-color: #F5CEC7; color: #E79796"
              >
                <Plus class="w-4 h-4" />
                Добавить
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="(mat, index) in formData.materials"
                :key="index"
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm"
                style="background-color: #F5CEC7; color: #E79796"
              >
                {{ mat }}
                <button
                  type="button"
                  @click="removeMaterial(index)"
                  class="hover:opacity-70"
                >
                  <X class="w-3 h-3" />
                </button>
              </span>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 py-3 rounded-lg text-sm"
              style="background-color: #E79796; color: white"
            >
              Отмена
            </button>
            <button
              type="submit"
              class="flex-1 py-3 rounded-lg text-sm"
              style="background-color: #F5CEC7; color: #E79796"
            >
              {{ editingProduct ? 'Сохранить' : 'Добавить' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
