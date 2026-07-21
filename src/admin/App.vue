<template>
  <div id="wpask-app-root">
    <!-- ====== Top Header (like UserFeedback Lite) ====== -->
    <header class="wpask-app-header">
      <div class="wpask-app-header-left">
        <!-- Logo -->
        <div class="wpask-logo">
          <div class="wpask-logo-icon">💬</div>
          <span class="wpask-logo-text">WPAsk</span>
          <span class="wpask-logo-badge">Pro</span>
        </div>

        <!-- Nav Tabs -->
        <nav class="wpask-nav-tabs">
          <router-link to="/" class="wpask-nav-tab" exact-active-class="active">
            <span class="tab-icon">📋</span> Surveys
          </router-link>
          <router-link to="/results" class="wpask-nav-tab" active-class="active">
            <span class="tab-icon">📊</span> Results
          </router-link>
          <router-link to="/settings" class="wpask-nav-tab" active-class="active">
            <span class="tab-icon">⚙️</span> Settings
          </router-link>
          <router-link to="/addons" class="wpask-nav-tab" active-class="active">
            <span class="tab-icon">🔌</span>
            <span style="color: #8b5cf6; font-weight: 700;">Addons</span>
          </router-link>
        </nav>
      </div>

      <div class="wpask-app-header-right">
        <a href="https://wpask.io/docs" target="_blank" class="wpask-btn wpask-btn-secondary wpask-btn-sm">
          📖 Docs
        </a>
        <router-link to="/surveys/new" class="wpask-btn wpask-btn-primary wpask-btn-sm">
          ✨ New Survey
        </router-link>
      </div>
    </header>

    <!-- ====== Router View ====== -->
    <div class="wpask-page-content">
      <router-view></router-view>
    </div>
  </div>
</template>

<script setup>
// Root App Shell
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
@import './admin.css';

/* Kill WP Admin CSS interference */
#wpcontent { padding-left: 0 !important; }
#wpbody-content { padding-bottom: 0 !important; }
#wpbody-content .wrap { margin: 0 !important; padding: 0 !important; }
#wpask-admin-app { margin: 0 !important; }
</style>
