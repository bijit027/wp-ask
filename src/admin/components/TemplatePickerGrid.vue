<template>
  <div class="wpask-template-picker">
    <div v-if="loading" class="wpask-template-loading">Loading templates…</div>

    <div v-else class="wpask-template-grid">
      <button
        v-for="template in templates"
        :key="template.id"
        type="button"
        class="wpask-template-card"
        :class="{
          'wpask-template-card--selected': selectedId === template.id,
          'wpask-template-card--locked': !template.is_available,
        }"
        :disabled="!template.is_available"
        @click="selectTemplate(template)"
      >
        <div class="wpask-template-card-icon" :class="{ 'wpask-template-card-icon--locked': !template.is_available }">
          <component :is="iconFor(template.icon)" />
          <span v-if="!template.is_available" class="wpask-template-lock-badge">
            <Lock />
          </span>
        </div>

        <div class="wpask-template-card-body">
          <div class="wpask-template-card-title-row">
            <span class="wpask-template-card-title">{{ template.title }}</span>
            <span v-if="template.is_pro" class="wpask-template-pro-badge">Pro</span>
          </div>
          <p class="wpask-template-card-desc">{{ template.description }}</p>
          <span class="wpask-template-card-meta">
            {{ template.questions?.length || 0 }} questions
          </span>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {
  Lock,
  FilePlus,
  Globe,
  TrendingUp,
  ShoppingCart,
  Mail,
  Store,
  Headphones,
  LayoutTemplate,
} from 'lucide-vue-next';

const props = defineProps({
  selectedId: { type: String, default: '' },
  highlightIds: { type: Array, default: () => [] },
});

const emit = defineEmits(['select', 'loaded']);

const loading = ref(true);
const templates = ref([]);

const iconMap = {
  'file-plus': FilePlus,
  globe: Globe,
  'trending-up': TrendingUp,
  'shopping-cart': ShoppingCart,
  mail: Mail,
  store: Store,
  headphones: Headphones,
};

function iconFor(name) {
  return iconMap[name] || LayoutTemplate;
}

function selectTemplate(template) {
  if (!template.is_available) {
    if (template.upgrade_url) {
      window.open(template.upgrade_url, '_blank', 'noopener,noreferrer');
    }
    return;
  }
  emit('select', template);
}

async function fetchTemplates() {
  const config = window.WPAskAdminConfig || {};

  try {
    const res = await fetch(`${config.api_url}/survey-templates`, {
      headers: { 'X-WP-Nonce': config.nonce },
    });

    if (res.ok) {
      let data = await res.json();
      if (props.highlightIds.length > 0) {
        data = [...data].sort((a, b) => {
          const aHighlight = props.highlightIds.includes(a.id) ? 0 : 1;
          const bHighlight = props.highlightIds.includes(b.id) ? 0 : 1;
          return aHighlight - bHighlight;
        });
      }
      templates.value = data;
      emit('loaded', data);
    }
  } catch (err) {
    console.error('Failed to load templates', err);
  } finally {
    loading.value = false;
  }
}

onMounted(fetchTemplates);
</script>
