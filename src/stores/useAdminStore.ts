import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as db from '@/lib/db'
import type { Category, Product } from '@/lib/db'

export const useAdminStore = defineStore('admin', () => {
  const categories = ref<Category[]>([])
  const products = ref<Product[]>([])
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Загрузка категорий
  const loadCategories = async () => {
    isLoading.value = true
    error.value = null
    try {
      categories.value = db.getCategories()
    } catch (e) {
      error.value = 'Ошибка загрузки категорий'
      console.error(e)
    } finally {
      isLoading.value = false
    }
  }

  // Загрузка товаров
  const loadProducts = async () => {
    isLoading.value = true
    error.value = null
    try {
      products.value = db.getProducts()
    } catch (e) {
      error.value = 'Ошибка загрузки товаров'
      console.error(e)
    } finally {
      isLoading.value = false
    }
  }

  // Создание категории
  const createCategory = async (category: Category) => {
    isLoading.value = true
    error.value = null
    try {
      db.createCategory(category)
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

  // Обновление категории
  const updateCategory = async (id: string, category: Partial<Category>) => {
    isLoading.value = true
    error.value = null
    try {
      db.updateCategory(id, category)
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

  // Удаление категории
  const deleteCategory = async (id: string) => {
    isLoading.value = true
    error.value = null
    try {
      db.deleteCategory(id)
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

  // Создание товара
  const createProduct = async (product: Product) => {
    isLoading.value = true
    error.value = null
    try {
      db.createProduct(product)
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

  // Обновление товара
  const updateProduct = async (id: string, product: Partial<Product>) => {
    isLoading.value = true
    error.value = null
    try {
      db.updateProduct(id, product)
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

  // Удаление товара
  const deleteProduct = async (id: string) => {
    isLoading.value = true
    error.value = null
    try {
      db.deleteProduct(id)
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

  // Аутентификация
  const login = async (username: string, password: string) => {
    isLoading.value = true
    error.value = null
    try {
      const success = db.authenticateUser(username, password)
      if (success) {
        isAuthenticated.value = true
        return true
      } else {
        error.value = 'Неверный логин или пароль'
        return false
      }
    } catch (e) {
      error.value = 'Ошибка аутентификации'
      console.error(e)
      return false
    } finally {
      isLoading.value = false
    }
  }

  // Выход
  const logout = () => {
    isAuthenticated.value = false
    error.value = null
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
