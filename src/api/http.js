/** Общий HTTP-клиент на fetch (без axios). */

const API_BASE = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

function buildUrl(path, searchParams) {
  const p = path.startsWith('/') ? path : `/${path}`
  let url = `${API_BASE}${p}`
  if (searchParams && Object.keys(searchParams).length > 0) {
    url += `?${new URLSearchParams(searchParams).toString()}`
  }
  return url
}

export class ApiError extends Error {
  constructor(message, status) {
    super(message)
    this.name = 'ApiError'
    this.status = status
  }
}

function parseErrorMessage(text, statusText) {
  if (!text) return statusText
  try {
    const j = JSON.parse(text)
    if (typeof j.detail === 'string') return j.detail
    if (Array.isArray(j.detail)) return JSON.stringify(j.detail)
    if (typeof j.message === 'string') return j.message
  } catch {
    /* не JSON */
  }
  return text
}

export async function apiRequest(path, init = {}) {
  const { searchParams, ...rest } = init
  const filtered = {}
  if (searchParams) {
    for (const [k, v] of Object.entries(searchParams)) {
      if (v !== undefined) filtered[k] = v
    }
  }
  const url = buildUrl(path, Object.keys(filtered).length ? filtered : undefined)
  const headers = new Headers(rest.headers)
  if (rest.body !== undefined && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }
  const res = await fetch(url, { ...rest, headers })
  const text = await res.text()
  if (!res.ok) {
    throw new ApiError(parseErrorMessage(text, res.statusText), res.status)
  }
  if (!text) return undefined
  return JSON.parse(text)
}

export function apiGet(path, searchParams) {
  return apiRequest(path, { method: 'GET', searchParams })
}

export function apiPost(path, body) {
  return apiRequest(path, {
    method: 'POST',
    body: body !== undefined ? JSON.stringify(body) : undefined,
  })
}

export function apiPut(path, body) {
  return apiRequest(path, {
    method: 'PUT',
    body: body !== undefined ? JSON.stringify(body) : undefined,
  })
}

export function apiDelete(path) {
  return apiRequest(path, { method: 'DELETE' })
}
