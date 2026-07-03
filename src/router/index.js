import { createRouter, createWebHistory } from "vue-router";
import BeauticiansView from "../views/BeauticiansView.vue";
import BookingsView from "../views/BookingsView.vue";
import CheckEmailView from "../views/CheckEmailView.vue";
import DashboardView from "../views/DashboardView.vue";
import FaceInsightsView from "../views/FaceInsightsView.vue";
import FeaturesView from "../views/FeaturesView.vue";
import ForgotPasswordView from "../views/ForgotPasswordView.vue";
import HomeView from "../views/HomeView.vue";
import HowItWorksView from "../views/HowItWorksView.vue";
import NotFoundView from "../views/NotFoundView.vue";
import PricingView from "../views/PricingView.vue";
import RecommendationsView from "../views/RecommendationsView.vue";
import ResetPasswordView from "../views/ResetPasswordView.vue";
import SignInView from "../views/SignInView.vue";
import SignUpView from "../views/SignUpView.vue";
import VerifyEmailView from "../views/VerifyEmailView.vue";
import WardrobeView from "../views/WardrobeView.vue";
import { applyPageSeo } from "../composables/usePageSeo";
import { authStore } from "../stores/auth";

const authPages = ["/signin", "/signup", "/forgot-password", "/reset-password"];
const verificationPages = ["/check-email", "/verify-email"];

const routes = [
  { path: "/", name: "home", component: HomeView, meta: { title: "Home" } },
  { path: "/features", name: "features", component: FeaturesView, meta: { title: "Features" } },
  {
    path: "/how-it-works",
    name: "how-it-works",
    component: HowItWorksView,
    meta: { title: "How It Works" },
  },
  {
    path: "/beauticians",
    name: "beauticians",
    component: BeauticiansView,
    meta: { title: "Beauticians" },
  },
  { path: "/wardrobe", name: "wardrobe", component: WardrobeView, meta: { title: "My Wardrobe", requiresAuth: true, layout: "dashboard" } },
  { path: "/pricing", name: "pricing", component: PricingView, meta: { title: "Pricing" } },
  { path: "/signin", name: "signin", component: SignInView, meta: { title: "Sign In" } },
  { path: "/signup", name: "signup", component: SignUpView, meta: { title: "Sign Up" } },
  { path: "/forgot-password", name: "forgot-password", component: ForgotPasswordView, meta: { title: "Forgot Password" } },
  { path: "/reset-password", name: "reset-password", component: ResetPasswordView, meta: { title: "Reset Password" } },
  { path: "/check-email", name: "check-email", component: CheckEmailView, meta: { title: "Verify Email", requiresAuth: true } },
  { path: "/verify-email", name: "verify-email", component: VerifyEmailView, meta: { title: "Email Verified" } },
  {
    path: "/dashboard",
    name: "dashboard",
    component: DashboardView,
    meta: { title: "Dashboard", requiresAuth: true, layout: "dashboard" },
  },
  {
    path: "/recommendations",
    name: "recommendations",
    component: RecommendationsView,
    meta: { title: "Outfit Recommendations", requiresAuth: true, layout: "dashboard" },
  },
  {
    path: "/face-insights",
    name: "face-insights",
    component: FaceInsightsView,
    meta: { title: "Face Insights", requiresAuth: true, layout: "dashboard" },
  },
  {
    path: "/bookings",
    name: "bookings",
    component: BookingsView,
    meta: { title: "My Bookings", requiresAuth: true, layout: "dashboard" },
  },
  { path: "/:pathMatch(.*)*", name: "not-found", component: NotFoundView, meta: { title: "Not Found" } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

const sessionReady = authStore.fetchUser();

router.beforeEach(async (to) => {
  await sessionReady;

  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return { path: "/signin", query: { redirect: to.fullPath } };
  }

  if (authStore.isLoggedIn && !authStore.emailVerified) {
    const isVerificationPage = verificationPages.includes(to.path);
    const isProtected = Boolean(to.meta.requiresAuth) && to.path !== "/check-email";

    if (isProtected && !isVerificationPage) {
      return { path: "/check-email" };
    }
  }

  if (authStore.isLoggedIn && authStore.emailVerified && authPages.includes(to.path)) {
    return { path: "/dashboard" };
  }

  if (authStore.isLoggedIn && authStore.emailVerified && to.path === "/check-email") {
    return { path: "/dashboard" };
  }
});

router.afterEach((to) => {
  applyPageSeo(to);
});

export default router;
