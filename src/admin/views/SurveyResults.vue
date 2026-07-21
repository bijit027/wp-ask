<template>
  <div class="wpask-content-inner">
    <!-- Back Link -->
    <button class="wpask-back-link" @click="$router.push('/')">
      <ArrowLeft />
      Back to surveys
    </button>

    <!-- Page Header -->
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">{{ survey?.title || 'Loading...' }}</h1>
        <p class="wpask-page-subtitle">Response overview and question breakdown.</p>
      </div>
      <div>
        <button class="wpask-btn wpask-btn-secondary" @click="exportCSV">
          <Download />
          Export CSV
        </button>
      </div>
    </div>

    <template v-if="survey">
      <!-- Top Metrics -->
      <div class="wpask-metrics-grid">
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <Eye /> Impressions
          </div>
          <div class="wpask-metric-value">{{ resultsData?.total_impressions || survey?.impressions || 0 }}</div>
        </div>
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <MessageSquare /> Responses
          </div>
          <div class="wpask-metric-value">{{ resultsData?.total_responses || responses.length }}</div>
        </div>
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <TrendingUp /> Completion rate
          </div>
          <div class="wpask-metric-value">{{ completionRate }}%</div>
        </div>
      </div>

      <!-- Question Breakdown -->
      <div class="wpask-q-breakdown">
        <h2 class="wpask-section-title">Question breakdown</h2>
        <div class="wpask-q-cards-grid">
          <div class="wpask-q-card" v-for="q in survey.questions" :key="q.id">
            <div class="wpask-q-card-header">
              <h3 class="wpask-q-card-title">{{ q.label }}</h3>
              <span class="wpask-q-card-type-badge">{{ q.type }}</span>
            </div>
            <div class="wpask-q-card-body">
              <template v-if="q.type === 'rating'">
                <div style="display:flex; align-items:baseline;">
                  <span class="wpask-avg-score">{{ getAverageRating(q.id) }}</span>
                  <span class="wpask-avg-sub">/ 5 average</span>
                </div>
                <div class="wpask-stars-row">
                  <Star
                    v-for="n in 5"
                    :key="n"
                    :style="n <= Math.round(getAverageRating(q.id)) ? 'fill: var(--primary); color: var(--primary);' : 'color: var(--border);'"
                  />
                </div>
                <!-- Rating distribution -->
                <div style="margin-top:12px;">
                  <div v-for="n in 5" :key="n" style="display:flex; align-items:center; margin-bottom:4px; font-size:12px;">
                    <span style="width:20px;">{{ n }}</span>
                    <Star style="width:12px; height:12px; margin-right:8px;" :style="n <= Math.round(getAverageRating(q.id)) ? 'fill: var(--primary); color: var(--primary);' : 'color: var(--border);'" />
                    <div style="flex:1; background:var(--muted); height:6px; border-radius:3px; overflow:hidden;">
                      <div style="background:var(--primary); height:100%;" :style="{ width: getRatingDistribution(q.id, n) + '%' }"></div>
                    </div>
                    <span style="width:40px; text-align:right; margin-left:8px; color:var(--muted-foreground);">{{ getRatingDistribution(q.id, n) }}%</span>
                  </div>
                </div>
              </template>
              
              <template v-else-if="q.type === 'nps'">
                <div style="display:flex; align-items:baseline;">
                  <span class="wpask-avg-score">{{ getAverageRating(q.id) }}</span>
                  <span class="wpask-avg-sub">/ 10 average</span>
                </div>
                <div style="margin-top:12px;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px; color:var(--muted-foreground);">
                    <span>Promoters (9-10)</span>
                    <span>{{ getNPSRangePercentage(q.id, 9, 10) }}%</span>
                  </div>
                  <div style="background:#22c55e; height:8px; border-radius:4px; margin-bottom:8px;" :style="{ width: getNPSRangePercentage(q.id, 9, 10) + '%' }"></div>
                  
                  <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px; color:var(--muted-foreground);">
                    <span>Passives (7-8)</span>
                    <span>{{ getNPSRangePercentage(q.id, 7, 8) }}%</span>
                  </div>
                  <div style="background:#eab308; height:8px; border-radius:4px; margin-bottom:8px;" :style="{ width: getNPSRangePercentage(q.id, 7, 8) + '%' }"></div>
                  
                  <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px; color:var(--muted-foreground);">
                    <span>Detractors (0-6)</span>
                    <span>{{ getNPSRangePercentage(q.id, 0, 6) }}%</span>
                  </div>
                  <div style="background:#ef4444; height:8px; border-radius:4px;" :style="{ width: getNPSRangePercentage(q.id, 0, 6) + '%' }"></div>
                </div>
              </template>
              
              <template v-else-if="q.type === 'radio' || q.type === 'choice' || q.type === 'checkbox' || q.type === 'dropdown'">
                <div v-for="opt in q.options" :key="opt" style="margin-bottom: 12px;">
                  <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px;">
                    <span>{{ opt }}</span>
                    <strong style="font-family:'Sora',sans-serif;">{{ getOptionPercentage(q.id, opt) }}%</strong>
                  </div>
                  <div style="background:var(--muted); height:6px; border-radius:3px; overflow:hidden;">
                    <div style="background:var(--primary); height:100%; transition:width 0.3s;" :style="{ width: getOptionPercentage(q.id, opt) + '%' }"></div>
                  </div>
                </div>
              </template>

              <template v-else>
                <div class="wpask-avg-score">{{ getAnswerCount(q.id) }}</div>
                <div class="wpask-avg-sub" style="margin-left:0; margin-top:4px;">Responses received</div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Individual Responses Table -->
      <div style="margin-top: 40px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <h2 class="wpask-section-title" style="margin:0;">Individual responses</h2>
          <div style="display:flex; gap:12px; align-items:center;">
            <div style="display:flex; align-items:center; gap:8px;">
              <label style="font-size:13px; color:var(--muted-foreground);">From:</label>
              <input type="date" v-model="dateRange.from" style="padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px;" />
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <label style="font-size:13px; color:var(--muted-foreground);">To:</label>
              <input type="date" v-model="dateRange.to" style="padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px;" />
            </div>
            <button @click="applyDateFilter" class="wpask-btn wpask-btn-secondary" style="padding:6px 12px; font-size:13px;">Apply Filter</button>
            <button @click="clearDateFilter" class="wpask-btn" style="padding:6px 12px; font-size:13px; background:var(--muted); color:var(--foreground);">Clear</button>
          </div>
        </div>
        <div class="wpask-responses-panel">
          <div class="wpask-responses-header">
            <div>Date</div>
            <div v-for="q in survey.questions" :key="q.id" class="truncate" :title="q.label">{{ q.label }}</div>
            <div style="text-align:right">Actions</div>
          </div>
          
          <div v-if="responses.length > 0">
            <div class="wpask-response-row" v-for="res in responses" :key="res.id">
              <div class="wpask-response-date">
                {{ new Date(res.created_at).toLocaleString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit' }) }}
              </div>
              
              <div v-for="q in survey.questions" :key="q.id" style="min-width:0;">
                <div v-if="res.answers[q.id]" class="truncate" style="font-size:13px; color:var(--foreground);">
                  <template v-if="q.type === 'rating'">
                    <div class="wpask-stars-row" style="margin-top:0;">
                      <Star
                        v-for="n in 5"
                        :key="n"
                        :style="n <= res.answers[q.id].value ? 'fill: var(--primary); color: var(--primary);' : 'color: var(--border);'"
                      />
                    </div>
                  </template>
                  <template v-else-if="q.type === 'checkbox'">
                    <span v-if="Array.isArray(res.answers[q.id].value)" style="font-size:13px; color:var(--foreground);">
                      {{ res.answers[q.id].value.join(', ') }}
                    </span>
                    <span v-else style="color:var(--muted-foreground);">—</span>
                  </template>
                  <template v-else-if="q.type === 'date'">
                    <span v-if="res.answers[q.id].value" style="font-size:13px; color:var(--foreground);">
                      {{ new Date(res.answers[q.id].value).toLocaleDateString() }}
                    </span>
                    <span v-else style="color:var(--muted-foreground);">—</span>
                  </template>
                  <template v-else-if="q.type === 'email'">
                    <span v-if="res.answers[q.id].value" style="font-size:13px; color:var(--foreground);">
                      {{ res.answers[q.id].value }}
                    </span>
                    <span v-else style="color:var(--muted-foreground);">—</span>
                  </template>
                  <template v-else-if="q.type === 'number'">
                    <span v-if="res.answers[q.id].value" style="font-size:13px; color:var(--foreground);">
                      {{ res.answers[q.id].value }}
                    </span>
                    <span v-else style="color:var(--muted-foreground);">—</span>
                  </template>
                  <template v-else>
                    {{ res.answers[q.id].value }}
                  </template>
                </div>
                <div v-else style="color:var(--muted-foreground);">—</div>
              </div>
              
              <div style="text-align:right;">
                <button class="wpask-icon-btn danger" @click="deleteResponse(res.id)" title="Permanently Delete Response">
                  <Trash2 />
                </button>
              </div>
            </div>
          </div>
          
          <div class="wpask-empty-row" v-else>
            No responses yet.
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ArrowLeft, Download, Eye, MessageSquare, TrendingUp, Star, Trash2 } from 'lucide-vue-next';

const route = useRoute();
const survey = ref(null);
const responses = ref([]);
const resultsData = ref(null);
const dateRange = ref({ from: '', to: '' });

const completionRate = computed(() => {
  if (!resultsData.value) return 0;
  return resultsData.value.completion_rate || 0;
});

const getAverageRating = (questionId) => {
  if (!resultsData.value?.questions_data?.[questionId]) return '0.0';
  
  const questionData = resultsData.value.questions_data[questionId];
  const total = Object.values(questionData).reduce((a, b) => a + b, 0);
  
  if (total === 0) return '0.0';
  
  let sum = 0;
  const keys = Object.keys(questionData).map(Number).sort((a, b) => a - b);
  
  // Determine scale based on max value
  const maxVal = Math.max(...keys);
  const scale = maxVal > 5 ? 10 : 5;
  
  for (let i = 0; i <= scale; i++) {
    sum += (questionData[i] || 0) * i;
  }
  
  return (sum / total).toFixed(1);
};

const getOptionPercentage = (questionId, optionValue) => {
  if (!resultsData.value?.questions_data?.[questionId]) return 0;
  
  const questionData = resultsData.value.questions_data[questionId];
  const total = Object.values(questionData).reduce((a, b) => a + b, 0);
  
  if (total === 0) return 0;
  
  const count = questionData[optionValue] || 0;
  return Math.round((count / total) * 100);
};

const getAnswerCount = (questionId) => {
  if (!resultsData.value?.questions_data?.[questionId]) return 0;
  
  const questionData = resultsData.value.questions_data[questionId];
  return Object.values(questionData).reduce((a, b) => a + b, 0);
};

const getNPSRangePercentage = (questionId, min, max) => {
  if (!resultsData.value?.questions_data?.[questionId]) return 0;
  
  const questionData = resultsData.value.questions_data[questionId];
  const total = Object.values(questionData).reduce((a, b) => a + b, 0);
  
  if (total === 0) return 0;
  
  let rangeCount = 0;
  for (let i = min; i <= max; i++) {
    rangeCount += (questionData[i] || 0);
  }
  
  return Math.round((rangeCount / total) * 100);
};

const getRatingDistribution = (questionId, rating) => {
  if (!resultsData.value?.questions_data?.[questionId]) return 0;
  
  const questionData = resultsData.value.questions_data[questionId];
  const total = Object.values(questionData).reduce((a, b) => a + b, 0);
  
  if (total === 0) return 0;
  
  const count = questionData[rating] || 0;
  return Math.round((count / total) * 100);
};

onMounted(async () => {
  const config = window.WPAskAdminConfig || {};
  const id = route.params.id;
  
  try {
    // Fetch survey details
    let res = await fetch(`${config.api_url}/surveys/${id}`, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) survey.value = await res.json();
    
    // Fetch aggregated results data
    res = await fetch(`${config.api_url}/surveys/${id}/results`, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) {
      resultsData.value = await res.json();
    }
    
    // Fetch individual responses for the table
    res = await fetch(`${config.api_url}/surveys/${id}/responses`, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) {
      const data = await res.json();
      responses.value = data.map(r => ({
        ...r,
        answers: typeof r.answers === 'string' ? JSON.parse(r.answers) : r.answers
      }));
    }
  } catch (e) {
    console.error('Failed to load results', e);
  }
});

const deleteResponse = async (responseId) => {
  if (!confirm('Are you sure you want to permanently delete this response?')) return;
  
  const config = window.WPAskAdminConfig || {};
  try {
    const res = await fetch(`${config.api_url}/responses/${responseId}`, {
      method: 'DELETE',
      headers: { 'X-WP-Nonce': config.nonce }
    });
    
    if (res.ok) {
      responses.value = responses.value.filter(r => r.id !== responseId);
    } else {
      alert('Failed to delete response.');
    }
  } catch (e) {
    console.error('Error deleting response', e);
    alert('Error deleting response.');
  }
};

const exportCSV = () => {
  const config = window.WPAskAdminConfig || {};
  const id = route.params.id;
  const exportUrl = `${config.api_url}/surveys/${id}/export?_wpnonce=${config.nonce}`;
  window.open(exportUrl, '_blank');
};

const applyDateFilter = async () => {
  const config = window.WPAskAdminConfig || {};
  const id = route.params.id;
  
  let url = `${config.api_url}/surveys/${id}/responses`;
  const params = new URLSearchParams();
  
  if (dateRange.value.from) {
    params.append('from', dateRange.value.from);
  }
  if (dateRange.value.to) {
    params.append('to', dateRange.value.to);
  }
  
  if (params.toString()) {
    url += '?' + params.toString();
  }
  
  try {
    const res = await fetch(url, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) {
      const data = await res.json();
      responses.value = data.map(r => ({
        ...r,
        answers: typeof r.answers === 'string' ? JSON.parse(r.answers) : r.answers
      }));
    }
  } catch (e) {
    console.error('Failed to filter responses', e);
  }
};

const clearDateFilter = async () => {
  dateRange.value = { from: '', to: '' };
  const config = window.WPAskAdminConfig || {};
  const id = route.params.id;
  
  try {
    const res = await fetch(`${config.api_url}/surveys/${id}/responses`, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) {
      const data = await res.json();
      responses.value = data.map(r => ({
        ...r,
        answers: typeof r.answers === 'string' ? JSON.parse(r.answers) : r.answers
      }));
    }
  } catch (e) {
    console.error('Failed to load responses', e);
  }
};
</script>
