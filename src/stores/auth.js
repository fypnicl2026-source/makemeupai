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

  get emailVerified() {
    return Boolean(this.user?.email_verified);
  },

  async login(email, password, remember = false) {
    this.loading = true;
    try {
      await getCsrf();
      const { data } = await api.post("/api/auth/login", { email, password, remember });
      setAuthToken(data.data.token);
      await this.fetchUser();
      return data;
    } finally {
      this.loading = false;
    }
  },

  async register(name, email, password, passwordConfirmation, city, gender) {
    this.loading = true;
    try {
      await getCsrf();
      const payload = {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        city,
      };
      if (gender) payload.gender = gender;
      const { data } = await api.post("/api/auth/register", payload);
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

  async forgotPassword(email) {
    await getCsrf();
    const { data } = await api.post("/api/auth/forgot-password", { email });
    return data;
  },

  async resetPassword(token, email, password, passwordConfirmation) {
    await getCsrf();
    const { data } = await api.post("/api/auth/reset-password", {
      token,
      email,
      password,
      password_confirmation: passwordConfirmation,
    });
    return data;
  },

  async resendVerification() {
    await getCsrf();
    const { data } = await api.post("/api/auth/email/resend");
    return data;
  },
});
