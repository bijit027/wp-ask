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
        <router-link to="/surveys/new" class="wpask-btn wpask-btn-primary wpask-btn-sm">
          <Plus />
          New survey
        </router-link>
      </div>
    </header>

    <!-- Router View -->
    <div class="wpask-page-content">
      <router-view></router-view>
    </div>
  </div>
</template>

<script setup>
import {
  MessageSquare,
  ClipboardList,
  BarChart3,
  Settings as SettingsIcon,
  Puzzle,
  BookOpen,
  Plus,
} from 'lucide-vue-next';
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
