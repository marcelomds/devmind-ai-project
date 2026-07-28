const BASE_URL = import.meta.env.VITE_API_URL;
const TOKEN_STORAGE_KEY = "devmind_token";

let token: string | null = localStorage.getItem(TOKEN_STORAGE_KEY);
let unauthorizedHandler: (() => void) | null = null;

export function getToken(): string | null {
  return token;
}

export function setToken(newToken: string | null): void {
  token = newToken;

  if (newToken) {
    localStorage.setItem(TOKEN_STORAGE_KEY, newToken);
  } else {
    localStorage.removeItem(TOKEN_STORAGE_KEY);
  }
}

// Lets the auth layer react (e.g. drop back to the login screen) whenever any
// request comes back 401, without this module needing to know about React state.
export function onUnauthorized(handler: () => void): void {
  unauthorizedHandler = handler;
}

function authHeaders(): Record<string, string> {
  return token ? { Authorization: `Bearer ${token}` } : {};
}

async function parseResponse<T>(response: Response): Promise<T> {
  if (response.status === 401) {
    setToken(null);
    unauthorizedHandler?.();
    throw new Error("API error: 401");
  }

  if (!response.ok) {
    throw new Error(`API error: ${response.status}`);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return response.json() as Promise<T>;
}

export async function apiGet<T>(path: string): Promise<T> {
  const response = await fetch(`${BASE_URL}${path}`, {
    headers: { Accept: "application/json", ...authHeaders() },
  });

  return parseResponse<T>(response);
}

export async function apiPost<T>(path: string, body: unknown): Promise<T> {
  const response = await fetch(`${BASE_URL}${path}`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...authHeaders(),
    },
    body: JSON.stringify(body),
  });

  return parseResponse<T>(response);
}

export async function apiPatch<T>(path: string, body: unknown): Promise<T> {
  const response = await fetch(`${BASE_URL}${path}`, {
    method: "PATCH",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...authHeaders(),
    },
    body: JSON.stringify(body),
  });

  return parseResponse<T>(response);
}

export async function apiDelete(path: string): Promise<void> {
  const response = await fetch(`${BASE_URL}${path}`, {
    method: "DELETE",
    headers: { Accept: "application/json", ...authHeaders() },
  });

  await parseResponse<void>(response);
}
