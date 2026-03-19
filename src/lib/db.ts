import Database from 'better-sqlite3'
import path from 'path'
import fs from 'fs'

const DB_DIR = path.join(process.cwd(), 'data')
const DB_PATH = path.join(DB_DIR, 'marketbw.db')

// Создаем директорию для БД, если она не существует
if (!fs.existsSync(DB_DIR)) {
  fs.mkdirSync(DB_DIR, { recursive: true })
}

const db = new Database(DB_PATH)

// Инициализация таблиц
db.exec(`
  CREATE TABLE IF NOT EXISTS categories (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    image TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS products (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    price REAL NOT NULL,
    category TEXT NOT NULL,
    images TEXT NOT NULL,
    materials TEXT NOT NULL,
    size TEXT,
    technique TEXT NOT NULL,
    in_stock INTEGER DEFAULT 1,
    featured INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS users (
    id TEXT PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );
`)

// Инициализация админ-пользователя (пароль: admin123)
const adminUser = db.prepare('SELECT * FROM users WHERE username = ?').get('admin')
if (!adminUser) {
  db.prepare('INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)')
    .run('1', 'admin', 'admin123')
}

export interface Category {
  id: string
  name: string
  description: string
  image: string
  created_at: string
}

export interface Product {
  id: string
  name: string
  description: string
  price: number
  category: string
  images: string[]
  materials: string[]
  size?: string
  technique: string
  in_stock: boolean
  featured: boolean
  created_at: string
}

export interface User {
  id: string
  username: string
  password_hash: string
  created_at: string
}

// Категории
export const getCategories = (): Category[] => {
  return db.prepare('SELECT * FROM categories ORDER BY created_at DESC').all() as Category[]
}

export const createCategory = (category: Category) => {
  return db.prepare('INSERT INTO categories (id, name, description, image) VALUES (?, ?, ?, ?)')
    .run(category.id, category.name, category.description, category.image)
}

export const updateCategory = (id: string, category: Partial<Category>) => {
  const fields: string[] = []
  const values: any[] = []

  if (category.name !== undefined) {
    fields.push('name = ?')
    values.push(category.name)
  }
  if (category.description !== undefined) {
    fields.push('description = ?')
    values.push(category.description)
  }
  if (category.image !== undefined) {
    fields.push('image = ?')
    values.push(category.image)
  }

  values.push(id)

  return db.prepare(`UPDATE categories SET ${fields.join(', ')} WHERE id = ?`).run(...values)
}

export const deleteCategory = (id: string) => {
  return db.prepare('DELETE FROM categories WHERE id = ?').run(id)
}

// Товары
export const getProducts = (): Product[] => {
  return db.prepare('SELECT * FROM products ORDER BY created_at DESC').all() as Product[]
}

export const createProduct = (product: Product) => {
  return db.prepare('INSERT INTO products (id, name, description, price, category, images, materials, size, technique, in_stock, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
    .run(product.id, product.name, product.description, product.price, product.category, JSON.stringify(product.images), JSON.stringify(product.materials), product.size, product.technique, product.in_stock ? 1 : 0, product.featured ? 1 : 0)
}

export const updateProduct = (id: string, product: Partial<Product>) => {
  const fields: string[] = []
  const values: any[] = []

  if (product.name !== undefined) {
    fields.push('name = ?')
    values.push(product.name)
  }
  if (product.description !== undefined) {
    fields.push('description = ?')
    values.push(product.description)
  }
  if (product.price !== undefined) {
    fields.push('price = ?')
    values.push(product.price)
  }
  if (product.category !== undefined) {
    fields.push('category = ?')
    values.push(product.category)
  }
  if (product.images !== undefined) {
    fields.push('images = ?')
    values.push(JSON.stringify(product.images))
  }
  if (product.materials !== undefined) {
    fields.push('materials = ?')
    values.push(JSON.stringify(product.materials))
  }
  if (product.size !== undefined) {
    fields.push('size = ?')
    values.push(product.size)
  }
  if (product.technique !== undefined) {
    fields.push('technique = ?')
    values.push(product.technique)
  }
  if (product.in_stock !== undefined) {
    fields.push('in_stock = ?')
    values.push(product.in_stock ? 1 : 0)
  }
  if (product.featured !== undefined) {
    fields.push('featured = ?')
    values.push(product.featured ? 1 : 0)
  }

  values.push(id)

  return db.prepare(`UPDATE products SET ${fields.join(', ')} WHERE id = ?`).run(...values)
}

export const deleteProduct = (id: string) => {
  return db.prepare('DELETE FROM products WHERE id = ?').run(id)
}

// Аутентификация
export const authenticateUser = (username: string, password: string): boolean => {
  const user = db.prepare('SELECT * FROM users WHERE username = ?').get(username) as User | undefined
  if (!user) return false
  return user.password_hash === password
}

export default db
