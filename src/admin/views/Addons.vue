<template>
  <div class="wpask-content-inner">
    <!-- Page Header -->
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">Add-ons</h1>
        <p class="wpask-page-subtitle">Extend WPAsk with integrations.</p>
      </div>
    </div>

    <!-- Addons Grid -->
    <div class="wpask-addons-grid">
      <div class="wpask-addon-card" v-for="addon in addons" :key="addon.name">
        <div class="wpask-addon-card-inner">
          <div style="min-width:0;">
            <div class="wpask-addon-name">{{ addon.name }}</div>
            <p class="wpask-addon-desc">{{ addon.desc }}</p>
          </div>
          <div style="flex-shrink:0;">
            <span class="wpask-installed-badge" v-if="addon.installed">
              <Circle />
              Installed
            </span>
            <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" v-else>
              Install
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Circle } from 'lucide-vue-next';

const addons = [
  { name: "Email notifications", desc: "Get pinged when a response comes in.", installed: true },
  { name: "Mailchimp sync", desc: "Push new contacts into an audience.", installed: false },
  { name: "Zapier", desc: "Send data to 5,000+ apps via zaps.", installed: false },
  { name: "Webhooks", desc: "Post responses to a custom URL.", installed: true },
  { name: "Conditional logic", desc: "Show or hide questions based on previous answers.", installed: false },
  { name: "Heatmaps", desc: "Visualize where your visitors click and how they interact.", installed: false },
];
</script>
