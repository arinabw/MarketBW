# syntax=docker/dockerfile:1
# Один контейнер: FastAPI (uvicorn) + статика Vue из dist. Сборка фронта — Node, рантайм — Python.
# BuildKit: DOCKER_BUILDKIT=1 — кэш npm между сборками (см. deploy.sh).

FROM node:20-alpine AS frontend
WORKDIR /build

COPY frontend/package*.json ./frontend/
RUN --mount=type=cache,target=/root/.npm \
    sh -c '\
    echo "[build-time] frontend: npm ci/install START $(date -u +%Y-%m-%dT%H:%M:%SZ)"; \
    cd frontend && if [ -f package-lock.json ]; then npm ci --no-audit --no-fund; else npm install --no-audit --no-fund; fi; \
    echo "[build-time] frontend: npm ci/install END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"'

COPY index.html ./
COPY src ./src
COPY public ./public
COPY tailwind.config.js postcss.config.cjs tsconfig.json env.d.ts ./
COPY frontend/vite.config.ts frontend/tsconfig.node.json ./frontend/

ENV NODE_ENV=production
RUN echo "[build-time] frontend: vite build START $(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    && cd frontend && npm run build \
    && echo "[build-time] frontend: vite build END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"

FROM python:3.13-slim AS runtime
WORKDIR /app

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    DATA_DIR=/app/data

COPY backend/requirements.txt /tmp/requirements.txt
RUN echo "[build-time] runtime: pip install START $(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    && pip install --no-cache-dir -r /tmp/requirements.txt \
    && rm /tmp/requirements.txt \
    && echo "[build-time] runtime: pip install END   $(date -u +%Y-%m-%dT%H:%M:%SZ)"

COPY backend/app ./app
COPY --from=frontend /build/dist ./dist

RUN mkdir -p /app/data

EXPOSE 8000
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000"]
