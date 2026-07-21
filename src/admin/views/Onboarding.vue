<template>
  <div class="wpask-onboarding-wrapper">
    <div class="wpask-onboarding-card">
      <div class="wpask-onboarding-header">
        <div class="wpask-onboarding-logo">
          <MessageSquare />
        </div>
        <h1 class="wpask-page-title">Welcome to WPAsk</h1>
        <p class="wpask-onboarding-subtitle">Let's get your first survey set up in seconds.</p>
      </div>

      <div class="wpask-onboarding-body">
        <div v-if="step === 1" class="step-content fade-in">
          <h3 class="wpask-onboarding-step-title">What's your primary goal?</h3>
          <div class="goal-grid">
            <div class="goal-card" :class="{ active: goal === 'feedback' }" @click="goal = 'feedback'">
              <div class="goal-icon"><ClipboardList /></div>
              <strong>Collect Feedback</strong>
              <p>Find out what users think</p>
            </div>
            <div class="goal-card" :class="{ active: goal === 'nps' }" @click="goal = 'nps'">
              <div class="goal-icon"><Star /></div>
              <strong>NPS & Ratings</strong>
              <p>Measure satisfaction</p>
            </div>
            <div class="goal-card" :class="{ active: goal === 'leads' }" @click="goal = 'leads'">
              <div class="goal-icon"><Target /></div>
              <strong>Capture Leads</strong>
              <p>Grow your email list</p>
            </div>
          </div>
          <div class="wpask-onboarding-actions wpask-onboarding-actions--end">
            <button class="wpask-btn wpask-btn-primary" @click="step = 2" :disabled="!goal">
              Continue
            </button>
          </div>
        </div>

        <div v-if="step === 2" class="step-content fade-in">
          <h3 class="wpask-onboarding-step-title">Choose a template</h3>
          <p class="wpask-onboarding-step-desc">We picked suggestions based on your goal. Select one to continue.</p>
          <TemplatePickerGrid
            :selected-id="selectedTemplateId"
            :highlight-ids="suggestedTemplateIds"
            @select="onTemplateSelect"
            @loaded="onTemplatesLoaded"
          />
          <div class="wpask-onboarding-actions">
            <button class="wpask-btn wpask-btn-secondary" @click="step = 1">Back</button>
            <button class="wpask-btn wpask-btn-primary" @click="step = 3" :disabled="!selectedTemplateId">
              Continue
            </button>
          </div>
        </div>

        <div v-if="step === 3" class="step-content fade-in">
          <h3 class="wpask-onboarding-step-title">Choose your brand color</h3>
          <p class="wpask-onboarding-step-desc">This will be used for your widget buttons and accents.</p>
          <div class="wpask-onboarding-color-picker">
            <el-color-picker v-model="brandColor" size="large" />
          </div>
          <div class="wpask-onboarding-actions">
            <button class="wpask-btn wpask-btn-secondary" @click="step = 2">Back</button>
            <button class="wpask-btn wpask-btn-primary" @click="finishOnboarding">
              Complete setup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { MessageSquare, ClipboardList, Star, Target } from 'lucide-vue-next';
import TemplatePickerGrid from '../components/TemplatePickerGrid.vue';

const router = useRouter();
const step = ref(1);
const goal = ref(null);
const brandColor = ref('#6366f1');
const selectedTemplateId = ref('');

const goalTemplateMap = {
  feedback: ['website-feedback', 'blank'],
  nps: ['nps-survey', 'website-feedback'],
  leads: ['lead-capture', 'post-purchase'],
};

const suggestedTemplateIds = computed(() => goalTemplateMap[goal.value] || ['blank']);

function onTemplateSelect(template) {
  selectedTemplateId.value = template.id;
}

function onTemplatesLoaded(templates) {
  if (selectedTemplateId.value) return;
  const suggested = suggestedTemplateIds.value.find(id =>
    templates.some(t => t.id === id && t.is_available)
  );
  if (suggested) {
    selectedTemplateId.value = suggested;
  }
}

const finishOnboarding = async () => {
  const config = window.WPAskAdminConfig || {};

  try {
    await fetch(`${config.api_url}/settings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.nonce,
      },
      body: JSON.stringify({ default_color: brandColor.value }),
    });
  } catch (e) {
    console.warn('Could not save onboarding settings', e);
  }

  const query = selectedTemplateId.value && selectedTemplateId.value !== 'blank'
    ? { template: selectedTemplateId.value }
    : {};

  router.push({ path: '/surveys/new', query });
};
</script>

<style scoped>
.wpask-onboarding-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 100px);
  background: #f6f7fb;
  padding: 20px;
}

.wpask-onboarding-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  width: 100%;
  max-width: 760px;
  overflow: hidden;
}

.wpask-onboarding-header {
  text-align: center;
  padding: 40px 40px 20px;
  border-bottom: 1px solid #f0f0f5;
}

.wpask-onboarding-logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  margin-bottom: 16px;
}

.wpask-onboarding-logo svg {
  width: 28px;
  height: 28px;
}

.wpask-onboarding-subtitle {
  color: #6b7280;
  margin-top: 5px;
}

.wpask-onboarding-body {
  padding: 32px 40px 40px;
  min-height: 300px;
}

.wpask-onboarding-step-title {
  margin-bottom: 8px;
  font-size: 18px;
}

.wpask-onboarding-step-desc {
  color: #6b7280;
  margin-bottom: 20px;
  font-size: 13px;
}

.wpask-onboarding-color-picker {
  display: flex;
  justify-content: center;
  margin: 30px 0;
}

.wpask-onboarding-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 30px;
}

.wpask-onboarding-actions--end {
  justify-content: flex-end;
}

.goal-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
}

.goal-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px 15px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.goal-card:hover {
  border-color: #a5b4fc;
  background: #f8fafc;
}

.goal-card.active {
  border-color: #6366f1;
  background: #eef2ff;
}

.goal-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #eef2ff;
  color: #6366f1;
  margin-bottom: 10px;
}

.goal-icon svg {
  width: 24px;
  height: 24px;
}

.goal-card strong {
  display: block;
  font-size: 14px;
  color: #1a1d2b;
  margin-bottom: 4px;
}

.goal-card p {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.fade-in {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 640px) {
  .goal-grid {
    grid-template-columns: 1fr;
  }

  .wpask-onboarding-body {
    padding: 24px;
  }
}
</style>
