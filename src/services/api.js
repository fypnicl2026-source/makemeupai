import axios from "axios";

const BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

let csrfToken = null;

const api = axios.create({
  baseURL: BASE_URL,
  withCredentials: true,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

export async function getCsrf() {
  await api.get("/sanctum/csrf-cookie");
  const { data } = await api.get("/sanctum/csrf-token");
  csrfToken = data.csrf_token;
}

api.interceptors.request.use((config) => {
  if (csrfToken && ["post", "put", "patch", "delete"].includes(config.method)) {
    config.headers["X-CSRF-TOKEN"] = csrfToken;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      const requestUrl = error.config?.url ?? "";

      if (!requestUrl.includes("/api/auth/me")) {
        const { authStore } = await import("../stores/auth.js");
        authStore.user = null;
        authStore.isLoggedIn = false;

        const router = (await import("../router/index.js")).default;
        if (router.currentRoute.value.path !== "/signin") {
          await router.push("/signin");
        }
      }
    }

    return Promise.reject(error);
  }
);

export default api;
