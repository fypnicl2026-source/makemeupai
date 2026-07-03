import axios from "axios";
import { triggerUnauthorized, triggerUnverified } from "./authEvents";

const BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";
const AUTH_TOKEN_KEY = "auth_token";

let csrfToken = null;
let authToken = sessionStorage.getItem(AUTH_TOKEN_KEY);

const api = axios.create({
  baseURL: BASE_URL,
  withCredentials: true,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

export function getAuthToken() {
  return authToken || sessionStorage.getItem(AUTH_TOKEN_KEY);
}

export function setAuthToken(token) {
  authToken = token;
  if (token) {
    sessionStorage.setItem(AUTH_TOKEN_KEY, token);
  } else {
    sessionStorage.removeItem(AUTH_TOKEN_KEY);
  }
}

export async function getCsrf() {
  await api.get("/sanctum/csrf-cookie");
  const { data } = await api.get("/sanctum/csrf-token");
  csrfToken = data.csrf_token;
}

api.interceptors.request.use((config) => {
  const token = getAuthToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  if (csrfToken && ["post", "put", "patch", "delete"].includes(config.method)) {
    config.headers["X-CSRF-TOKEN"] = csrfToken;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status;
    const requestUrl = error.config?.url ?? "";

    if (status === 403 && error.response?.data?.code === "unverified") {
      triggerUnverified();
    }

    if (status === 401 && !requestUrl.includes("/api/auth/me")) {
      triggerUnauthorized();
    }

    return Promise.reject(error);
  }
);

export default api;
