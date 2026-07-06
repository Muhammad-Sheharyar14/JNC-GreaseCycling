import { createRouter, createWebHashHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import LoginView from '../views/LoginView.vue';
import RouteView from '../views/RouteView.vue';
import StopDetailView from '../views/StopDetailView.vue';
import PickupView from '../views/PickupView.vue';
import ProfileView from '../views/ProfileView.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true }
  },
  {
    path: '/',
    name: 'route',
    component: RouteView,
    meta: { requiresAuth: true }
  },
  {
    path: '/stops/:id',
    name: 'stop-detail',
    component: StopDetailView,
    meta: { requiresAuth: true }
  },
  {
    path: '/stops/:id/pickup',
    name: 'pickup',
    component: PickupView,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHashHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'route' });
  } else {
    next();
  }
});

export default router;
