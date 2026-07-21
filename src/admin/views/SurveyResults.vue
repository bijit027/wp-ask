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
          <div class="wpask-metric-value">{{ survey.impressions || 0 }}</div>
        </div>
        <div class="wpask-metric-card">
          <div class="wpask-metric-label">
            <MessageSquare /> Responses
          </div>
          <div class="wpask-metric-value">{{ responses.length }}</div>
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
              </template>
              
              <template v-else-if="q.type === 'radio' || q.type === 'choice'">
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
        <h2 class="wpask-section-title">Individual responses</h2>
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

const completionRate = computed(() => {
  if (!survey.value || survey.value.impressions === 0) return 0;
  return Math.round((responses.value.length / survey.value.impressions) * 100);
});

const getAverageRating = (questionId) => {
  const ratings = responses.value
    .map(r => r.answers[questionId]?.value)
    .filter(val => val != null);
  
  if (ratings.length === 0) return '0.0';
  const sum = ratings.reduce((a, b) => a + parseInt(b, 10), 0);
  return (sum / ratings.length).toFixed(1);
};

const getOptionPercentage = (questionId, optionValue) => {
  const answers = responses.value
    .map(r => r.answers[questionId]?.value)
    .filter(val => val != null);
    
  if (answers.length === 0) return 0;
  
  const count = answers.filter(val => val === optionValue).length;
  return Math.round((count / answers.length) * 100);
};

const getAnswerCount = (questionId) => {
  return responses.value
    .filter(r => r.answers[questionId]?.value != null)
    .length;
};

const exportCSV = () => {
  alert('Pro Feature: CSV Export is available in the Pro version.');
};

onMounted(async () => {
  const config = window.WPAskAdminConfig || {};
  const id = route.params.id;
  
  try {
    let res = await fetch(`${config.api_url}/surveys/${id}`, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) survey.value = await res.json();
    
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
</script>
