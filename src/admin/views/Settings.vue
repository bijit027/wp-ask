<template>
  <div class="wpask-page">
    <div class="wpask-page-title-bar">
      <h1 class="wpask-page-title">⚙️ Settings</h1>
      <div class="wpask-app-header-right">
        <button class="wpask-btn wpask-btn-primary" @click="saveSettings">
          💾 Save Settings
        </button>
      </div>
    </div>

    <div class="wpask-content-inner">
      <div class="wpask-card" style="max-width: 800px; margin: 0 auto;">
        <div class="wpask-card-header">
          <span class="wpask-card-title">General Settings</span>
        </div>
        <div class="wpask-card-body">
          <div class="settings-form-group">
            <label class="settings-label">Brand Color</label>
            <p class="settings-help">This color will be used as the default for new surveys.</p>
            <el-color-picker v-model="settings.default_color" />
          </div>

          <div class="settings-form-group">
            <label class="settings-label">Widget Position</label>
            <p class="settings-help">The default screen position for new surveys.</p>
            <el-radio-group v-model="settings.default_position">
              <el-radio-button label="bottom-left">Bottom Left</el-radio-button>
              <el-radio-button label="bottom-right">Bottom Right</el-radio-button>
              <el-radio-button label="bottom-center">Bottom Center</el-radio-button>
            </el-radio-group>
          </div>

          <div class="settings-form-group">
            <label class="settings-label">Uninstall Behavior</label>
            <p class="settings-help">What should happen to your data when you delete the plugin?</p>
            <el-checkbox v-model="settings.delete_on_uninstall">
              Delete all WPAsk data upon uninstallation (cannot be undone)
            </el-checkbox>
          </div>
        </div>
      </div>
      
      <div class="wpask-card" style="max-width: 800px; margin: 30px auto 0;">
        <div class="wpask-card-header">
          <span class="wpask-card-title">Access Control</span>
        </div>
        <div class="wpask-card-body">
          <div class="settings-form-group">
            <label class="settings-label">Who can manage surveys?</label>
            <p class="settings-help">Select the minimum user role required to access the WPAsk dashboard.</p>
            <el-select v-model="settings.minimum_role" placeholder="Select role" style="width: 250px;">
              <el-option label="Administrator" value="administrator" />
              <el-option label="Editor" value="editor" />
              <el-option label="Author" value="author" />
            </el-select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const settings = ref({
  default_color: '#6366f1',
  default_position: 'bottom-right',
  delete_on_uninstall: false,
  minimum_role: 'administrator'
});

const saveSettings = async () => {
  const config = window.WPAskAdminConfig || {};
  try {
    const res = await fetch(`${config.api_url}/settings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.nonce
      },
      body: JSON.stringify(settings.value)
    });
    
    if (res.ok) {
      alert('Settings saved successfully!');
    } else {
      alert('Failed to save settings.');
    }
  } catch (e) {
    console.error('Error saving settings', e);
  }
};

onMounted(async () => {
  const config = window.WPAskAdminConfig || {};
  try {
    const res = await fetch(`${config.api_url}/settings`, {
      headers: { 'X-WP-Nonce': config.nonce }
    });
    if (res.ok) {
      const data = await res.json();
      Object.assign(settings.value, data);
    }
  } catch (e) {
    console.error('Failed to load settings', e);
  }
});
</script>

<style scoped>
.settings-form-group {
  margin-bottom: 25px;
}
.settings-form-group:last-child {
  margin-bottom: 0;
}
.settings-label {
  display: block;
  font-weight: 600;
  color: #1a1d2b;
  margin-bottom: 5px;
  font-size: 14px;
}
.settings-help {
  color: #6b7280;
  font-size: 13px;
  margin: 0 0 10px 0;
}
</style>
