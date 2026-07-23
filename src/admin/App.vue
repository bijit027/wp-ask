<template>
  <div id="pollquest-app-root">
    <!-- Top Header -->
    <header class="pollquest-app-header">
      <div class="pollquest-app-header-left">
        <!-- Logo -->
        <div class="pollquest-logo">
          <div class="pollquest-logo-icon">
            <MessageSquare />
          </div>
          <div style="display:flex; align-items: baseline; gap: 8px;">
            <span class="pollquest-logo-text">PollQuest</span>
            <span class="pollquest-logo-badge">Pro</span>
          </div>
        </div>

        <!-- Nav Tabs -->
        <nav class="pollquest-nav-tabs">
          <router-link to="/" class="pollquest-nav-tab" exact-active-class="active">
            <ClipboardList /> Surveys
          </router-link>
          <router-link to="/results" class="pollquest-nav-tab" active-class="active">
            <BarChart3 /> Results
          </router-link>
          <router-link to="/heatmaps" class="pollquest-nav-tab" active-class="active">
            <MousePointerClick /> Heatmaps
          </router-link>
          <router-link to="/settings" class="pollquest-nav-tab" active-class="active">
            <SettingsIcon /> Settings
          </router-link>
          <router-link to="/addons" class="pollquest-nav-tab" active-class="active">
            <Puzzle /> Add-ons
          </router-link>
        </nav>
      </div>

      <div class="pollquest-app-header-right">
        <a href="https://pollquest.io/docs" target="_blank" class="pollquest-btn pollquest-btn-secondary pollquest-btn-sm">
          <BookOpen />
          Docs
        </a>
        <button type="button" class="pollquest-btn pollquest-btn-primary pollquest-btn-sm" @click="showTemplatePicker = true">
          <Plus />
          New survey
        </button>
      </div>
    </header>

    <!-- Router View -->
    <div class="pollquest-page-content">
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
#pollquest-admin-app { margin: 0 !important; }
</style>
