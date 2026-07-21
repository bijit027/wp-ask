import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    name: 'SurveysList',
    component: () => import('./views/SurveysList.vue')
  },
  {
    path: '/surveys/new',
    name: 'SurveyBuilderNew',
    component: () => import('./views/SurveyBuilder.vue')
  },
  {
    path: '/surveys/:id/edit',
    name: 'SurveyBuilderEdit',
    component: () => import('./views/SurveyBuilder.vue')
  },
  {
    path: '/surveys/:id/results',
    name: 'SurveyResults',
    component: () => import('./views/SurveyResults.vue')
  },
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('./views/Settings.vue')
  },
  {
    path: '/onboarding',
    name: 'Onboarding',
    component: () => import('./views/Onboarding.vue')
  }
];

const router = createRouter({
  history: createWebHashHistory(),
  routes
});

export default router;
