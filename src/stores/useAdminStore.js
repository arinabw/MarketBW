import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as api from '@/api/admin'

export const useAdminStore = defineStore('admin', () => {
  const categories = ref([])
  const products = ref([])
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const error = ref(null)

  const loadCategories = async () => {
    isLoading.value = true
    error.value = null
    try {
      categories.value = await api.getCategories()
    } catch (e) {
      error.value = 'Ошибка загрузки категорий'
      console.error(e)
    } finally {
      isLoading.value = false
    }
  }

  const loadProducts = async () => {
    isLoading.value = true
    error.value = null
    try {
      products.value = await api.getProducts()
    } catch (e) {
      error.value = 'Ошибка загрузки товаров'
      console.error(e)
    } finally {
      isLoading.value = false
    }
  }

  const createCategory = async (category) => {
    isLoading.value = true
    error.value = null
    try {
      await api.createCategory({
        name: category.name,
        description: category.description,
        image: category.image,
      })
      await loadCategories()
      return true
    } catch (e) {
      error.value = 'Ошибка создания категории'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const updateCategory = async (id, category) => {
    isLoading.value = true
    error.value = null
    try {
      const prev = categories.value.find((c) => c.id === id)
      await api.updateCategory(id, {
        name: category.name ?? prev?.name ?? '',
        description: category.description ?? prev?.description ?? '',
        image: category.image ?? prev?.image ?? '',
      })
      await loadCategories()
      return true
    } catch (e) {
      error.value = 'Ошибка обновления категории'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const deleteCategory = async (id) => {
    isLoading.value = true
    error.value = null
    try {
      await api.deleteCategory(id)
      await loadCategories()
      return true
    } catch (e) {
      error.value = 'Ошибка удаления категории'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const createProduct = async (product) => {
    isLoading.value = true
    error.value = null
    try {
      await api.createProduct({
        name: product.name,
        description: product.description,
        price: product.price,
        category: product.category,
        images: product.images,
        materials: product.materials,
        size: product.size,
        technique: product.technique,
        inStock: product.inStock,
        featured: product.featured,
      })
      await loadProducts()
      return true
    } catch (e) {
      error.value = 'Ошибка создания товара'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const updateProduct = async (id, product) => {
    isLoading.value = true
    error.value = null
    try {
      const prev = products.value.find((p) => p.id === id)
      if (!prev) {
        error.value = 'Товар не найден'
        return false
      }
      await api.updateProduct(id, {
        name: product.name ?? prev.name,
        description: product.description ?? prev.description,
        price: product.price ?? prev.price,
        category: product.category ?? prev.category,
        images: product.images ?? prev.images,
        materials: product.materials ?? prev.materials,
        size: product.size ?? prev.size,
        technique: product.technique ?? prev.technique,
        inStock: product.inStock ?? prev.in_stock,
        featured: product.featured ?? prev.featured,
      })
      await loadProducts()
      return true
    } catch (e) {
      error.value = 'Ошибка обновления товара'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const deleteProduct = async (id) => {
    isLoading.value = true
    error.value = null
    try {
      await api.deleteProduct(id)
      await loadProducts()
      return true
    } catch (e) {
      error.value = 'Ошибка удаления товара'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const login = async (username, password) => {
    isLoading.value = true
    error.value = null
    try {
      const data = await api.login(username, password)
      if (data?.success) {
        isAuthenticated.value = true
        return true
      }
      error.value = 'Неверный логин или пароль'
      return false
    } catch (e) {
      error.value = 'Неверный логин или пароль'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const logout = () => {
    isAuthenticated.value = false
    error.value = null
    void api.logout()
  }

  return {
    categories,
    products,
    isAuthenticated,
    isLoading,
    error,
    loadCategories,
    loadProducts,
    createCategory,
    updateCategory,
    deleteCategory,
    createProduct,
    updateProduct,
    deleteProduct,
    login,
    logout,
  }
})
