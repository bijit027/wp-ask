<template>
  <div class="wpask-content-inner">
    <!-- Page Header -->
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">Add-ons</h1>
        <p class="wpask-page-subtitle">Extend WPAsk with integrations and advanced features.</p>
      </div>
      <div v-if="!isPro">
        <a
          :href="upgradeUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="wpask-btn wpask-btn-primary"
        >
          <Sparkles />
          Upgrade to Pro
        </a>
      </div>
    </div>

    <!-- Lite upgrade banner -->
    <div v-if="!isPro" class="wpask-addons-upgrade-banner">
      <div class="wpask-addons-upgrade-banner-icon">
        <Lock />
      </div>
      <div class="wpask-addons-upgrade-banner-body">
        <strong>You're using WPAsk Lite.</strong>
        Unlock heatmaps, webhooks, integrations, and more with
        <a :href="upgradeUrl" target="_blank" rel="noopener noreferrer">WPAsk Pro</a>.
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
        :class="{ 'wpask-addon-card--locked': addon.locked, 'wpask-addon-card--pro': addon.tier === 'pro' }"
      >
        <div class="wpask-addon-card-top">
          <div class="wpask-addon-icon" :class="{ 'wpask-addon-icon--locked': addon.locked }">
            <component :is="iconFor(addon.icon)" />
            <span v-if="addon.locked" class="wpask-addon-lock-badge">
              <Lock />
            </span>
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
            <span class="wpask-included-badge" v-else-if="addon.installed && !addon.locked">
              <CircleCheck />
              Included
            </span>
            <a
              v-else-if="addon.locked"
              :href="addon.upgrade_url || upgradeUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="wpask-btn wpask-btn-secondary wpask-btn-sm wpask-addon-upgrade-btn"
            >
              <Lock />
              Get Pro
            </a>
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
  Lock,
  Sparkles,
  Mail,
  GitBranch,
  Download,
  MousePointerClick,
  Webhook,
  Send,
  Zap,
  Star,
  Puzzle,
} from 'lucide-vue-next';

const loading = ref(true);
const addons = ref([]);
const isPro = ref(false);
const upgradeUrl = ref('https://wpask.io/pricing');

const iconMap = {
  mail: Mail,
  'git-branch': GitBranch,
  download: Download,
  'mouse-pointer-click': MousePointerClick,
  webhook: Webhook,
  send: Send,
  zap: Zap,
  star: Star,
};

function iconFor(name) {
  return iconMap[name] || Puzzle;
}

async function fetchAddons() {
  const config = window.WPAskAdminConfig || {};
  isPro.value = !!config.is_pro;
  upgradeUrl.value = config.upgrade_url || upgradeUrl.value;

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
