import { apiDelete, apiGet, apiPost, apiPut } from '@/api/http'

export const login = async (username, password) => {
  return apiPost('/login', { username, password })
}

export const logout = async () => {
  return apiPost('/logout')
}

export const getCategories = async () => {
  return apiGet('/categories')
}

export const createCategory = async (category) => {
  return apiPost('/categories', category)
}

export const updateCategory = async (id, category) => {
  return apiPut(`/categories/${id}`, category)
}

export const deleteCategory = async (id) => {
  return apiDelete(`/categories/${id}`)
}

export const getProducts = async () => {
  return apiGet('/products')
}

export const createProduct = async (product) => {
  return apiPost('/products', product)
}

export const updateProduct = async (id, product) => {
  return apiPut(`/products/${id}`, product)
}

export const deleteProduct = async (id) => {
  return apiDelete(`/products/${id}`)
}
