"""SQLite — та же схема, что была в src/lib/db.ts (совместимость с существующей БД)."""

from __future__ import annotations

import json
import os
import sqlite3
import time
from pathlib import Path

DATA_DIR = Path(os.environ.get("DATA_DIR", "/app/data"))
DB_PATH = DATA_DIR / "marketbw.db"

SCHEMA = """
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

CREATE TABLE IF NOT EXISTS reviews (
    id TEXT PRIMARY KEY,
    author TEXT NOT NULL,
    rating INTEGER NOT NULL,
    text TEXT NOT NULL,
    date TEXT NOT NULL,
    product_id TEXT
);

CREATE TABLE IF NOT EXISTS faqs (
    id TEXT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category TEXT NOT NULL
);
"""


def get_conn() -> sqlite3.Connection:
    DATA_DIR.mkdir(parents=True, exist_ok=True)
    conn = sqlite3.connect(str(DB_PATH))
    conn.row_factory = sqlite3.Row
    return conn


def init_db() -> None:
    from app.seed_content import seed_content_if_empty

    with get_conn() as conn:
        conn.executescript(SCHEMA)
        row = conn.execute("SELECT 1 FROM users WHERE username = ?", ("admin",)).fetchone()
        if not row:
            conn.execute(
                "INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)",
                ("1", "admin", "admin123"),
            )
        seed_content_if_empty(conn)


def _category_row(r: sqlite3.Row) -> dict:
    return {
        "id": r["id"],
        "name": r["name"],
        "description": r["description"] or "",
        "image": r["image"],
        "created_at": r["created_at"],
    }


def _product_row(r: sqlite3.Row) -> dict:
    return {
        "id": r["id"],
        "name": r["name"],
        "description": r["description"],
        "price": r["price"],
        "category": r["category"],
        "images": json.loads(r["images"] or "[]"),
        "materials": json.loads(r["materials"] or "[]"),
        "size": r["size"],
        "technique": r["technique"],
        "in_stock": bool(r["in_stock"]),
        "featured": bool(r["featured"]),
        "created_at": r["created_at"],
    }


def get_categories() -> list[dict]:
    with get_conn() as conn:
        rows = conn.execute("SELECT * FROM categories ORDER BY created_at DESC").fetchall()
        return [_category_row(r) for r in rows]


def create_category(name: str, description: str | None, image: str) -> dict:
    cid = str(int(time.time() * 1000))
    with get_conn() as conn:
        conn.execute(
            "INSERT INTO categories (id, name, description, image) VALUES (?, ?, ?, ?)",
            (cid, name, description or "", image),
        )
    return {"id": cid, "name": name, "description": description or "", "image": image}


def update_category(cid: str, name: str | None, description: str | None, image: str | None) -> None:
    fields: list[str] = []
    values: list = []
    if name is not None:
        fields.append("name = ?")
        values.append(name)
    if description is not None:
        fields.append("description = ?")
        values.append(description)
    if image is not None:
        fields.append("image = ?")
        values.append(image)
    if not fields:
        return
    values.append(cid)
    with get_conn() as conn:
        conn.execute(f"UPDATE categories SET {', '.join(fields)} WHERE id = ?", values)


def delete_category(cid: str) -> None:
    with get_conn() as conn:
        conn.execute("DELETE FROM categories WHERE id = ?", (cid,))


def get_products() -> list[dict]:
    with get_conn() as conn:
        rows = conn.execute("SELECT * FROM products ORDER BY created_at DESC").fetchall()
        return [_product_row(r) for r in rows]


def create_product(
    name: str,
    description: str,
    price: float,
    category: str,
    images: list,
    materials: list,
    size: str | None,
    technique: str,
    in_stock: bool,
    featured: bool,
) -> dict:
    pid = str(int(time.time() * 1000))
    with get_conn() as conn:
        conn.execute(
            """INSERT INTO products
            (id, name, description, price, category, images, materials, size, technique, in_stock, featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)""",
            (
                pid,
                name,
                description,
                price,
                category,
                json.dumps(images or []),
                json.dumps(materials or []),
                size,
                technique,
                1 if in_stock else 0,
                1 if featured else 0,
            ),
        )
    return {
        "id": pid,
        "name": name,
        "description": description,
        "price": price,
        "category": category,
        "images": images or [],
        "materials": materials or [],
        "size": size,
        "technique": technique,
        "in_stock": in_stock,
        "featured": featured,
    }


def update_product(
    pid: str,
    name: str | None = None,
    description: str | None = None,
    price: float | None = None,
    category: str | None = None,
    images: list | None = None,
    materials: list | None = None,
    size: str | None = None,
    technique: str | None = None,
    in_stock: bool | None = None,
    featured: bool | None = None,
) -> None:
    fields: list[str] = []
    values: list = []
    if name is not None:
        fields.append("name = ?")
        values.append(name)
    if description is not None:
        fields.append("description = ?")
        values.append(description)
    if price is not None:
        fields.append("price = ?")
        values.append(price)
    if category is not None:
        fields.append("category = ?")
        values.append(category)
    if images is not None:
        fields.append("images = ?")
        values.append(json.dumps(images))
    if materials is not None:
        fields.append("materials = ?")
        values.append(json.dumps(materials))
    if size is not None:
        fields.append("size = ?")
        values.append(size)
    if technique is not None:
        fields.append("technique = ?")
        values.append(technique)
    if in_stock is not None:
        fields.append("in_stock = ?")
        values.append(1 if in_stock else 0)
    if featured is not None:
        fields.append("featured = ?")
        values.append(1 if featured else 0)
    if not fields:
        return
    values.append(pid)
    with get_conn() as conn:
        conn.execute(f"UPDATE products SET {', '.join(fields)} WHERE id = ?", values)


def delete_product(pid: str) -> None:
    with get_conn() as conn:
        conn.execute("DELETE FROM products WHERE id = ?", (pid,))


def _review_row(r: sqlite3.Row) -> dict:
    return {
        "id": r["id"],
        "author": r["author"],
        "rating": int(r["rating"]),
        "text": r["text"],
        "date": r["date"],
        "product_id": r["product_id"],
    }


def _faq_row(r: sqlite3.Row) -> dict:
    return {
        "id": r["id"],
        "question": r["question"],
        "answer": r["answer"],
        "category": r["category"],
    }


def get_reviews(product_id: str | None = None) -> list[dict]:
    with get_conn() as conn:
        if product_id:
            rows = conn.execute(
                "SELECT * FROM reviews WHERE product_id = ? ORDER BY date DESC",
                (product_id,),
            ).fetchall()
        else:
            rows = conn.execute("SELECT * FROM reviews ORDER BY date DESC").fetchall()
        return [_review_row(r) for r in rows]


def get_faqs() -> list[dict]:
    with get_conn() as conn:
        rows = conn.execute("SELECT * FROM faqs ORDER BY id").fetchall()
        return [_faq_row(r) for r in rows]


def authenticate_user(username: str, password: str) -> bool:
    with get_conn() as conn:
        row = conn.execute(
            "SELECT password_hash FROM users WHERE username = ?", (username,)
        ).fetchone()
    if not row:
        return False
    return row["password_hash"] == password
