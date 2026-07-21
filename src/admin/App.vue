<template>
  <div id="wpask-app-root">
    <!-- Top Header -->
    <header class="wpask-app-header">
      <div class="wpask-app-header-left">
        <!-- Logo -->
        <div class="wpask-logo">
          <div class="wpask-logo-icon">
            <MessageSquare />
          </div>
          <div style="display:flex; align-items: baseline; gap: 8px;">
            <span class="wpask-logo-text">WPAsk</span>
            <span class="wpask-logo-badge">Pro</span>
          </div>
        </div>

        <!-- Nav Tabs -->
        <nav class="wpask-nav-tabs">
          <router-link to="/" class="wpask-nav-tab" exact-active-class="active">
            <ClipboardList /> Surveys
          </router-link>
          <router-link to="/results" class="wpask-nav-tab" active-class="active">
            <BarChart3 /> Results
          </router-link>
          <router-link to="/heatmaps" class="wpask-nav-tab" active-class="active">
            <MousePointerClick /> Heatmaps
          </router-link>
          <router-link to="/settings" class="wpask-nav-tab" active-class="active">
            <SettingsIcon /> Settings
          </router-link>
          <router-link to="/addons" class="wpask-nav-tab" active-class="active">
            <Puzzle /> Add-ons
          </router-link>
        </nav>
      </div>

      <div class="wpask-app-header-right">
        <a href="https://wpask.io/docs" target="_blank" class="wpask-btn wpask-btn-secondary wpask-btn-sm">
          <BookOpen />
          Docs
        </a>
        <button type="button" class="wpask-btn wpask-btn-primary wpask-btn-sm" @click="showTemplatePicker = true">
          <Plus />
          New survey
        </button>
      </div>
    </header>

    <!-- Router View -->
    <div class="wpask-page-content">
      <router-view></router-view>
    </div>

    <TemplatePickerModal v-model:visible="showTemplatePicker" />
  </div>
</template>

<script setup>
import { ref, provide, watch } from 'vue';
import { useRoute } from 'vue-router';
import {
  MessageSquare,
  ClipboardList,
  BarChart3,
  Settings as SettingsIcon,
  Puzzle,
  MousePointerClick,
  BookOpen,
  Plus,
} from 'lucide-vue-next';
import TemplatePickerModal from './components/TemplatePickerModal.vue';

const route = useRoute();
const showTemplatePicker = ref(false);

provide('openTemplatePicker', () => {
  showTemplatePicker.value = true;
});

watch(
  () => route.query.new,
  (value) => {
    if (value === '1') {
      showTemplatePicker.value = true;
    }
  },
  { immediate: true }
);
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700&display=swap');
@import './admin.css';

/* Kill WP Admin CSS interference */
#wpcontent { padding-left: 0 !important; }
#wpbody-content { padding-bottom: 0 !important; }
#wpbody-content .wrap { margin: 0 !important; padding: 0 !important; }
#wpask-admin-app { margin: 0 !important; }
</style>
