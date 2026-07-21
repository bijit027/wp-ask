<template>
  <div class="wpask-editor-wrap">
    <!-- Sub-header -->
    <div class="wpask-editor-subheader">
      <div class="wpask-editor-subheader-inner">
        <!-- Left: back + title + status -->
        <div class="wpask-editor-title-row">
          <button class="wpask-editor-back-btn" @click="$router.push('/')" aria-label="Back to surveys">
            <ArrowLeft />
          </button>
          <input
            v-model="survey.title"
            class="wpask-editor-title-input"
            placeholder="Untitled survey"
          />
          <span class="wpask-status-chip" :class="survey.status">
            <Circle />
            {{ survey.status === 'publish' ? 'Published' : 'Draft' }}
          </span>
        </div>

        <!-- Right: actions -->
        <div class="wpask-editor-actions">
          <button class="wpask-btn wpask-btn-ghost wpask-btn-sm" @click="previewSurvey" v-if="route.params.id">
            Preview
          </button>
          <button class="wpask-btn wpask-btn-secondary wpask-btn-sm" @click="saveDraft">
            Save draft
          </button>
          <button class="wpask-btn wpask-btn-primary wpask-btn-sm" @click="publishSurvey">
            <Check />
            Publish
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="wpask-editor-tabs">
        <button
          v-for="t in editorTabs"
          :key="t"
          class="wpask-editor-tab"
          :class="{ active: activeTab === t }"
          @click="activeTab = t"
        >{{ t }}</button>
      </div>
    </div>

    <!-- Editor body -->
    <div class="wpask-editor-body">

      <!-- ====== DESIGN TAB ====== -->
      <template v-if="activeTab === 'design'">
        <!-- Left: Questions list -->
        <aside class="wpask-questions-panel">
          <div class="wpask-questions-header">
            <div>
              <div class="wpask-questions-title">Questions</div>
              <p class="wpask-questions-subtitle">Drag to reorder</p>
            </div>
            <span class="wpask-q-count">{{ survey.questions.length }}</span>
          </div>

          <ul class="wpask-q-list">
            <li
              v-for="(q, i) in survey.questions"
              :key="q.id"
              class="wpask-q-item"
              :class="{ active: activeQuestionId === q.id }"
              @click="activeQuestionId = q.id"
            >
              <GripVertical class="wpask-q-grip" />
              <div class="wpask-q-type-icon">
                <component :is="questionIcon(q.type)" />
              </div>
              <div style="min-width:0">
                <div class="wpask-q-label">{{ q.label || 'Untitled' }}</div>
                <p class="wpask-q-meta">{{ q.type }} · #{{ i + 1 }}</p>
              </div>
              <button
                class="wpask-q-remove"
                @click.stop="removeQuestion(i)"
                aria-label="Remove question"
              >
                <X />
              </button>
            </li>
          </ul>

          <!-- Add question types -->
          <div class="wpask-add-question-section">
            <p class="wpask-add-q-label">Add question</p>
            <div class="wpask-q-type-grid">
              <button
                v-for="qt in questionTypes"
                :key="qt.type"
                class="wpask-q-type-btn"
                @click="addQuestion(qt.type)"
              >
                <component :is="qt.icon" />
                {{ qt.label }}
              </button>
            </div>
          </div>
        </aside>

        <!-- Center: Live preview -->
        <section class="wpask-preview-panel">
          <div class="wpask-preview-toolbar">
            <span class="wpask-preview-label">Live preview</span>
            <div class="wpask-device-toggle">
              <button
                class="wpask-device-btn"
                :class="{ active: previewDevice === 'desktop' }"
                @click="previewDevice = 'desktop'"
                aria-label="Desktop"
              >
                <Monitor />
              </button>
              <button
                class="wpask-device-btn"
                :class="{ active: previewDevice === 'mobile' }"
                @click="previewDevice = 'mobile'"
                aria-label="Mobile"
              >
                <Smartphone />
              </button>
            </div>
          </div>

          <div
            class="wpask-browser-chrome-wrap"
            :class="{ mobile: previewDevice === 'mobile' }"
          >
            <!-- Browser chrome -->
            <div class="wpask-browser-bar">
              <span class="wpask-browser-dot" style="background:#f87171;" />
              <span class="wpask-browser-dot" style="background:#fbbf24;" />
              <span class="wpask-browser-dot" style="background:#34d399;" />
              <div class="wpask-browser-url">example.com</div>
            </div>

            <!-- Preview viewport -->
            <div class="wpask-preview-viewport">
              <div class="wpask-widget-preview">
                <div class="wpask-widget-header" :style="{ backgroundColor: survey.settings?.color || '#6366f1' }">
                  <span class="wpask-widget-header-title">{{ survey.title || 'Survey' }}</span>
                  <X />
                </div>
                <div class="wpask-widget-body">
                  <div class="wpask-widget-question">{{ activeQuestion?.label || 'New question' }}</div>

                  <!-- Rating -->
                  <div class="wpask-preview-stars" v-if="activeQuestion?.type === 'rating'">
                    <Star v-for="n in 5" :key="n" />
                  </div>

                  <!-- NPS -->
                  <div class="wpask-nps-grid" v-else-if="activeQuestion?.type === 'nps'">
                    <div class="wpask-nps-cell" v-for="n in 11" :key="n">{{ n - 1 }}</div>
                  </div>

                  <!-- Text / Textarea -->
                  <textarea
                    v-else-if="activeQuestion?.type === 'textarea' || activeQuestion?.type === 'text'"
                    class="wpask-preview-textarea"
                    placeholder="Type your answer…"
                    readonly
                  />

                  <!-- Yes/No -->
                  <div class="wpask-yesno-grid" v-else-if="activeQuestion?.type === 'yesno'">
                    <button class="wpask-yesno-btn">Yes</button>
                    <button class="wpask-yesno-btn">No</button>
                  </div>

                  <!-- Multiple choice -->
                  <div class="wpask-choice-list" v-else-if="activeQuestion?.type === 'choice' || activeQuestion?.type === 'radio'">
                    <div
                      class="wpask-choice-item"
                      v-for="(opt, i) in (activeQuestion.options || ['Option 1', 'Option 2'])"
                      :key="i"
                    >
                      <Circle />
                      {{ opt }}
                    </div>
                  </div>

                  <!-- Checkboxes -->
                  <div class="wpask-choice-list" v-else-if="activeQuestion?.type === 'checkbox'">
                    <div
                      class="wpask-choice-item"
                      v-for="(opt, i) in (activeQuestion.options || ['Option 1', 'Option 2'])"
                      :key="i"
                    >
                      <div style="width:16px; height:16px; border:2px solid #d1d5db; border-radius:3px; display:flex; align-items:center; justify-content:center;">
                        <Check style="width:12px; height:12px; color:var(--primary);" />
                      </div>
                      {{ opt }}
                    </div>
                  </div>

                  <!-- Dropdown -->
                  <div v-else-if="activeQuestion?.type === 'dropdown'">
                    <select style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                      <option>Select an option...</option>
                      <option v-for="(opt, i) in (activeQuestion.options || ['Option 1', 'Option 2'])" :key="i">{{ opt }}</option>
                    </select>
                  </div>

                  <!-- Date -->
                  <div v-else-if="activeQuestion?.type === 'date'">
                    <input type="date" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                  </div>

                  <!-- Email -->
                  <div v-else-if="activeQuestion?.type === 'email'">
                    <input type="email" placeholder="user@example.com" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                  </div>

                  <!-- Number -->
                  <div v-else-if="activeQuestion?.type === 'number'">
                    <input type="number" placeholder="0" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-size:14px;">
                  </div>

                  <button class="wpask-widget-next-btn">Next</button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Right: Inspector -->
        <aside class="wpask-inspector-panel">
          <div class="wpask-inspector-header">
            <div class="wpask-inspector-icon">
              <Hash />
            </div>
            <div>
              <div class="wpask-inspector-label">Question</div>
              <div class="wpask-inspector-title">Properties</div>
            </div>
          </div>

          <div class="wpask-inspector-fields" v-if="activeQuestion">
            <!-- Type -->
            <div class="wpask-field">
              <label>Type</label>
              <select v-model="activeQuestion.type">
                <option v-for="qt in questionTypes" :key="qt.type" :value="qt.type">{{ qt.label }}</option>
              </select>
            </div>

            <!-- Question text -->
            <div class="wpask-field">
              <label>Question</label>
              <input v-model="activeQuestion.label" placeholder="Enter your question..." />
            </div>

            <!-- Options for choice/checkbox/dropdown type -->
            <div class="wpask-field" v-if="activeQuestion.type === 'choice' || activeQuestion.type === 'radio' || activeQuestion.type === 'checkbox' || activeQuestion.type === 'dropdown'">
              <label>Options</label>
              <div class="wpask-options-list">
                <div
                  class="wpask-option-row"
                  v-for="(opt, i) in (activeQuestion.options || [])"
                  :key="i"
                >
                  <input
                    :value="opt"
                    @input="updateOption(i, $event.target.value)"
                    placeholder="Option text"
                  />
                  <button class="wpask-icon-btn danger" @click="removeOption(i)">
                    <X />
                  </button>
                </div>
                <button
                  class="wpask-add-rule-btn"
                  @click="addOption"
                  style="margin-top:6px;"
                >
                  <Plus /> Add option
                </button>
              </div>
            </div>

            <!-- Required -->
            <label class="wpask-toggle-row">
              <input type="checkbox" v-model="activeQuestion.required" />
              <div>
                <div class="wpask-toggle-row-label">Required</div>
                <p class="wpask-toggle-row-hint">Users must answer before moving on.</p>
              </div>
            </label>

            <hr class="wpask-inspector-divider" />

            <!-- Conditional Logic -->
            <div class="wpask-field">
              <label class="wpask-toggle-row" style="margin-bottom:12px;">
                <input type="checkbox" v-model="activeQuestion.logic.enabled" />
                <div>
                  <div class="wpask-toggle-row-label">Enable Conditional Logic</div>
                  <p class="wpask-toggle-row-hint">Show/hide this question based on previous answers.</p>
                </div>
              </label>

              <template v-if="activeQuestion.logic.enabled">
                <div style="margin-top:12px;">
                  <label style="font-size:13px; font-weight:500; color:var(--foreground); margin-bottom:8px !important; display:block;">Action</label>
                  <select v-model="activeQuestion.logic.action" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;">
                    <option value="show">Show this question if conditions match</option>
                    <option value="hide">Hide this question if conditions match</option>
                    <option value="skip">Skip to next question if conditions match</option>
                  </select>
                </div>

                <div style="margin-top:16px;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <label style="font-size:13px; font-weight:500; color:var(--foreground); margin:0;">Conditions</label>
                    <button class="wpask-add-rule-btn" @click="addLogicCondition" style="margin:0; padding:4px 8px; font-size:12px;">
                      <Plus style="width:14px; height:14px;" /> Add condition
                    </button>
                  </div>
                  
                  <div v-if="activeQuestion.logic.conditions.length === 0" style="color:var(--muted-foreground); font-size:12px; padding:12px; background:var(--muted); border-radius:6px;">
                    No conditions added. Add at least one condition.
                  </div>

                  <div v-for="(cond, idx) in activeQuestion.logic.conditions" :key="idx" class="wpask-rule-row" style="margin-top:8px;">
                    <select v-model="cond.questionId" style="width:140px; font-size:12px;" @change="updateConditionOptions(idx)">
                      <option value="">Select question...</option>
                      <option v-for="q in getPreviousQuestions()" :key="q.id" :value="q.id">{{ q.label }}</option>
                    </select>
                    <select v-model="cond.operator" style="width:100px; font-size:12px;">
                      <option value="=">Is</option>
                      <option value="!=">Is not</option>
                      <option value="in">Includes</option>
                    </select>
                    <select v-model="cond.value" style="width:120px; font-size:12px;">
                      <option value="">Select value...</option>
                      <option v-for="opt in getConditionOptions(cond.questionId)" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                    <button class="wpask-icon-btn danger" @click="removeLogicCondition(idx)" style="margin-left:8px;">
                      <X style="width:14px; height:14px;" />
                    </button>
                  </div>
                </div>
              </template>
            </div>

            <hr class="wpask-inspector-divider" />

            <button
              class="wpask-btn wpask-btn-danger"
              style="font-size:13px; padding: 6px 0;"
              @click="removeQuestion(activeQuestionIndex)"
            >
              <Trash2 />
              Delete question
            </button>
          </div>

          <div v-else style="color: var(--muted-foreground); font-size:13px; text-align:center; padding: 24px 0;">
            Select a question to edit its properties.
          </div>
        </aside>
      </template>

      <!-- ====== SETTINGS TAB ====== -->
      <template v-if="activeTab === 'settings'">
        <div style="grid-column: 1 / -1;">
          <div class="wpask-builder-tab-content">
            <div class="wpask-field">
              <label style="font-size:13px; font-weight:500; color:var(--foreground); margin-bottom:8px !important; display:block;">Brand color</label>
              <p style="font-size:12px; color:var(--muted-foreground); margin-bottom:10px !important;">Used for accents on published surveys.</p>
              <div class="wpask-color-row">
                <label class="wpask-color-swatch">
                  <div class="wpask-color-preview" :style="{ background: survey.settings?.color || '#6366f1' }"></div>
                  <input type="color" v-model="survey.settings.color" />
                </label>
                <code class="wpask-color-code">{{ (survey.settings?.color || '#6366f1').toUpperCase() }}</code>
              </div>
            </div>

            <div class="wpask-field" style="margin-top:24px;">
              <label style="font-size:13px; font-weight:500; color:var(--foreground); margin-bottom:8px !important; display:block;">Widget position</label>
              <p style="font-size:12px; color:var(--muted-foreground); margin-bottom:10px !important;">Where the survey widget appears on the page.</p>
              <div class="wpask-position-toggle">
                <button
                  v-for="pos in ['bottom-left', 'bottom-center', 'bottom-right']"
                  :key="pos"
                  class="wpask-pos-btn"
                  :class="{ active: survey.settings?.position === pos }"
                  @click="survey.settings.position = pos"
                >{{ pos }}</button>
              </div>
            </div>

            <div class="wpask-field" style="margin-top:24px;">
              <label style="font-size:13px; font-weight:500; color:var(--foreground); margin-bottom:8px !important; display:block;">Confirmation message</label>
              <input v-model="survey.settings.confirmation.message" placeholder="Thank you for your feedback!" style="max-width:480px;" />
            </div>

            <div class="wpask-field" style="margin-top:24px;">
              <label style="font-size:13px; font-weight:500; color:var(--foreground); margin-bottom:8px !important; display:block;">Schedule Publish (Optional)</label>
              <p style="font-size:12px; color:var(--muted-foreground); margin-bottom:10px !important;">Set a future date and time for the survey to go live.</p>
              <input type="datetime-local" v-model="survey.publish_at" style="max-width:480px;" />
            </div>
          </div>
        </div>
      </template>

      <!-- ====== TARGETING TAB ====== -->
      <template v-if="activeTab === 'targeting'">
        <div style="grid-column: 1 / -1;">
          <div class="wpask-builder-tab-content">
            <div class="wpask-field">
              <label style="font-size:13px; font-weight:500; color:var(--foreground); margin-bottom:8px !important; display:block;">Match type</label>
              <div class="wpask-position-toggle">
                <button
                  class="wpask-pos-btn"
                  :class="{ active: survey.targeting?.rule_match === 'all' }"
                  @click="survey.targeting.rule_match = 'all'"
                >Match ALL rules</button>
                <button
                  class="wpask-pos-btn"
                  :class="{ active: survey.targeting?.rule_match === 'any' }"
                  @click="survey.targeting.rule_match = 'any'"
                >Match ANY rule</button>
              </div>
            </div>

            <div v-for="(rule, index) in survey.targeting.rules" :key="index" class="wpask-rule-row" style="margin-top:10px;">
              <select v-model="rule.type" style="width:150px;" @change="rule.value = ''">
                <option value="url">URL</option>
                <option value="post_type">Post Type</option>
                <option value="user_status">User Status</option>
              </select>
              <select v-model="rule.operator" style="width:120px;">
                <option value="is">Is</option>
                <option value="is_not">Is Not</option>
                <option value="contains" v-if="rule.type === 'url'">Contains</option>
              </select>

              <select v-if="rule.type === 'post_type'" v-model="rule.value" style="flex:1;">
                <option value="" disabled>Select post type...</option>
                <option v-for="pt in logicOptions.post_types" :key="pt.value" :value="pt.value">{{ pt.label }}</option>
              </select>

              <select v-else-if="rule.type === 'user_status'" v-model="rule.value" style="flex:1;">
                <option value="" disabled>Select status...</option>
                <option v-for="st in logicOptions.user_status" :key="st.value" :value="st.value">{{ st.label }}</option>
              </select>

              <input v-else v-model="rule.value" placeholder="Value..." style="flex:1;" />

              <button class="wpask-icon-btn danger" @click="removeRule(index)">
                <Trash2 />
              </button>
            </div>

            <button class="wpask-add-rule-btn" @click="addRule" style="margin-top:12px;">
              <Plus /> Add Rule
            </button>
          </div>
        </div>
      </template>

      <!-- ====== NOTIFICATIONS TAB ====== -->
      <template v-if="activeTab === 'notifications'">
        <div style="grid-column: 1 / -1;">
          <div class="wpask-builder-tab-content">
            <div class="wpask-notif-alert">
              Send an email when someone fills out this survey.
            </div>

            <div class="wpask-switch-row">
              <span class="wpask-switch-label">Enable Email Notifications</span>
              <label class="wpask-switch">
                <input type="checkbox" v-model="survey.notifications.email.active" />
                <span class="wpask-switch-slider"></span>
              </label>
            </div>

            <div v-if="survey.notifications.email.active" style="margin-top:20px;">
              <div class="wpask-field">
                <label style="font-size:13px; font-weight:500; color:var(--foreground); margin-bottom:6px !important; display:block;">Email Addresses</label>
                <input
                  v-model="survey.notifications.email.addresses"
                  placeholder="admin@example.com, author@example.com"
                  style="max-width:480px;"
                />
                <p class="wpask-help-text">Comma-separated list of email addresses.</p>
              </div>
            </div>
          </div>
        </div>
      </template>

    </div><!-- end editor-body -->
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
  ArrowLeft, Check, X, Circle, GripVertical,
  Star, TrendingUp, Type, ListChecks, ToggleLeft,
  Monitor, Smartphone, Hash, Plus, Trash2,
} from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const activeTab = ref('design');
const previewDevice = ref('desktop');
const activeQuestionId = ref(null);

const editorTabs = ['design', 'settings', 'targeting', 'notifications'];

const logicOptions = ref({ post_types: [], user_status: [] });

const questionTypes = [
  { type: 'rating', label: 'Rating', icon: Star },
  { type: 'nps', label: 'NPS', icon: TrendingUp },
  { type: 'text', label: 'Short text', icon: Type },
  { type: 'email', label: 'Email', icon: Type },
  { type: 'number', label: 'Number', icon: Type },
  { type: 'radio', label: 'Multiple choice', icon: ListChecks },
  { type: 'checkbox', label: 'Checkboxes', icon: ListChecks },
  { type: 'dropdown', label: 'Dropdown', icon: ListChecks },
  { type: 'date', label: 'Date', icon: Type },
  { type: 'yesno', label: 'Yes / No', icon: ToggleLeft },
];

const questionIcon = (type) => {
  const found = questionTypes.find(qt => qt.type === type);
  return found ? found.icon : Type;
};

// Initial State
const survey = reactive({
  title: 'My New Survey',
  status: 'draft',
  type: 'floating',
  questions: [
    { id: 'q1', type: 'rating', label: 'How would you rate your experience?', required: true }
  ],
  settings: {
    color: '#6366f1',
    position: 'bottom-right',
    confirmation: { type: 'message', message: 'Thank you for your feedback!' }
  },
  targeting: {
    rule_match: 'all',
    rules: []
  },
  notifications: {
    email: {
      active: false,
      addresses: '',
      logic: { enable: false, conditions: [] }
    }
  },
  publish_at: null
});

// Set initial active question
if (survey.questions.length > 0) {
  activeQuestionId.value = survey.questions[0].id;
}

const activeQuestion = computed(() =>
  survey.questions.find(q => q.id === activeQuestionId.value) || null
);

const activeQuestionIndex = computed(() =>
  survey.questions.findIndex(q => q.id === activeQuestionId.value)
);

const addQuestion = (type) => {
  const newQ = {
    id: 'q' + Date.now(),
    type,
    label: 'New question',
    required: false,
    options: (type === 'choice' || type === 'radio' || type === 'checkbox' || type === 'dropdown') ? ['Option 1', 'Option 2'] : undefined,
    logic: {
      enabled: false,
      action: 'show',
      conditions: []
    }
  };
  survey.questions.push(newQ);
  activeQuestionId.value = newQ.id;
};

const removeQuestion = (index) => {
  survey.questions.splice(index, 1);
  if (survey.questions.length > 0) {
    activeQuestionId.value = survey.questions[Math.max(0, index - 1)].id;
  } else {
    activeQuestionId.value = null;
  }
};

const addLogicCondition = () => {
  if (!activeQuestion.value.logic) {
    activeQuestion.value.logic = { enabled: true, action: 'show', conditions: [] };
  }
  activeQuestion.value.logic.conditions.push({ questionId: '', operator: '=', value: '' });
};

const removeLogicCondition = (index) => {
  activeQuestion.value.logic.conditions.splice(index, 1);
};

const getPreviousQuestions = () => {
  const currentIndex = survey.questions.findIndex(q => q.id === activeQuestionId.value);
  return survey.questions.slice(0, currentIndex);
};

const getConditionOptions = (questionId) => {
  const question = survey.questions.find(q => q.id === questionId);
  if (!question) return [];
  
  if (question.options && Array.isArray(question.options)) {
    return question.options;
  }
  
  if (question.type === 'rating') {
    return ['1', '2', '3', '4', '5'];
  }
  
  if (question.type === 'nps') {
    return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
  }
  
  if (question.type === 'yesno') {
    return ['Yes', 'No'];
  }
  
  return [];
};

const updateConditionOptions = (index) => {
  activeQuestion.value.logic.conditions[index].value = '';
};

const updateOption = (i, value) => {
  if (activeQuestion.value && activeQuestion.value.options) {
    activeQuestion.value.options[i] = value;
  }
};

const addOption = () => {
  if (activeQuestion.value) {
    if (!activeQuestion.value.options) activeQuestion.value.options = [];
    activeQuestion.value.options.push(`Option ${activeQuestion.value.options.length + 1}`);
  }
};

const removeOption = (i) => {
  if (activeQuestion.value?.options) {
    activeQuestion.value.options.splice(i, 1);
  }
};

const addRule = () => {
  survey.targeting.rules.push({ type: 'url', operator: 'is', value: '' });
};

const removeRule = (index) => {
  survey.targeting.rules.splice(index, 1);
};

const previewSurvey = () => {
  if (route.params.id) {
    const previewUrl = new URL(window.location.origin);
    previewUrl.searchParams.set('wpask_preview', route.params.id);
    window.open(previewUrl.toString(), '_blank');
  }
};

const saveDraft = async () => {
  survey.status = 'draft';
  await saveSurvey();
};

const publishSurvey = async () => {
  survey.status = 'publish';
  await saveSurvey();
};

const saveSurvey = async () => {
  const config = window.WPAskAdminConfig || {};
  const isEdit = !!route.params.id;
  const url = isEdit
    ? `${config.api_url}/surveys/${route.params.id}`
    : `${config.api_url}/surveys`;
  const method = isEdit ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.nonce
      },
      body: JSON.stringify(survey)
    });
    if (res.ok) {
      const data = await res.json();
      if (!isEdit) {
        router.push(`/surveys/${data.id}/edit`);
      }
      alert(`Survey ${survey.status === 'publish' ? 'published' : 'saved'} successfully!`);
    } else {
      alert('Failed to save survey. Check console.');
    }
  } catch (e) {
    console.error('Error saving survey', e);
  }
};

// Load existing survey if editing
onMounted(async () => {
  const config = window.WPAskAdminConfig || {};

  // Fetch logic options
  try {
    const logicRes = await fetch(`${config.api_url}/logic-type`, {
      headers: { 'X-WP-Nonce': config.nonce }
    });
    if (logicRes.ok) {
      logicOptions.value = await logicRes.json();
    }
  } catch (e) {
    console.error('Error fetching logic options', e);
  }

  if (route.params.id) {
    try {
      const res = await fetch(`${config.api_url}/surveys/${route.params.id}`, {
        headers: { 'X-WP-Nonce': config.nonce }
      });
      if (res.ok) {
        const data = await res.json();
        Object.assign(survey, data);

        // Ensure nested objects exist
        if (!survey.settings) survey.settings = { color: '#6366f1', position: 'bottom-right', confirmation: { type: 'message', message: 'Thank you for your feedback!' } };
        if (!survey.targeting) survey.targeting = { rule_match: 'all', rules: [] };
        if (!survey.notifications) {
          survey.notifications = { email: { active: false, addresses: '', logic: { enable: false, conditions: [] } } };
        } else if (!survey.notifications.email) {
          survey.notifications.email = { active: false, addresses: '', logic: { enable: false, conditions: [] } };
        }

        // Set first question active
        if (survey.questions?.length > 0) {
          activeQuestionId.value = survey.questions[0].id;
        }
      }
    } catch (e) {
      console.error('Failed to load survey', e);
    }
  }
});
</script>
