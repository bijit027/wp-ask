<template>
  <div class="pollquest-content-inner">
    <div class="pollquest-page-header">
      <div>
        <button class="pollquest-editor-back-btn" @click="$router.push('/heatmaps')" aria-label="Back">
          <ArrowLeft />
        </button>
        <h1 class="pollquest-page-title" style="display:inline; margin-left:8px;">
          {{ heatmap.page_title || 'Heatmap' }}
        </h1>
        <p class="pollquest-page-subtitle">
          <a v-if="heatmap.page_url" :href="heatmap.page_url" target="_blank" rel="noopener">
            {{ heatmap.page_url }}
          </a>
        </p>
      </div>
      <div style="display:flex; gap:8px;">
        <button
          class="pollquest-btn pollquest-btn-secondary"
          @click="toggleStatus"
        >
          {{ heatmap.status === 'publish' ? 'Pause tracking' : 'Resume tracking' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="pollquest-addons-loading">Loading heatmap data…</div>

    <template v-else-if="heatmap.id">
      <div class="pollquest-stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="pollquest-stat-card">
          <div class="pollquest-stat-label">Total clicks</div>
          <div class="pollquest-stat-value">{{ heatmap.points?.total_clicks || 0 }}</div>
        </div>
        <div class="pollquest-stat-card">
          <div class="pollquest-stat-label">Sessions</div>
          <div class="pollquest-stat-value">{{ heatmap.session_count || 0 }}</div>
        </div>
        <div class="pollquest-stat-card">
          <div class="pollquest-stat-label">Status</div>
          <div class="pollquest-stat-value" style="font-size:18px;">{{ heatmap.status === 'publish' ? 'Active' : 'Paused' }}</div>
        </div>
      </div>

      <div class="pollquest-settings-section">
        <div>
          <h2 class="pollquest-settings-section-title">Click heatmap</h2>
          <p class="pollquest-settings-section-desc">
            Warmer areas show where visitors click most. Coordinates are normalized to page scroll height.
          </p>
        </div>

        <div class="pollquest-heatmap-canvas-wrap">
          <div class="pollquest-heatmap-canvas" ref="canvasRef">
            <div
              v-for="(cell, index) in gridCells"
              :key="index"
              class="pollquest-heatmap-cell"
              :style="cellStyle(cell)"
            />
            <div v-if="!gridCells.length" class="pollquest-heatmap-empty">
              No click data yet. Visit the page on the frontend to start collecting clicks.
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ArrowLeft } from 'lucide-vue-next';

const route = useRoute();
const loading = ref(true);
const heatmap = ref({});
const canvasRef = ref(null);

const gridCells = computed(() => heatmap.value.points?.grid || []);

function cellStyle(cell) {
  const max = heatmap.value.points?.max_count || 1;
  const intensity = cell.count / max;
  const size = 100 / (heatmap.value.points?.grid_size || 20);

  return {
    left: `${cell.x * 100}%`,
    top: `${cell.y * 100}%`,
    width: `${size}%`,
    height: `${size}%`,
    opacity: Math.max(0.15, intensity),
    background: `radial-gradient(circle, rgba(99,102,241,${Math.min(0.9, intensity)}) 0%, rgba(99,102,241,0) 70%)`,
  };
}

async function api(path, method = 'GET', body = null) {
  const config = window.PollQuestAdminConfig || {};
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
  };
  if (body) opts.body = JSON.stringify(body);
  return fetch(`${config.api_url}/${path}`, opts);
}

async function loadHeatmap() {
  loading.value = true;
  try {
    const res = await api(`heatmaps/${route.params.id}`);
    if (res.ok) {
      heatmap.value = await res.json();
    }
  } finally {
    loading.value = false;
  }
}

async function toggleStatus() {
  const newStatus = heatmap.value.status === 'publish' ? 'draft' : 'publish';
  const res = await api(`heatmaps/${heatmap.value.id}`, 'PUT', { status: newStatus });
  if (res.ok) {
    await loadHeatmap();
  }
}

onMounted(loadHeatmap);
</script>

<style scoped>
.pollquest-heatmap-canvas-wrap {
  margin-top: 16px;
}

.pollquest-heatmap-canvas {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 10;
  background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
}

.pollquest-heatmap-cell {
  position: absolute;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  pointer-events: none;
}

.pollquest-heatmap-empty {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  text-align: center;
  color: var(--muted-foreground);
  font-size: 14px;
}
</style>
