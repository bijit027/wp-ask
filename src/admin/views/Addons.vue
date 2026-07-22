<template>
  <div class="wpask-content-inner">
    <!-- Page Header -->
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">Add-ons</h1>
        <p class="wpask-page-subtitle">Extend WPAsk with integrations and advanced features.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="wpask-addons-loading">Loading add-ons…</div>

    <!-- Addons Grid -->
    <div v-else class="wpask-addons-grid">
      <div
        v-for="addon in addons"
        :key="addon.id"
        class="wpask-addon-card"
      >
        <div class="wpask-addon-card-top">
          <div class="wpask-addon-icon">
            <component :is="iconFor(addon.icon)" />
          </div>
          <span v-if="addon.tier === 'pro'" class="wpask-addon-pro-badge">Pro</span>
        </div>

        <div class="wpask-addon-card-inner">
          <div style="min-width:0;">
            <div class="wpask-addon-name">{{ addon.name }}</div>
            <p class="wpask-addon-desc">{{ addon.description }}</p>
          </div>
          <div class="wpask-addon-action" style="flex-shrink:0;">
            <span class="wpask-installed-badge" v-if="addon.active">
              <CircleCheck />
              Active
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {
  CircleCheck,
  Mail,
  GitBranch,
  Download,
  MousePointerClick,
  Star,
  Puzzle,
} from 'lucide-vue-next';

const loading = ref(true);
const addons = ref([]);

const iconMap = {
  mail: Mail,
  'git-branch': GitBranch,
  download: Download,
  'mouse-pointer-click': MousePointerClick,
  star: Star,
};

function iconFor(name) {
  return iconMap[name] || Puzzle;
}

async function fetchAddons() {
  const config = window.WPAskAdminConfig || {};

  try {
    const res = await fetch(`${config.api_url}/addons`, {
      headers: { 'X-WP-Nonce': config.nonce },
    });

    if (!res.ok) {
      throw new Error('Failed to load add-ons');
    }

    const data = await res.json();
    isPro.value = !!data.is_pro;
    upgradeUrl.value = data.upgrade_url || upgradeUrl.value;
    addons.value = data.addons || [];
  } catch (err) {
    console.error(err);
    addons.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(fetchAddons);
</script>
