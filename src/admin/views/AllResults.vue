<template>
  <div class="pollquest-content-inner">
    <!-- Page Header -->
    <div class="pollquest-page-header">
      <div>
        <h1 class="pollquest-page-title">Results &amp; Analytics</h1>
        <p class="pollquest-page-subtitle">Global performance across all your surveys.</p>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" style="text-align:center; padding: 60px 0; color: var(--muted-foreground);">
      <div class="pollquest-spinner" style="margin: 0 auto 16px;"></div>
      Loading analytics...
    </div>

    <template v-else>
      <!-- Global Metric Cards -->
      <div class="pollquest-metrics-grid">
        <div class="pollquest-metric-card">
          <div class="pollquest-metric-label">
            <ClipboardList /> Total Surveys
          </div>
          <div class="pollquest-metric-value">{{ summary.total_surveys }}</div>
        </div>
        <div class="pollquest-metric-card">
          <div class="pollquest-metric-label">
            <Eye /> Total Impressions
          </div>
          <div class="pollquest-metric-value">{{ summary.total_impressions.toLocaleString() }}</div>
        </div>
        <div class="pollquest-metric-card">
          <div class="pollquest-metric-label">
            <MessageSquare /> Total Responses
          </div>
          <div class="pollquest-metric-value">{{ summary.total_responses.toLocaleString() }}</div>
        </div>
        <div class="pollquest-metric-card">
          <div class="pollquest-metric-label">
            <TrendingUp /> Avg. Completion Rate
          </div>
          <div class="pollquest-metric-value">{{ summary.completion_rate }}%</div>
        </div>
      </div>

      <!-- Per-survey breakdown table -->
      <div style="margin-top: 40px;">
        <h2 class="pollquest-section-title">Survey breakdown</h2>

        <div v-if="surveys.length === 0" class="pollquest-empty-state">
          <BarChart3 style="width:48px;height:48px;color:var(--muted-foreground);margin-bottom:12px;" />
          <p>No surveys yet. <router-link to="/surveys/new" class="pollquest-link">Create your first survey</router-link>.</p>
        </div>

        <div v-else class="pollquest-responses-panel">
          <div class="pollquest-responses-header" style="grid-template-columns: 2fr 1fr 1fr 1fr 120px;">
            <div>Survey</div>
            <div>Status</div>
            <div>Impressions</div>
            <div>Responses</div>
            <div style="text-align:right;">Action</div>
          </div>

          <div
            v-for="s in surveys"
            :key="s.id"
            class="pollquest-response-row"
            style="grid-template-columns: 2fr 1fr 1fr 1fr 120px; align-items: center;"
          >
            <div style="font-weight:500; color:var(--foreground); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              {{ s.title }}
            </div>

            <div>
              <span class="pollquest-status-badge" :class="s.status">
                <span class="pollquest-status-dot"></span>
                {{ s.status }}
              </span>
            </div>

            <div style="font-family:'Sora',sans-serif; font-weight:600; color:var(--foreground);">
              {{ (s.impressions || 0).toLocaleString() }}
            </div>

            <div style="font-family:'Sora',sans-serif; font-weight:600; color:var(--foreground);">
              {{ (surveyCounts[s.id] || 0).toLocaleString() }}
            </div>

            <div style="text-align:right;">
              <router-link
                :to="`/surveys/${s.id}/results`"
                class="pollquest-btn pollquest-btn-secondary"
                style="font-size:12px; padding:5px 12px;"
              >
                <BarChart3 style="width:12px;height:12px;" /> View
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ClipboardList, Eye, MessageSquare, TrendingUp, BarChart3 } from 'lucide-vue-next';

const loading = ref(true);
const summary = ref({ total_surveys: 0, total_impressions: 0, total_responses: 0, completion_rate: 0 });
const surveys = ref([]);
const surveyCounts = ref({});

onMounted(async () => {
  const config = window.PollQuestAdminConfig || {};

  try {
    // 1. Load global summary
    const summaryRes = await fetch(`${config.api_url}/results-summary`, {
      headers: { 'X-WP-Nonce': config.nonce }
    });
    if (summaryRes.ok) {
      summary.value = await summaryRes.json();
    }

    // 2. Load all surveys for the table
    const surveysRes = await fetch(`${config.api_url}/surveys?per_page=100`, {
      headers: { 'X-WP-Nonce': config.nonce }
    });
    if (surveysRes.ok) {
      surveys.value = await surveysRes.json();
    }

    // 3. Load per-survey response counts
    await Promise.allSettled(
      surveys.value.map(async (s) => {
        const res = await fetch(`${config.api_url}/surveys/${s.id}/results`, {
          headers: { 'X-WP-Nonce': config.nonce }
        });
        if (res.ok) {
          const data = await res.json();
          surveyCounts.value[s.id] = data.total_responses || 0;
        }
      })
    );
  } catch (e) {
    console.error('Failed to load results summary', e);
  } finally {
    loading.value = false;
  }
});
</script>
