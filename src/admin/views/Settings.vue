<template>
  <div class="pollquest-content-inner">
    <!-- Page Header -->
    <div class="pollquest-page-header">
      <div>
        <h1 class="pollquest-page-title">Settings</h1>
        <p class="pollquest-page-subtitle">Defaults, permissions and data handling for PollQuest.</p>
      </div>
      <div>
        <button class="pollquest-btn pollquest-btn-primary" @click="saveSettings">
          Save changes
        </button>
      </div>
    </div>

    <div class="pollquest-settings-sections">
      
      <!-- General Settings -->
      <section class="pollquest-settings-section">
        <div>
          <h2 class="pollquest-settings-section-title">General</h2>
          <p class="pollquest-settings-section-desc">Applies to any new survey you create.</p>
        </div>
        
        <div class="pollquest-settings-fields">
          <!-- Brand Color -->
          <div>
            <div class="pollquest-field-label">Brand color</div>
            <p class="pollquest-field-hint">Used for accents on published surveys.</p>
            <div class="pollquest-field-control pollquest-color-row">
              <label class="pollquest-color-swatch">
                <div class="pollquest-color-preview" :style="{ background: settings.default_color || '#6366f1' }"></div>
                <input type="color" v-model="settings.default_color" />
              </label>
              <code class="pollquest-color-code">{{ (settings.default_color || '#6366f1').toUpperCase() }}</code>
            </div>
          </div>

          <!-- Widget Position -->
          <div>
            <div class="pollquest-field-label">Widget position</div>
            <p class="pollquest-field-hint">Where the survey widget appears on the page.</p>
            <div class="pollquest-field-control pollquest-position-toggle">
              <button
                v-for="pos in ['left', 'center', 'right']"
                :key="pos"
                class="pollquest-pos-btn"
                :class="{ active: settings.default_position === `bottom-${pos}` }"
                @click="settings.default_position = `bottom-${pos}`"
              >
                Bottom {{ pos }}
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Access Control -->
      <section class="pollquest-settings-section">
        <div>
          <h2 class="pollquest-settings-section-title">Access control</h2>
          <p class="pollquest-settings-section-desc">Choose which roles can manage surveys.</p>
        </div>
        
        <div class="pollquest-settings-fields">
          <div>
            <div class="pollquest-field-label">Minimum role</div>
            <div class="pollquest-field-control">
              <select class="pollquest-select" v-model="settings.minimum_role">
                <option value="administrator">Administrator</option>
                <option value="editor">Editor</option>
                <option value="author">Author</option>
                <option value="contributor">Contributor</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <!-- Privacy -->
      <section class="pollquest-settings-section">
        <div>
          <h2 class="pollquest-settings-section-title">Privacy</h2>
          <p class="pollquest-settings-section-desc">Control external service usage.</p>
        </div>
        
        <div class="pollquest-settings-fields">
          <label class="pollquest-toggle-row">
            <input type="checkbox" v-model="settings.enable_gravatar" />
            <div>
              <div class="pollquest-toggle-row-label">Enable Gravatar avatars</div>
              <p class="pollquest-toggle-row-hint">Sends a hash of respondent email addresses to gravatar.com to load profile photos. Disabled by default for privacy compliance.</p>
            </div>
          </label>
        </div>
      </section>

      <!-- Data Handling -->
      <section class="pollquest-settings-section">
        <div>
          <h2 class="pollquest-settings-section-title">Data handling</h2>
          <p class="pollquest-settings-section-desc">Control what happens on plugin removal.</p>
        </div>
        
        <div class="pollquest-settings-fields">
          <label class="pollquest-toggle-row">
            <input type="checkbox" v-model="settings.delete_on_uninstall" />
            <div>
              <div class="pollquest-toggle-row-label">Delete all data on uninstall</div>
              <p class="pollquest-toggle-row-hint">Removes surveys, responses and settings. This cannot be undone.</p>
            </div>
          </label>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const settings = ref({
  default_color: '#6366f1',
  default_position: 'bottom-right',
  delete_on_uninstall: false,
  minimum_role: 'administrator',
  enable_gravatar: false
});

const saveSettings = async () => {
  const config = window.PollQuestAdminConfig || {};
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
  const config = window.PollQuestAdminConfig || {};
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
