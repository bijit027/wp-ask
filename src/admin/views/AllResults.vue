<template>
  <div class="wpask-content-inner">
    <!-- Page Header -->
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">Results &amp; Analytics</h1>
        <p class="wpask-page-subtitle">Global performance across all your surveys.</p>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" style="text-align:center; padding: 60px 0; color: var(--muted-foreground);">
      <div class="wpask-spinner" style="margin: 0 auto 16px;"></div>
      Loading analytics...
    </div>

    <template v-else>
      <!-- Global Metric Cards -->
      <div class="wpask-metrics-grid">
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <ClipboardList /> Total Surveys
          </div>
          <div class="wpask-metric-value">{{ summary.total_surveys }}</div>
        </div>
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <Eye /> Total Impressions
          </div>
          <div class="wpask-metric-value">{{ summary.total_impressions.toLocaleString() }}</div>
        </div>
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <MessageSquare /> Total Responses
          </div>
          <div class="wpask-metric-value">{{ summary.total_responses.toLocaleString() }}</div>
        </div>
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <TrendingUp /> Avg. Completion Rate
          </div>
          <div class="wpask-metric-value">{{ summary.completion_rate }}%</div>
        </div>
      </div>

      <!-- Per-survey breakdown table -->
      <div style="margin-top: 40px;">
        <h2 class="wpask-section-title">Survey breakdown</h2>

        <div v-if="surveys.length === 0" class="wpask-empty-state">
          <BarChart3 style="width:48px;height:48px;color:var(--muted-foreground);margin-bottom:12px;" />
          <p>No surveys yet. <router-link to="/surveys/new" class="wpask-link">Create your first survey</router-link>.</p>
        </div>

        <div v-else class="wpask-responses-panel">
          <div class="wpask-responses-header" style="grid-template-columns: 2fr 1fr 1fr 1fr 120px;">
            <div>Survey</div>
            <div>Status</div>
            <div>Impressions</div>
            <div>Responses</div>
            <div style="text-align:right;">Action</div>
          </div>

          <div
            v-for="s in surveys"
            :key="s.id"
            class="wpask-response-row"
            style="grid-template-columns: 2fr 1fr 1fr 1fr 120px; align-items: center;"
          >
            <div style="font-weight:500; color:var(--foreground); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              {{ s.title }}
            </div>

            <div>
              <span class="wpask-status-badge" :class="s.status">
                <span class="wpask-status-dot"></span>
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
                class="wpask-btn wpask-btn-secondary"
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
  const config = window.WPAskAdminConfig || {};

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
