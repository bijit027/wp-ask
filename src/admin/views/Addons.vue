<template>
  <div class="pollquest-content-inner">
    <!-- Page Header -->
    <div class="pollquest-page-header">
      <div>
        <h1 class="pollquest-page-title">Add-ons</h1>
        <p class="pollquest-page-subtitle">Extend PollQuest with integrations and advanced features.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="pollquest-addons-loading">Loading add-ons…</div>

    <!-- Addons Grid -->
    <div v-else class="pollquest-addons-grid">
      <div
        v-for="addon in addons"
        :key="addon.id"
        class="pollquest-addon-card"
      >
        <div class="pollquest-addon-card-top">
          <div class="pollquest-addon-icon">
            <component :is="iconFor(addon.icon)" />
          </div>
          <span v-if="addon.tier === 'pro'" class="pollquest-addon-pro-badge">Pro</span>
        </div>

        <div class="pollquest-addon-card-inner">
          <div style="min-width:0;">
            <div class="pollquest-addon-name">{{ addon.name }}</div>
            <p class="pollquest-addon-desc">{{ addon.description }}</p>
          </div>
          <div class="pollquest-addon-action" style="flex-shrink:0;">
            <span class="pollquest-installed-badge" v-if="addon.active">
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
  const config = window.PollQuestAdminConfig || {};

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
