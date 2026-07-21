<template>
  <div class="wpask-admin">
    <header class="wpask-header">
      <div class="logo">
        <span class="dashicons dashicons-feedback"></span>
        <h2>WPAsk</h2>
      </div>
      <nav class="wpask-nav">
        <router-link to="/">Surveys</router-link>
        <router-link to="/settings">Settings</router-link>
      </nav>
    </header>

    <main class="wpask-main">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
// Main App Component
</script>

<style scoped>
.wpask-admin {
  background: #f0f0f1;
  min-height: 100vh;
  margin-left: -20px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

.wpask-header {
  background: white;
  padding: 0 30px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #c3c4c7;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #4F46E5;
}

.logo h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
}

.wpask-nav {
  display: flex;
  gap: 20px;
}

.wpask-nav a {
  text-decoration: none;
  color: #50575e;
  font-weight: 500;
  padding: 5px 0;
}

.wpask-nav a.router-link-active {
  color: #4F46E5;
  border-bottom: 2px solid #4F46E5;
}

.wpask-main {
  padding: 30px;
  max-width: 1200px;
  margin: 0 auto;
}
</style>
