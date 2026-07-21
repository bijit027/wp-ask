<template>
  <div class="wpask-onboarding-wrapper">
    <div class="wpask-onboarding-card">
      <div class="wpask-onboarding-header">
        <div class="wpask-logo-icon" style="font-size:48px; margin-bottom:10px;">💬</div>
        <h1 class="wpask-page-title">Welcome to WPAsk</h1>
        <p style="color:#6b7280; margin-top:5px;">Let's get your first survey set up in seconds.</p>
      </div>

      <div class="wpask-onboarding-body">
        <div v-if="step === 1" class="step-content fade-in">
          <h3 style="margin-bottom: 15px;">What's your primary goal?</h3>
          <div class="goal-grid">
            <div class="goal-card" :class="{active: goal === 'feedback'}" @click="goal = 'feedback'">
              <div class="goal-icon">📝</div>
              <strong>Collect Feedback</strong>
              <p>Find out what users think</p>
            </div>
            <div class="goal-card" :class="{active: goal === 'nps'}" @click="goal = 'nps'">
              <div class="goal-icon">⭐</div>
              <strong>NPS & Ratings</strong>
              <p>Measure satisfaction</p>
            </div>
            <div class="goal-card" :class="{active: goal === 'leads'}" @click="goal = 'leads'">
              <div class="goal-icon">🎯</div>
              <strong>Capture Leads</strong>
              <p>Grow your email list</p>
            </div>
          </div>
          <div style="text-align: right; margin-top: 30px;">
            <button class="wpask-btn wpask-btn-primary" @click="step = 2" :disabled="!goal">
              Continue →
            </button>
          </div>
        </div>

        <div v-if="step === 2" class="step-content fade-in">
          <h3 style="margin-bottom: 15px;">Choose your brand color</h3>
          <p style="color:#6b7280; margin-bottom:20px; font-size:13px;">This will be used for your widget buttons and accents.</p>
          <div style="display:flex; justify-content:center; margin-bottom:30px;">
            <el-color-picker v-model="brandColor" size="large" />
          </div>
          <div style="display: flex; justify-content: space-between; margin-top: 30px;">
            <button class="wpask-btn wpask-btn-secondary" @click="step = 1">← Back</button>
            <button class="wpask-btn wpask-btn-primary" @click="finishOnboarding">
              Complete Setup ✨
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const step = ref(1);
const goal = ref(null);
const brandColor = ref('#6366f1');

const finishOnboarding = async () => {
  const config = window.WPAskAdminConfig || {};
  
  // Save initial settings
  try {
    await fetch(`${config.api_url}/settings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.nonce
      },
      body: JSON.stringify({ default_color: brandColor.value })
    });
  } catch (e) {
    console.warn('Could not save onboarding settings', e);
  }

  // Redirect to builder
  router.push('/surveys/new');
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
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 600px;
  overflow: hidden;
}

.wpask-onboarding-header {
  text-align: center;
  padding: 40px 40px 20px;
  border-bottom: 1px solid #f0f0f5;
}

.wpask-onboarding-body {
  padding: 40px;
  min-height: 300px;
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
  font-size: 32px;
  margin-bottom: 10px;
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
</style>


