<template>
  <div class="wpask-content-inner">
    <!-- Page Header -->
    <div class="wpask-page-header">
      <div>
        <h1 class="wpask-page-title">Settings</h1>
        <p class="wpask-page-subtitle">Defaults, permissions and data handling for WPAsk.</p>
      </div>
      <div>
        <button class="wpask-btn wpask-btn-primary" @click="saveSettings">
          Save changes
        </button>
      </div>
    </div>

    <div class="wpask-settings-sections">
      
      <!-- General Settings -->
      <section class="wpask-settings-section">
        <div>
          <h2 class="wpask-settings-section-title">General</h2>
          <p class="wpask-settings-section-desc">Applies to any new survey you create.</p>
        </div>
        
        <div class="wpask-settings-fields">
          <!-- Brand Color -->
          <div>
            <div class="wpask-field-label">Brand color</div>
            <p class="wpask-field-hint">Used for accents on published surveys.</p>
            <div class="wpask-field-control wpask-color-row">
              <label class="wpask-color-swatch">
                <div class="wpask-color-preview" :style="{ background: settings.default_color || '#6366f1' }"></div>
                <input type="color" v-model="settings.default_color" />
              </label>
              <code class="wpask-color-code">{{ (settings.default_color || '#6366f1').toUpperCase() }}</code>
            </div>
          </div>

          <!-- Widget Position -->
          <div>
            <div class="wpask-field-label">Widget position</div>
            <p class="wpask-field-hint">Where the survey widget appears on the page.</p>
            <div class="wpask-field-control wpask-position-toggle">
              <button
                v-for="pos in ['left', 'center', 'right']"
                :key="pos"
                class="wpask-pos-btn"
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
      <section class="wpask-settings-section">
        <div>
          <h2 class="wpask-settings-section-title">Access control</h2>
          <p class="wpask-settings-section-desc">Choose which roles can manage surveys.</p>
        </div>
        
        <div class="wpask-settings-fields">
          <div>
            <div class="wpask-field-label">Minimum role</div>
            <div class="wpask-field-control">
              <select class="wpask-select" v-model="settings.minimum_role">
                <option value="administrator">Administrator</option>
                <option value="editor">Editor</option>
                <option value="author">Author</option>
                <option value="contributor">Contributor</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <!-- Data Handling -->
      <section class="wpask-settings-section">
        <div>
          <h2 class="wpask-settings-section-title">Data handling</h2>
          <p class="wpask-settings-section-desc">Control what happens on plugin removal.</p>
        </div>
        
        <div class="wpask-settings-fields">
          <label class="wpask-toggle-row">
            <input type="checkbox" v-model="settings.delete_on_uninstall" />
            <div>
              <div class="wpask-toggle-row-label">Delete all data on uninstall</div>
              <p class="wpask-toggle-row-hint">Removes surveys, responses and settings. This cannot be undone.</p>
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
