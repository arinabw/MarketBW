"""Один процесс: FastAPI + раздача Vue SPA из dist (без nginx в контейнере)."""

from __future__ import annotations

import os
from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import APIRouter, FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import FileResponse
from fastapi.staticfiles import StaticFiles

from app.database import (
    authenticate_user,
    create_category,
    create_product,
    delete_category,
    delete_product,
    get_categories,
    get_faqs,
    get_products,
    get_reviews,
    init_db,
    update_category,
    update_product,
)

BASE_DIR = Path(__file__).resolve().parent.parent
DIST_DIR = BASE_DIR / "dist"


@asynccontextmanager
async def lifespan(_app: FastAPI):
    init_db()
    yield


app = FastAPI(title="MarketBW", lifespan=lifespan, docs_url=False, redoc_url=False)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

api = APIRouter(prefix="/api")


@api.get("/categories")
def api_categories():
    try:
        return get_categories()
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при получении категорий")


@api.post("/categories")
def api_create_category(body: dict):
    name = body.get("name")
    image = body.get("image")
    if not name or not image:
        raise HTTPException(status_code=400, detail="Не все обязательные поля заполнены")
    try:
        return create_category(name, body.get("description"), image)
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при создании категории")


@api.put("/categories/{cid}")
def api_update_category(cid: str, body: dict):
    try:
        update_category(cid, body.get("name"), body.get("description"), body.get("image"))
        return {"success": True}
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при обновлении категории")


@api.delete("/categories/{cid}")
def api_delete_category(cid: str):
    try:
        delete_category(cid)
        return {"success": True}
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при удалении категории")


@api.get("/products")
def api_products():
    try:
        return get_products()
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при получении товаров")


@api.get("/products/{pid}")
def api_product_by_id(pid: str):
    try:
        from app.database import get_products
        products = get_products()
        product = next((p for p in products if p["id"] == pid), None)
        if not product:
            raise HTTPException(status_code=404, detail="Товар не найден")
        return product
    except HTTPException:
        raise
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при получении товара")


@api.get("/reviews")
def api_reviews(product_id: str | None = None):
    try:
        return get_reviews(product_id)
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при получении отзывов")


@api.get("/faqs")
def api_faqs():
    try:
        return get_faqs()
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при получении FAQ")


@api.post("/products")
def api_create_product(body: dict):
    if not body.get("name") or body.get("price") in (None, "") or not body.get("category"):
        raise HTTPException(status_code=400, detail="Не все обязательные поля заполнены")
    try:
        return create_product(
            name=body["name"],
            description=body.get("description") or "",
            price=float(body["price"]),
            category=body["category"],
            images=body.get("images") or [],
            materials=body.get("materials") or [],
            size=body.get("size"),
            technique=body.get("technique") or "",
            in_stock=bool(body.get("inStock", True)),
            featured=bool(body.get("featured", False)),
        )
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при создании товара")


@api.put("/products/{pid}")
def api_update_product(pid: str, body: dict):
    try:
        update_product(
            pid,
            name=body.get("name"),
            description=body.get("description"),
            price=float(body["price"]) if body.get("price") is not None else None,
            category=body.get("category"),
            images=body.get("images"),
            materials=body.get("materials"),
            size=body.get("size"),
            technique=body.get("technique"),
            in_stock=body.get("inStock"),
            featured=body.get("featured"),
        )
        return {"success": True}
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при обновлении товара")


@api.delete("/products/{pid}")
def api_delete_product(pid: str):
    try:
        delete_product(pid)
        return {"success": True}
    except Exception:
        raise HTTPException(status_code=500, detail="Ошибка при удалении товара")


@api.post("/login")
def api_login(body: dict):
    username = body.get("username")
    password = body.get("password")
    if not username or not password:
        raise HTTPException(status_code=400, detail="Не все обязательные поля заполнены")
    if authenticate_user(username, password):
        return {"success": True}
    raise HTTPException(status_code=401, detail="Неверный логин или пароль")


@api.post("/logout")
def api_logout():
    return {"success": True}


app.include_router(api)

# Раздача статики из dist
_assets = DIST_DIR / "assets"
if _assets.is_dir():
    app.mount("/assets", StaticFiles(directory=str(_assets)), name="assets")

# Раздача изображений из public/images
_public_images = BASE_DIR / "public" / "images"
if _public_images.is_dir():
    app.mount("/images", StaticFiles(directory=str(_public_images)), name="images")


def _serve_dist_or_spa(full_path: str) -> FileResponse:
    """Статика из dist или index.html (Vue Router + /admin)."""
    rel = full_path.lstrip("/") if full_path else ""
    if rel and ".." in rel.split("/"):
        raise HTTPException(status_code=404)
    # На случай если запрос попал сюда вместо роутера /api
    if rel == "api" or rel.startswith("api/"):
        raise HTTPException(status_code=404)
    if rel:
        candidate = (DIST_DIR / rel).resolve()
        try:
            candidate.relative_to(DIST_DIR.resolve())
        except ValueError:
            raise HTTPException(status_code=404)
        if candidate.is_file():
            return FileResponse(candidate)
    index = DIST_DIR / "index.html"
    if not index.is_file():
        raise HTTPException(status_code=503, detail="dist не собран")
    return FileResponse(index)


@app.get("/")
def root_index():
    return _serve_dist_or_spa("")


@app.get("/{full_path:path}")
def spa_or_static(full_path: str):
    return _serve_dist_or_spa(full_path)


# uvicorn app.main:app — при прямом запуске файла
if __name__ == "__main__":
    import uvicorn

    port = int(os.environ.get("PORT", "8000"))
    uvicorn.run("app.main:app", host="0.0.0.0", port=port, reload=True)
