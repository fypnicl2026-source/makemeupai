import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import { authStore } from "./stores/auth";
import { onUnauthorized, onUnverified } from "./services/authEvents";
import { setAuthToken } from "./services/api";
import "./assets/main.css";

onUnauthorized(() => {
  setAuthToken(null);
  authStore.user = null;
  authStore.isLoggedIn = false;

  if (router.currentRoute.value.path !== "/signin") {
    router.push("/signin");
  }
});

onUnverified(() => {
  if (router.currentRoute.value.path !== "/check-email") {
    router.push("/check-email");
  }
});

createApp(App).use(router).mount("#app");
