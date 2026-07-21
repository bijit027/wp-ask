<template>
  <div>
    <!-- Page Title Bar -->
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
            <div class="wpask-stat-value">{{ stats.total }}</div>
            <div class="wpask-stat-label">Total Surveys</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon blue">💬</div>
          <div>
            <div class="wpask-stat-value">{{ stats.responses }}</div>
            <div class="wpask-stat-label">Total Responses</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon green">👁️</div>
          <div>
            <div class="wpask-stat-value">{{ stats.views }}</div>
            <div class="wpask-stat-label">Total Views</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon amber">📈</div>
          <div>
            <div class="wpask-stat-value">{{ stats.completion }}%</div>
            <div class="wpask-stat-label">Avg. Completion</div>
          </div>
        </div>
      </div>

      <!-- Surveys Table Card -->
      <div class="wpask-card">
        <div class="wpask-card-header">
          <span class="wpask-card-title">Your Surveys</span>
          <div style="display:flex; gap: 10px;">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search surveys..."
              class="wpask-search-input"
            />
          </div>
        </div>

        <!-- Table -->
        <table class="wpask-survey-table" v-if="surveys.length > 0">
          <thead>
            <tr>
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
            <tr v-for="survey in filteredSurveys" :key="survey.id">
              <td>
                <span class="wpask-survey-name">{{ survey.title }}</span>
                <div style="font-size:12px; color:#9ca3af; margin-top:3px;">{{ survey.type }}</div>
              </td>
              <td>
                <span class="wpask-status-badge" :class="survey.status === 'publish' ? 'active' : 'draft'">
                  {{ survey.status === 'publish' ? 'Active' : 'Draft' }}
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
                  <router-link :to="`/surveys/${survey.id}/edit`" class="wpask-btn wpask-btn-secondary wpask-btn-sm">
                    Edit
                  </router-link>
                  <router-link :to="`/surveys/${survey.id}/results`" class="wpask-btn wpask-btn-secondary wpask-btn-sm">
                    Results
                  </router-link>
                  <button class="wpask-btn wpask-btn-danger wpask-btn-sm" @click="deleteSurvey(survey.id)">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div class="wpask-empty-state" v-else>
          <span class="wpask-empty-icon">📝</span>
          <h3 class="wpask-empty-title">Create your first survey</h3>
          <p class="wpask-empty-desc">Start collecting feedback in minutes. Choose a template or build from scratch.</p>
          <router-link to="/surveys/new" class="wpask-btn wpask-btn-primary">
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
const stats = ref({ total: 0, responses: 0, views: 0, completion: 0 });
const loading = ref(false);

const filteredSurveys = computed(() =>
  surveys.value.filter(s => s.title.toLowerCase().includes(searchQuery.value.toLowerCase()))
);

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const deleteSurvey = async (id) => {
  if (!confirm('Are you sure you want to delete this survey?')) return;
  // API call goes here
  surveys.value = surveys.value.filter(s => s.id !== id);
};

onMounted(async () => {
  // Load surveys from REST API
  try {
    const config = window.WPAskAdminConfig || {};
    const res = await fetch(`${config.api_url}/surveys`, {
      headers: { 'X-WP-Nonce': config.nonce }
    });
    if (res.ok) {
      surveys.value = await res.json();
      stats.value.total = surveys.value.length;
    }
  } catch (e) {
    // console.warn('Could not load surveys', e);
  }
});
</script>

<style scoped>
.wpask-search-input {
  border: 1px solid #d1d5db;
  border-radius: 7px;
  padding: 7px 12px;
  font-size: 13px;
  outline: none;
  width: 220px;
  color: #374151;
  font-family: inherit;
}

.wpask-search-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99,102,241,0.1);
}
</style>
