<template>
  <div class="wpask-content-inner">
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">Heatmaps</h1>
        <p class="wpask-page-subtitle">Track where visitors click on your pages.</p>
      </div>
      <div>
        <button type="button" class="wpask-btn wpask-btn-primary" @click="showCreate = true">
          <Plus />
          New heatmap
        </button>
      </div>
    </div>

    <div class="wpask-stats-grid" style="grid-template-columns: repeat(3, 1fr);">
      <div class="wpask-stat-card">
        <div class="wpask-stat-label">Active</div>
        <div class="wpask-stat-value">{{ activeCount }}</div>
      </div>
      <div class="wpask-stat-card">
        <div class="wpask-stat-label">Total clicks</div>
        <div class="wpask-stat-value">{{ totalClicks }}</div>
      </div>
      <div class="wpask-stat-card">
        <div class="wpask-stat-label">Pages tracked</div>
        <div class="wpask-stat-value">{{ heatmaps.length }}</div>
      </div>
    </div>

    <div class="wpask-surveys-panel">
      <div class="wpask-table-header" style="grid-template-columns: minmax(0,2fr) 100px 100px 120px auto;">
        <div>Page</div>
        <div>Status</div>
        <div style="text-align:right">Clicks</div>
        <div>Sessions</div>
        <div style="text-align:right">Actions</div>
      </div>

      <ul v-if="heatmaps.length">
        <li
          v-for="item in heatmaps"
          :key="item.id"
          class="wpask-survey-row"
          style="grid-template-columns: minmax(0,2fr) 100px 100px 120px auto;"
        >
          <div style="min-width:0;">
            <div class="wpask-survey-name truncate">{{ item.page_title }}</div>
            <a :href="item.page_url" target="_blank" rel="noopener" class="wpask-survey-type">{{ item.page_url }}</a>
          </div>
          <div class="wpask-status-dot-wrap">
            <span class="wpask-ping-wrap">
              <span class="wpask-ping-ring" :class="item.status !== 'publish' ? 'draft' : ''" />
              <span class="wpask-ping-dot" :class="item.status !== 'publish' ? 'draft' : ''" />
            </span>
            <span class="wpask-status-text">{{ statusLabel(item.status) }}</span>
          </div>
          <div class="wpask-stat-cell right">{{ item.click_count || 0 }}</div>
          <div class="wpask-stat-cell muted">{{ item.session_count || 0 }}</div>
          <div class="wpask-survey-actions">
            <router-link :to="`/heatmaps/${item.id}`">
              <button class="wpask-results-btn">View</button>
            </router-link>
            <button
              class="wpask-icon-btn"
              :title="item.status === 'publish' ? 'Pause' : 'Activate'"
              @click="toggleStatus(item)"
            >
              <Pause v-if="item.status === 'publish'" />
              <Play v-else />
            </button>
            <button class="wpask-icon-btn danger" title="Delete" @click="deleteHeatmap(item.id)">
              <Trash2 />
            </button>
          </div>
        </li>
      </ul>

      <div v-else class="wpask-empty-row">
        No heatmaps yet. Create one to start tracking clicks on a page.
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showCreate" class="wpask-modal-overlay" @click.self="showCreate = false">
        <div class="wpask-modal" role="dialog" aria-modal="true">
          <div class="wpask-modal-header">
            <div>
              <h2 class="wpask-modal-title">Create heatmap</h2>
              <p class="wpask-modal-subtitle">Select a page to start tracking visitor clicks.</p>
            </div>
            <button type="button" class="wpask-icon-btn" @click="showCreate = false">
              <X />
            </button>
          </div>
          <div class="wpask-modal-body">
            <div class="wpask-field-label">Page</div>
            <select v-model="selectedPageId" class="wpask-select" style="width:100%; margin-top:8px;">
              <option value="">Select a page…</option>
              <option v-for="page in pages" :key="page.id" :value="page.id">
                {{ page.title }} ({{ page.type }})
              </option>
            </select>
          </div>
          <div class="wpask-modal-footer">
            <button type="button" class="wpask-btn wpask-btn-secondary" @click="showCreate = false">Cancel</button>
            <button
              type="button"
              class="wpask-btn wpask-btn-primary"
              :disabled="!selectedPageId || creating"
              @click="createHeatmap"
            >
              Create heatmap
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Plus, Pause, Play, Trash2, X } from 'lucide-vue-next';

const router = useRouter();
const heatmaps = ref([]);
const pages = ref([]);
const showCreate = ref(false);
const selectedPageId = ref('');
const creating = ref(false);

const activeCount = computed(() => heatmaps.value.filter(h => h.status === 'publish').length);
const totalClicks = computed(() => heatmaps.value.reduce((sum, h) => sum + (h.click_count || 0), 0));

const statusLabel = (s) => ({ publish: 'Active', draft: 'Paused', trash: 'Trashed' }[s] || s);

async function api(path, method = 'GET', body = null) {
  const config = window.WPAskAdminConfig || {};
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
  };
  if (body) opts.body = JSON.stringify(body);
  return fetch(`${config.api_url}/${path}`, opts);
}

async function loadHeatmaps() {
  const res = await api('heatmaps');
  if (res.ok) {
    heatmaps.value = await res.json();
  }
}

async function loadPages() {
  const config = window.WPAskAdminConfig || {};
  const res = await fetch(`${config.api_url}/pages?per_page=100`, {
    headers: { 'X-WP-Nonce': config.nonce },
  });
  if (res.ok) {
    const data = await res.json();
    pages.value = data.pages || [];
  }
}

async function createHeatmap() {
  if (!selectedPageId.value) return;
  creating.value = true;
  try {
    const res = await api('heatmaps', 'POST', { page_id: Number(selectedPageId.value) });
    if (res.ok) {
      const data = await res.json();
      showCreate.value = false;
      selectedPageId.value = '';
      router.push(`/heatmaps/${data.id}`);
      return;
    }
    const err = await res.json();
    alert(err.message || 'Could not create heatmap.');
  } finally {
    creating.value = false;
  }
}

async function toggleStatus(item) {
  const newStatus = item.status === 'publish' ? 'draft' : 'publish';
  const res = await api(`heatmaps/${item.id}`, 'PUT', { status: newStatus });
  if (res.ok) loadHeatmaps();
}

async function deleteHeatmap(id) {
  if (!confirm('Delete this heatmap and all click data?')) return;
  const res = await api(`heatmaps/${id}`, 'DELETE');
  if (res.ok) loadHeatmaps();
}

onMounted(async () => {
  await Promise.all([loadHeatmaps(), loadPages()]);
});
</script>
