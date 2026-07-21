<template>
  <div class="wpask-page">
    <div class="wpask-page-title-bar">
      <div style="display: flex; align-items: center; gap: 15px;">
        <router-link to="/" class="wpask-btn wpask-btn-secondary" style="padding: 6px 10px;">
          ← Back
        </router-link>
        <h1 class="wpask-page-title">Results: {{ survey?.title || 'Loading...' }}</h1>
      </div>
      <div class="wpask-app-header-right">
        <button class="wpask-btn wpask-btn-secondary" @click="exportCSV">
          📥 Export CSV
        </button>
      </div>
    </div>

    <div class="wpask-content-inner" v-if="survey">
      <!-- Top Stats -->
      <div class="wpask-stats-grid">
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon blue">👁️</div>
          <div>
            <div class="wpask-stat-value">{{ survey.impressions || 0 }}</div>
            <div class="wpask-stat-label">Impressions</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon green">💬</div>
          <div>
            <div class="wpask-stat-value">{{ responses.length }}</div>
            <div class="wpask-stat-label">Responses</div>
          </div>
        </div>
        <div class="wpask-stat-card">
          <div class="wpask-stat-icon amber">📈</div>
          <div>
            <div class="wpask-stat-value">{{ completionRate }}%</div>
            <div class="wpask-stat-label">Completion Rate</div>
          </div>
        </div>
      </div>

      <!-- Question Summaries -->
      <div style="margin-bottom: 30px;">
        <h2 style="font-size: 18px; margin-bottom: 15px; color: #1a1d2b;">Question Breakdown</h2>
        <div class="grid-2-col">
          <div class="wpask-card" v-for="q in survey.questions" :key="q.id">
            <div class="wpask-card-header">
              <span class="wpask-card-title">{{ q.label }}</span>
              <span style="font-size:12px; color:#9ca3af; text-transform:uppercase;">{{ q.type }}</span>
            </div>
            <div class="wpask-card-body">
              <div v-if="q.type === 'rating'">
                <div style="font-size: 32px; font-weight: 800; color: #fbbf24;">
                  {{ getAverageRating(q.id) }} <span style="font-size: 16px; color: #9ca3af;">/ 5</span>
                </div>
                <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">Average Rating</div>
              </div>
              
              <div v-else-if="q.type === 'radio'">
                <div v-for="opt in q.options" :key="opt" style="margin-bottom: 10px;">
                  <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px;">
                    <span>{{ opt }}</span>
                    <strong>{{ getOptionPercentage(q.id, opt) }}%</strong>
                  </div>
                  <div style="background:#f0f0f5; height:6px; border-radius:3px; overflow:hidden;">
                    <div style="background:#6366f1; height:100%;" :style="{ width: getOptionPercentage(q.id, opt) + '%' }"></div>
                  </div>
                </div>
              </div>

              <div v-else>
                <div style="font-size: 24px; font-weight: 800; color: #1a1d2b;">
                  {{ getAnswerCount(q.id) }}
                </div>
                <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">Text Responses Received</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Individual Responses Table -->
      <div class="wpask-card">
        <div class="wpask-card-header">
          <span class="wpask-card-title">Individual Responses</span>
        </div>
        <table class="wpask-survey-table" v-if="responses.length > 0">
          <thead>
            <tr>
              <th>Date</th>
              <th v-for="q in survey.questions" :key="q.id">{{ q.label }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="res in responses" :key="res.id">
              <td style="white-space:nowrap; color:#6b7280; font-size:13px;">
                {{ new Date(res.created_at).toLocaleString() }}
              </td>
              <td v-for="q in survey.questions" :key="q.id">
                <div class="answer-cell">
                  <span v-if="res.answers[q.id]">
                    {{ q.type === 'rating' ? '⭐ '.repeat(res.answers[q.id].value) : res.answers[q.id].value }}
                  </span>
                  <span v-else style="color:#d1d5db;">—</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="wpask-empty-state" style="padding: 40px;" v-else>
          <span class="wpask-empty-icon" style="font-size:32px;">📭</span>
          <h3 class="wpask-empty-title">No responses yet</h3>
          <p class="wpask-empty-desc">When users fill out your survey, their answers will appear here.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';

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
    // Fetch Survey details
    let res = await fetch(`${config.api_url}/surveys/${id}`, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) survey.value = await res.json();
    
    // Fetch Responses for this survey
    res = await fetch(`${config.api_url}/responses?survey_id=${id}`, { headers: { 'X-WP-Nonce': config.nonce } });
    if (res.ok) {
      const data = await res.json();
      // Ensure answers are parsed
      responses.value = data.map(r => ({
        ...r,
        answers: typeof r.answers === 'string' ? JSON.parse(r.answers) : r.answers
      }));
    }
  } catch (e) {
    console.error('Failed to load results', e);
  }
});
</script>

<style scoped>
.grid-2-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.answer-cell {
  max-width: 250px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
