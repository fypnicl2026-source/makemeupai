import { reactive } from "vue";
import api, { getAuthToken, getCsrf, setAuthToken } from "../services/api";

function setAuthenticatedUser(store, user) {
  store.user = user;
  store.isLoggedIn = true;
}

function clearUser(store) {
  store.user = null;
  store.isLoggedIn = false;
  setAuthToken(null);
}

export const authStore = reactive({
  user: null,
  isLoggedIn: false,
  loading: false,

  async login(email, password) {
    this.loading = true;
    try {
      await getCsrf();
      const { data } = await api.post("/api/auth/login", { email, password });
      setAuthToken(data.data.token);
      await this.fetchUser();
      return data;
    } finally {
      this.loading = false;
    }
  },

  async register(name, email, password, passwordConfirmation, city) {
    this.loading = true;
    try {
      await getCsrf();
      const { data } = await api.post("/api/auth/register", {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        city,
      });
      setAuthToken(data.data.token);
      await this.fetchUser();
      return data;
    } finally {
      this.loading = false;
    }
  },

  async logout() {
    this.loading = true;
    try {
      await getCsrf();
      await api.post("/api/auth/logout");
      clearUser(this);
    } finally {
      this.loading = false;
    }
  },

  async fetchUser() {
    this.loading = true;
    try {
      if (!getAuthToken()) {
        clearUser(this);
        return null;
      }

      const { data } = await api.get("/api/auth/me");
      setAuthenticatedUser(this, data.data.user);
      return data;
    } catch (error) {
      if (error.response?.status === 401 || !error.response) {
        clearUser(this);
        return null;
      }
      throw error;
    } finally {
      this.loading = false;
    }
  },
});
