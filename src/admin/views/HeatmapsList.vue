<template>
  <div class="pollquest-content-inner">
    <div class="pollquest-page-header">
      <div>
        <h1 class="pollquest-page-title">Heatmaps</h1>
        <p class="pollquest-page-subtitle">Track where visitors click on your pages.</p>
      </div>
      <div>
        <button type="button" class="pollquest-btn pollquest-btn-primary" @click="showCreate = true">
          <Plus />
          New heatmap
        </button>
      </div>
    </div>

    <div class="pollquest-stats-grid" style="grid-template-columns: repeat(3, 1fr);">
      <div class="pollquest-stat-card">
        <div class="pollquest-stat-label">Active</div>
        <div class="pollquest-stat-value">{{ activeCount }}</div>
      </div>
      <div class="pollquest-stat-card">
        <div class="pollquest-stat-label">Total clicks</div>
        <div class="pollquest-stat-value">{{ totalClicks }}</div>
      </div>
      <div class="pollquest-stat-card">
        <div class="pollquest-stat-label">Pages tracked</div>
        <div class="pollquest-stat-value">{{ heatmaps.length }}</div>
      </div>
    </div>

    <div class="pollquest-surveys-panel">
      <div class="pollquest-table-header" style="grid-template-columns: minmax(0,2fr) 100px 100px 120px auto;">
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
          class="pollquest-survey-row"
          style="grid-template-columns: minmax(0,2fr) 100px 100px 120px auto;"
        >
          <div style="min-width:0;">
            <div class="pollquest-survey-name truncate">{{ item.page_title }}</div>
            <a :href="item.page_url" target="_blank" rel="noopener" class="pollquest-survey-type">{{ item.page_url }}</a>
          </div>
          <div class="pollquest-status-dot-wrap">
            <span class="pollquest-ping-wrap">
              <span class="pollquest-ping-ring" :class="item.status !== 'publish' ? 'draft' : ''" />
              <span class="pollquest-ping-dot" :class="item.status !== 'publish' ? 'draft' : ''" />
            </span>
            <span class="pollquest-status-text">{{ statusLabel(item.status) }}</span>
          </div>
          <div class="pollquest-stat-cell right">{{ item.click_count || 0 }}</div>
          <div class="pollquest-stat-cell muted">{{ item.session_count || 0 }}</div>
          <div class="pollquest-survey-actions">
            <router-link :to="`/heatmaps/${item.id}`">
              <button class="pollquest-results-btn">View</button>
            </router-link>
            <button
              class="pollquest-icon-btn"
              :title="item.status === 'publish' ? 'Pause' : 'Activate'"
              @click="toggleStatus(item)"
            >
              <Pause v-if="item.status === 'publish'" />
              <Play v-else />
            </button>
            <button class="pollquest-icon-btn danger" title="Delete" @click="deleteHeatmap(item.id)">
              <Trash2 />
            </button>
          </div>
        </li>
      </ul>

      <div v-else class="pollquest-empty-row">
        No heatmaps yet. Create one to start tracking clicks on a page.
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showCreate" class="pollquest-modal-overlay" @click.self="showCreate = false">
        <div class="pollquest-modal" role="dialog" aria-modal="true">
          <div class="pollquest-modal-header">
            <div>
              <h2 class="pollquest-modal-title">Create heatmap</h2>
              <p class="pollquest-modal-subtitle">Select a page to start tracking visitor clicks.</p>
            </div>
            <button type="button" class="pollquest-icon-btn" @click="showCreate = false">
              <X />
            </button>
          </div>
          <div class="pollquest-modal-body">
            <div class="pollquest-field-label">Page</div>
            <select v-model="selectedPageId" class="pollquest-select" style="width:100%; margin-top:8px;">
              <option value="">Select a page…</option>
              <option v-for="page in pages" :key="page.id" :value="page.id">
                {{ page.title }} ({{ page.type }})
              </option>
            </select>
          </div>
          <div class="pollquest-modal-footer">
            <button type="button" class="pollquest-btn pollquest-btn-secondary" @click="showCreate = false">Cancel</button>
            <button
              type="button"
              class="pollquest-btn pollquest-btn-primary"
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
  const config = window.PollQuestAdminConfig || {};
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
  const config = window.PollQuestAdminConfig || {};
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
