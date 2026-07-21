<template>
  <div>
    <div class="wpask-page-title-bar">
      <h1 class="wpask-page-title">🔌 Addons</h1>
    </div>

    <div class="wpask-content-inner">
      <div class="addons-grid">
        <div class="addon-card" v-for="addon in addons" :key="addon.id">
          <div class="addon-icon">{{ addon.icon }}</div>
          <div class="addon-body">
            <h3 class="addon-title">{{ addon.name }}</h3>
            <p class="addon-desc">{{ addon.description }}</p>
          </div>
          <div class="addon-footer">
            <span class="addon-badge pro">Pro</span>
            <a href="https://wpask.io" target="_blank" class="wpask-btn wpask-btn-primary wpask-btn-sm">
              Upgrade to Unlock
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const addons = [
  { id: 'email', icon: '📧', name: 'Email Notifications', description: 'Get notified by email whenever a new response comes in.' },
  { id: 'webhooks', icon: '🔗', name: 'Webhooks', description: 'Send survey responses to Zapier, Make, or any webhook URL.' },
  { id: 'conditional', icon: '🎯', name: 'Conditional Logic', description: 'Show or hide questions based on previous answers.' },
  { id: 'gutenberg', icon: '📄', name: 'Gutenberg Block', description: 'Embed surveys directly into posts and pages via the block editor.' },
  { id: 'heatmap', icon: '🔥', name: 'Heatmaps', description: 'Visualize where your visitors click and how they interact.' },
  { id: 'export', icon: '📤', name: 'Export Responses', description: 'Export all survey responses as CSV or Excel files.' },
];
</script>

<style scoped>
.addons-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.addon-card {
  background: #fff;
  border: 1px solid #e9eaf0;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: box-shadow 0.2s;
}

.addon-card:hover {
  box-shadow: 0 4px 20px rgba(0,0,0,0.07);
}

.addon-icon { font-size: 34px; }

.addon-title {
  font-size: 16px;
  font-weight: 700;
  color: #1a1d2b;
  margin-bottom: 6px !important;
}

.addon-desc {
  font-size: 13px;
  color: #6b7280;
  line-height: 1.5;
}

.addon-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #f0f0f5;
}

.addon-badge.pro {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 20px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}
</style>
