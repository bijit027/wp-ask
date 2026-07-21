<template>
  <Teleport to="body">
    <div v-if="visible" class="wpask-modal-overlay" @click.self="close">
      <div class="wpask-modal" role="dialog" aria-modal="true" aria-labelledby="wpask-template-modal-title">
        <div class="wpask-modal-header">
          <div>
            <h2 id="wpask-template-modal-title" class="wpask-modal-title">Create a new survey</h2>
            <p class="wpask-modal-subtitle">Pick a template to get started quickly, or start from scratch.</p>
          </div>
          <button type="button" class="wpask-icon-btn" aria-label="Close" @click="close">
            <X />
          </button>
        </div>

        <div class="wpask-modal-body">
          <TemplatePickerGrid
            :selected-id="selectedId"
            @select="onSelect"
          />
        </div>

        <div class="wpask-modal-footer">
          <button type="button" class="wpask-btn wpask-btn-secondary" @click="close">Cancel</button>
          <button
            type="button"
            class="wpask-btn wpask-btn-primary"
            :disabled="!selectedId"
            @click="confirmSelection"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { X } from 'lucide-vue-next';
import TemplatePickerGrid from './TemplatePickerGrid.vue';

const props = defineProps({
  visible: { type: Boolean, default: false },
});

const emit = defineEmits(['update:visible', 'selected']);

const router = useRouter();
const selectedId = ref('');
const selectedTemplate = ref(null);

watch(
  () => props.visible,
  (open) => {
    if (open) {
      selectedId.value = '';
      selectedTemplate.value = null;
    }
  }
);

function close() {
  emit('update:visible', false);
}

function onSelect(template) {
  selectedId.value = template.id;
  selectedTemplate.value = template;
}

function confirmSelection() {
  if (!selectedTemplate.value) return;

  emit('selected', selectedTemplate.value);
  close();

  const query = selectedTemplate.value.id === 'blank'
    ? {}
    : { template: selectedTemplate.value.id };

  router.push({ path: '/surveys/new', query });
}
</script>
