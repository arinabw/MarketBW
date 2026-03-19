import express from 'express'
import cors from 'cors'
import path from 'path'
import { fileURLToPath } from 'url'
import { dirname } from 'path'
import * as db from './src/lib/db.js'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

const app = express()
const PORT = process.env.PORT || 3000

// Middleware
app.use(cors())
app.use(express.json())
app.use(express.static(path.join(__dirname, 'dist')))

// API Routes
app.get('/api/categories', (req, res) => {
  try {
    const categories = db.getCategories()
    res.json(categories)
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при получении категорий' })
  }
})

app.post('/api/categories', (req, res) => {
  try {
    const { name, description, image } = req.body
    if (!name || !image) {
      return res.status(400).json({ error: 'Не все обязательные поля заполнены' })
    }

    const category = {
      id: Date.now().toString(),
      name,
      description,
      image
    }

    db.createCategory(category)
    res.json(category)
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при создании категории' })
  }
})

app.put('/api/categories/:id', (req, res) => {
  try {
    const { id } = req.params
    const { name, description, image } = req.body

    db.updateCategory(id, { name, description, image })
    res.json({ success: true })
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при обновлении категории' })
  }
})

app.delete('/api/categories/:id', (req, res) => {
  try {
    const { id } = req.params
    db.deleteCategory(id)
    res.json({ success: true })
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при удалении категории' })
  }
})

app.get('/api/products', (req, res) => {
  try {
    const products = db.getProducts()
    res.json(products)
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при получении товаров' })
  }
})

app.post('/api/products', (req, res) => {
  try {
    const { name, description, price, category, images, materials, size, technique, inStock, featured } = req.body

    if (!name || !price || !category) {
      return res.status(400).json({ error: 'Не все обязательные поля заполнены' })
    }

    const product = {
      id: Date.now().toString(),
      name,
      description,
      price,
      category,
      images: images || [],
      materials: materials || [],
      size,
      technique,
      in_stock: inStock,
      featured
    }

    db.createProduct(product)
    res.json(product)
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при создании товара' })
  }
})

app.put('/api/products/:id', (req, res) => {
  try {
    const { id } = req.params
    const { name, description, price, category, images, materials, size, technique, inStock, featured } = req.body

    db.updateProduct(id, {
      name,
      description,
      price,
      category,
      images,
      materials,
      size,
      technique,
      in_stock: inStock,
      featured
    })
    res.json({ success: true })
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при обновлении товара' })
  }
})

app.delete('/api/products/:id', (req, res) => {
  try {
    const { id } = req.params
    db.deleteProduct(id)
    res.json({ success: true })
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при удалении товара' })
  }
})

app.post('/api/login', (req, res) => {
  try {
    const { username, password } = req.body

    if (!username || !password) {
      return res.status(400).json({ error: 'Не все обязательные поля заполнены' })
    }

    const success = db.authenticateUser(username, password)
    if (success) {
      res.json({ success: true })
    } else {
      res.status(401).json({ error: 'Неверный логин или пароль' })
    }
  } catch (error) {
    res.status(500).json({ error: 'Ошибка при аутентификации' })
  }
})

app.post('/api/logout', (req, res) => {
  res.json({ success: true })
})

// Serve admin panel
app.get('/admin', (req, res) => {
  res.sendFile(path.join(__dirname, 'dist', 'index.html'))
})

app.get('/admin/*', (req, res) => {
  res.sendFile(path.join(__dirname, 'dist', 'index.html'))
})

// Start server
app.listen(PORT, () => {
  console.log(`Admin server running on port ${PORT}`)
})
