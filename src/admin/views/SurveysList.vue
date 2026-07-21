<template>
  <div class="wpask-content-inner">
    <!-- Page Header -->
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">Surveys</h1>
        <p class="wpask-page-subtitle">Create, publish and manage the surveys running on your site.</p>
      </div>
      <div>
        <button type="button" class="wpask-btn wpask-btn-primary" @click="openTemplatePicker">
          <Plus />
          New survey
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="wpask-stats-grid">
      <div class="wpask-stat-card">
        <div class="wpask-stat-label">Total</div>
        <div class="wpask-stat-value">{{ counts.all || 0 }}</div>
      </div>
      <div class="wpask-stat-card">
        <div class="wpask-stat-label">Published</div>
        <div class="wpask-stat-value">{{ counts.publish || 0 }}</div>
      </div>
      <div class="wpask-stat-card">
        <div class="wpask-stat-label">Drafts</div>
        <div class="wpask-stat-value">{{ counts.draft || 0 }}</div>
      </div>
      <div class="wpask-stat-card">
        <div class="wpask-stat-label">Trashed</div>
        <div class="wpask-stat-value">{{ counts.trash || 0 }}</div>
      </div>
    </div>

    <!-- Table Panel -->
    <div class="wpask-surveys-panel">
      <!-- Toolbar -->
      <div class="wpask-panel-toolbar">
        <div class="wpask-filter-tabs">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            class="wpask-filter-tab"
            :class="{ active: activeTab === tab.value }"
            @click="changeTab(tab.value)"
          >
            {{ tab.label }}
          </button>
        </div>

        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
          <!-- Bulk actions -->
          <template v-if="selected.length > 0">
            <span style="font-size:13px; color:var(--muted-foreground);">{{ selected.length }} selected</span>
            <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="bulkAction('publish')">Publish</button>
            <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="bulkAction('draft')">Draft</button>
            <button class="wpask-btn wpask-btn-danger wpask-btn-sm" @click="bulkAction('trash')">Trash</button>
          </template>

          <!-- Search -->
          <div class="wpask-search-wrap">
            <Search class="wpask-search-icon" />
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search surveys"
              class="wpask-search-input"
            />
          </div>
        </div>
      </div>

      <!-- Column Headers -->
      <div class="wpask-table-header">
        <div>Name</div>
        <div>Status</div>
        <div style="text-align:right">Responses</div>
        <div style="text-align:right">Views</div>
        <div style="text-align:right">Completion</div>
        <div>Created</div>
        <div style="text-align:right">Actions</div>
      </div>

      <!-- Rows -->
      <ul v-if="filteredSurveys.length > 0">
        <li
          v-for="survey in filteredSurveys"
          :key="survey.id"
          class="wpask-survey-row"
        >
          <!-- Name -->
          <div style="min-width:0;">
            <div class="wpask-survey-name truncate">{{ survey.title }}</div>
            <div class="wpask-survey-type">{{ survey.type }}</div>
          </div>

          <!-- Status -->
          <div class="wpask-status-dot-wrap">
            <span class="wpask-ping-wrap">
              <span class="wpask-ping-ring" :class="survey.status !== 'publish' ? 'draft' : ''" />
              <span class="wpask-ping-dot" :class="survey.status !== 'publish' ? 'draft' : ''" />
            </span>
            <span class="wpask-status-text">{{ statusLabel(survey.status) }}</span>
          </div>

          <!-- Responses -->
          <div class="wpask-stat-cell right">{{ survey.responses || 0 }}</div>

          <!-- Views -->
          <div class="wpask-stat-cell right">{{ survey.impressions || 0 }}</div>

          <!-- Completion -->
          <div class="wpask-stat-cell right muted">
            {{ survey.impressions > 0 ? Math.round(((survey.responses || 0) / survey.impressions) * 100) : 0 }}%
          </div>

          <!-- Created -->
          <div class="wpask-stat-cell muted">{{ formatDate(survey.created_at) }}</div>

          <!-- Actions -->
          <div class="wpask-survey-actions">
            <template v-if="survey.status !== 'trash'">
              <router-link :to="`/surveys/${survey.id}/edit`">
                <button class="wpask-icon-btn" title="Edit">
                  <Pencil />
                </button>
              </router-link>
              <button class="wpask-results-btn" @click="$router.push(`/surveys/${survey.id}/results`)">
                Results
              </button>
              <button class="wpask-icon-btn" title="Duplicate" @click="duplicateSurvey(survey.id)">
                <Copy />
              </button>
              <button class="wpask-icon-btn danger" title="Trash" @click="trashSurvey(survey.id)">
                <Trash2 />
              </button>
              <button class="wpask-icon-btn" title="More">
                <MoreHorizontal />
              </button>
            </template>
            <template v-else>
              <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="restoreSurvey(survey.id)">Restore</button>
              <button class="wpask-btn wpask-btn-danger wpask-btn-sm" @click="deleteSurvey(survey.id)">Delete</button>
            </template>
          </div>
        </li>
      </ul>

      <!-- Empty state -->
      <div class="wpask-empty-row" v-else>
        {{ activeTab === 'trash' ? 'Trash is empty.' : 'No surveys match your search.' }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import { Search, Pencil, Copy, Trash2, MoreHorizontal, Plus } from 'lucide-vue-next';

const openTemplatePicker = inject('openTemplatePicker', () => {});

const surveys = ref([]);
const searchQuery = ref('');
const activeTab = ref('all');
const selected = ref([]);
const counts = ref({ all: 0, publish: 0, draft: 0, trash: 0 });

const tabs = [
  { label: 'All', value: 'all' },
  { label: 'Published', value: 'publish' },
  { label: 'Draft', value: 'draft' },
  { label: 'Trash', value: 'trash' },
];

const filteredSurveys = computed(() =>
  surveys.value.filter(s => s.title.toLowerCase().includes(searchQuery.value.toLowerCase()))
);

const statusLabel = (s) => ({ publish: 'Active', draft: 'Draft', trash: 'Trashed' }[s] || s);

const formatDate = (d) => {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const apiCall = async (path, method = 'POST', body = null) => {
  const config = window.WPAskAdminConfig || {};
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce }
  };
  if (body) opts.body = JSON.stringify(body);
  return fetch(`${config.api_url}/${path}`, opts);
};

const loadSurveys = async () => {
  const config = window.WPAskAdminConfig || {};
  const res = await fetch(`${config.api_url}/surveys?status=${activeTab.value}`, {
    headers: { 'X-WP-Nonce': config.nonce }
  });
  if (res.ok) {
    surveys.value = await res.json();
    const rawCounts = res.headers.get('X-WP-StatusCounts');
    if (rawCounts) counts.value = JSON.parse(rawCounts);
  }
  selected.value = [];
};

const changeTab = (tab) => {
  activeTab.value = tab;
  loadSurveys();
};

const trashSurvey = async (id) => {
  if (!confirm('Move this survey to trash?')) return;
  const res = await apiCall(`surveys/${id}/trash`);
  if (res.ok) loadSurveys();
};

const restoreSurvey = async (id) => {
  const res = await apiCall(`surveys/${id}/restore`);
  if (res.ok) loadSurveys();
};

const deleteSurvey = async (id) => {
  if (!confirm('Permanently delete this survey? This cannot be undone.')) return;
  const res = await apiCall(`surveys/${id}`, 'DELETE');
  if (res.ok) loadSurveys();
};

const duplicateSurvey = async (id) => {
  const res = await apiCall(`surveys/${id}/duplicate`);
  if (res.ok) {
    await loadSurveys();
  }
};

const bulkAction = async (status) => {
  if (selected.value.length === 0) return;
  const res = await apiCall('surveys/bulk-status', 'POST', { ids: selected.value, status });
  if (res.ok) loadSurveys();
};

onMounted(loadSurveys);
</script>
