<template>
  <div>
    <div class="wpask-page-title-bar">
      <h1 class="wpask-page-title">All Surveys</h1>
      <router-link to="/surveys/new" class="wpask-btn wpask-btn-primary">
        ✨ Create New Survey
      </router-link>
    </div>

    <div class="wpask-content-inner">
      <!-- Stats Row -->
      <div class="wpask-stats-grid">
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon indigo">📋</div>
          <div>
            <div class="wpask-stat-value">{{ counts.all || 0 }}</div>
            <div class="wpask-stat-label">Total Surveys</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon green">✅</div>
          <div>
            <div class="wpask-stat-value">{{ counts.publish || 0 }}</div>
            <div class="wpask-stat-label">Published</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon blue">📝</div>
          <div>
            <div class="wpask-stat-value">{{ counts.draft || 0 }}</div>
            <div class="wpask-stat-label">Drafts</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon amber">🗑️</div>
          <div>
            <div class="wpask-stat-value">{{ counts.trash || 0 }}</div>
            <div class="wpask-stat-label">Trashed</div>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="wpask-card">
        <!-- Status Tabs + Search -->
        <div class="wpask-list-toolbar">
          <div class="wpask-status-tabs">
            <button
              v-for="tab in tabs"
              :key="tab.value"
              class="wpask-status-tab"
              :class="{ active: activeTab === tab.value }"
              @click="changeTab(tab.value)"
            >
              {{ tab.label }}
              <span class="wpask-tab-badge">{{ counts[tab.value] || 0 }}</span>
            </button>
          </div>

          <div style="display:flex; gap:10px; align-items:center;">
            <!-- Bulk actions (shown when items are selected) -->
            <template v-if="selected.length > 0">
              <span style="font-size:13px; color:#6b7280;">{{ selected.length }} selected</span>
              <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="bulkAction('publish')">Publish</button>
              <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="bulkAction('draft')">Draft</button>
              <button class="wpask-btn wpask-btn-danger wpask-btn-sm" @click="bulkAction('trash')">Trash</button>
              <button v-if="activeTab === 'trash'" class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="bulkAction('draft')">Restore All</button>
            </template>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search surveys..."
              class="wpask-search-input"
            />
          </div>
        </div>

        <!-- Table -->
        <table class="wpask-survey-table" v-if="filteredSurveys.length > 0">
          <thead>
            <tr>
              <th style="width:36px;">
                <input type="checkbox" @change="toggleAll" :checked="isAllSelected" />
              </th>
              <th>Survey Name</th>
              <th>Status</th>
              <th>Responses</th>
              <th>Views</th>
              <th>Completion</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="survey in filteredSurveys" :key="survey.id" :class="{ 'row-selected': selected.includes(survey.id) }">
              <td>
                <input type="checkbox" :value="survey.id" v-model="selected" />
              </td>
              <td>
                <span class="wpask-survey-name">{{ survey.title }}</span>
                <div style="font-size:12px; color:#9ca3af; margin-top:3px; text-transform:capitalize;">{{ survey.type }}</div>
              </td>
              <td>
                <span class="wpask-status-badge" :class="survey.status">
                  {{ statusLabel(survey.status) }}
                </span>
              </td>
              <td>{{ survey.responses || 0 }}</td>
              <td>{{ survey.impressions || 0 }}</td>
              <td>
                {{ survey.impressions > 0 ? Math.round(((survey.responses || 0) / survey.impressions) * 100) : 0 }}%
              </td>
              <td>{{ formatDate(survey.created_at) }}</td>
              <td>
                <div class="wpask-table-actions">
                  <!-- Active surveys -->
                  <template v-if="survey.status !== 'trash'">
                    <router-link :to="`/surveys/${survey.id}/edit`" class="wpask-btn wpask-btn-secondary wpask-btn-sm">Edit</router-link>
                    <router-link :to="`/surveys/${survey.id}/results`" class="wpask-btn wpask-btn-secondary wpask-btn-sm">Results</router-link>
                    <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="duplicateSurvey(survey.id)" title="Duplicate">⧉</button>
                    <button class="wpask-btn wpask-btn-danger wpask-btn-sm" @click="trashSurvey(survey.id)">Trash</button>
                  </template>
                  <!-- Trashed surveys -->
                  <template v-else>
                    <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="restoreSurvey(survey.id)">↩ Restore</button>
                    <button class="wpask-btn wpask-btn-danger wpask-btn-sm" @click="deleteSurvey(survey.id)">Delete Permanently</button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div class="wpask-empty-state" v-else>
          <span class="wpask-empty-icon">{{ activeTab === 'trash' ? '🗑️' : '📝' }}</span>
          <h3 class="wpask-empty-title">{{ activeTab === 'trash' ? 'Trash is empty' : 'Create your first survey' }}</h3>
          <p class="wpask-empty-desc">{{ activeTab === 'trash' ? 'No trashed surveys found.' : 'Start collecting feedback in minutes.' }}</p>
          <router-link v-if="activeTab !== 'trash'" to="/surveys/new" class="wpask-btn wpask-btn-primary">
            ✨ Create New Survey
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

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

const isAllSelected = computed(
  () => surveys.value.length > 0 && selected.value.length === surveys.value.length
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

const toggleAll = (e) => {
  selected.value = e.target.checked ? surveys.value.map(s => s.id) : [];
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
    alert('Survey duplicated successfully!');
  }
};

const bulkAction = async (status) => {
  if (selected.value.length === 0) return;
  const action = status === 'trash' ? 'Trash' : status === 'draft' ? 'Restore to draft' : 'Publish';
  if (!confirm(`${action} ${selected.value.length} surveys?`)) return;

  const res = await apiCall('surveys/bulk-status', 'POST', { ids: selected.value, status });
  if (res.ok) loadSurveys();
};

onMounted(loadSurveys);
</script>

<style scoped>
.wpask-list-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-bottom: 1px solid #f0f0f5;
  flex-wrap: wrap;
  gap: 10px;
}

.wpask-status-tabs {
  display: flex;
  gap: 4px;
}

.wpask-status-tab {
  background: none;
  border: none;
  padding: 6px 12px;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s;
  font-family: inherit;
}

.wpask-status-tab:hover { background: #f3f4f6; color: #374151; }

.wpask-status-tab.active {
  background: #eef2ff;
  color: #4f46e5;
  font-weight: 600;
}

.wpask-tab-badge {
  background: #e5e7eb;
  color: #6b7280;
  font-size: 11px;
  font-weight: 600;
  padding: 1px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.wpask-status-tab.active .wpask-tab-badge {
  background: #c7d2fe;
  color: #4338ca;
}

.wpask-search-input {
  border: 1px solid #d1d5db;
  border-radius: 7px;
  padding: 7px 12px;
  font-size: 13px;
  outline: none;
  width: 200px;
  color: #374151;
  font-family: inherit;
}

.wpask-search-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99,102,241,0.1);
}

.wpask-status-badge.trash {
  background: #fef2f2;
  color: #dc2626;
}

tr.row-selected td {
  background: #fafafa;
}

.wpask-table-actions {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
}
</style>
