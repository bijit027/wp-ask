<template>
  <div class="onboarding">
    <h2>Welcome to WPAsk</h2>
    <el-card>
      <p>Quick setup wizard will go here.</p>
      <el-button type="primary" @click="finishOnboarding">Complete Setup</el-button>
    </el-card>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const finishOnboarding = () => {
  // Logic to save onboarding state via API goes here
  router.push('/');
};
</script>

<style scoped>
.onboarding {
  max-width: 600px;
  margin: 40px auto;
  text-align: center;
}
</style>
