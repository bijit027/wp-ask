<template>
  <div class="survey-builder">
    <div class="builder-header">
      <div class="title-edit">
        <el-input 
          v-model="survey.title" 
          placeholder="Enter Survey Name..." 
          size="large"
          class="survey-title-input"
        >
          <template #prefix>
            <el-icon><Edit /></el-icon>
          </template>
        </el-input>
      </div>
      <div class="actions">
        <el-tag :type="survey.status === 'publish' ? 'success' : 'info'" size="large" class="status-tag">
          {{ survey.status === 'publish' ? 'Active' : 'Draft' }}
        </el-tag>
        <el-button @click="saveDraft">Save Draft</el-button>
        <el-button type="primary" @click="publishSurvey" :icon="Check">Publish</el-button>
      </div>
    </div>

    <div class="builder-body">
      <!-- Left Column: Controls -->
      <div class="builder-controls">
        <el-tabs v-model="activeTab" class="builder-tabs">
          
          <!-- DESIGN TAB -->
          <el-tab-pane label="Design" name="design">
            <div class="design-section">
              <h3>Questions</h3>
              <p class="help-text">Drag to reorder. The first question is always shown first.</p>
              
              <draggable 
                v-model="survey.questions" 
                item-key="id"
                handle=".drag-handle"
                class="question-list"
              >
                <template #item="{ element, index }">
                  <el-card class="question-card" shadow="hover">
                    <div class="question-header">
                      <span class="drag-handle"><el-icon><Rank /></el-icon></span>
                      <span class="question-type-badge">{{ element.type }}</span>
                      <el-button type="danger" link @click="removeQuestion(index)"><el-icon><Delete /></el-icon></el-button>
                    </div>
                    <el-input v-model="element.label" placeholder="Question Text" />
                    <div class="question-settings">
                      <el-checkbox v-model="element.required">Required</el-checkbox>
                    </div>
                  </el-card>
                </template>
              </draggable>

              <el-dropdown @command="addQuestion" trigger="click" class="add-question-btn">
                <el-button type="dashed" class="full-width">
                  + Add Question
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="radio">Multiple Choice (Radio)</el-dropdown-item>
                    <el-dropdown-item command="checkbox">Multiple Choice (Checkbox)</el-dropdown-item>
                    <el-dropdown-item command="textarea">Long Text</el-dropdown-item>
                    <el-dropdown-item command="rating">Star Rating</el-dropdown-item>
                    <el-dropdown-item command="nps">Net Promoter Score (NPS)</el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </el-tab-pane>

          <!-- SETTINGS TAB -->
          <el-tab-pane label="Settings" name="settings">
            <el-form label-position="top">
              <el-form-item label="Widget Color">
                <el-color-picker v-model="survey.settings.color" />
              </el-form-item>
              
              <el-form-item label="Position on Screen">
                <el-radio-group v-model="survey.settings.position">
                  <el-radio-button label="bottom-left">Bottom Left</el-radio-button>
                  <el-radio-button label="bottom-right">Bottom Right</el-radio-button>
                  <el-radio-button label="bottom-center">Bottom Center</el-radio-button>
                </el-radio-group>
              </el-form-item>

              <el-divider />

              <h3>Confirmation Message</h3>
              <el-form-item label="Message to show after submission">
                <el-input 
                  type="textarea" 
                  v-model="survey.settings.confirmation.message" 
                  rows="3"
                />
              </el-form-item>
            </el-form>
          </el-tab-pane>

          <!-- TARGETING TAB -->
          <el-tab-pane label="Targeting" name="targeting">
            <el-alert
              title="Where should this survey appear?"
              type="info"
              show-icon
              :closable="false"
              class="mb-20"
            />
            <el-form label-position="top">
              <el-form-item label="Match Type">
                <el-radio-group v-model="survey.targeting.rule_match">
                  <el-radio label="all">Match ALL rules</el-radio>
                  <el-radio label="any">Match ANY rule</el-radio>
                </el-radio-group>
              </el-form-item>

              <div v-for="(rule, index) in survey.targeting.rules" :key="index" class="rule-row">
                <el-select v-model="rule.type" placeholder="Rule Type" style="width: 150px">
                  <el-option label="URL" value="url" />
                  <el-option label="Post Type" value="post_type" />
                  <el-option label="User Status" value="user_status" />
                </el-select>
                
                <el-select v-model="rule.operator" style="width: 120px">
                  <el-option label="Is" value="is" />
                  <el-option label="Is Not" value="is_not" />
                  <el-option label="Contains" value="contains" v-if="rule.type === 'url'" />
                </el-select>

                <el-input v-model="rule.value" placeholder="Value..." style="flex: 1" />
                
                <el-button type="danger" plain icon="Delete" @click="removeRule(index)" circle />
              </div>

              <el-button @click="addRule" type="dashed" class="mt-10">+ Add Rule</el-button>
            </el-form>
          </el-tab-pane>
        </el-tabs>
      </div>

      <!-- Right Column: Live Preview -->
      <div class="builder-preview">
        <div class="preview-wrapper">
          <div class="browser-chrome">
            <div class="dots"><span></span><span></span><span></span></div>
            <div class="url-bar">example.com</div>
          </div>
          <div class="preview-content">
            <!-- Simulated Widget -->
            <div 
              class="live-widget-preview" 
              :class="survey.settings.position"
            >
              <div class="widget-header" :style="{ backgroundColor: survey.settings.color }">
                <span>{{ survey.title || 'Survey' }}</span>
                <el-icon><Close /></el-icon>
              </div>
              <div class="widget-body" v-if="survey.questions.length > 0">
                <p class="question-label">{{ survey.questions[0].label }}</p>
                <div v-if="survey.questions[0].type === 'textarea'" class="preview-textarea"></div>
                <div v-if="survey.questions[0].type === 'rating'" class="preview-rating">⭐⭐⭐⭐⭐</div>
                <div v-if="survey.questions[0].type === 'radio'" class="preview-radio">
                  <label><input type="radio" disabled> Option 1</label>
                  <label><input type="radio" disabled> Option 2</label>
                </div>
                <el-button type="primary" class="preview-btn" :style="{ backgroundColor: survey.settings.color, borderColor: survey.settings.color }">Next</el-button>
              </div>
              <div class="widget-body empty-state" v-else>
                <el-empty description="Add questions to see preview" :image-size="60" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Edit, Check, Delete, Rank, Close } from '@element-plus/icons-vue';
import draggable from 'vuedraggable';

const router = useRouter();
const route = useRoute();
const activeTab = ref('design');

// Initial State
const survey = reactive({
  title: 'My New Survey',
  status: 'draft',
  type: 'floating',
  questions: [
    { id: 'q1', type: 'rating', label: 'How would you rate your experience?', required: true }
  ],
  settings: {
    color: '#4F46E5',
    position: 'bottom-right',
    confirmation: { type: 'message', message: 'Thank you for your feedback!' }
  },
  targeting: {
    rule_match: 'all',
    rules: []
  }
});

const addQuestion = (type) => {
  survey.questions.push({
    id: 'q' + Date.now(),
    type: type,
    label: 'New Question',
    required: false
  });
};

const removeQuestion = (index) => {
  survey.questions.splice(index, 1);
};

const addRule = () => {
  survey.targeting.rules.push({ type: 'url', operator: 'is', value: '' });
};

const removeRule = (index) => {
  survey.targeting.rules.splice(index, 1);
};

const saveDraft = () => {
  survey.status = 'draft';
  // API Call goes here
  console.log('Saving draft...', survey);
};

const publishSurvey = () => {
  survey.status = 'publish';
  // API Call goes here
  console.log('Publishing...', survey);
};
</script>

<style scoped>
.survey-builder {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: calc(100vh - 120px);
}

.builder-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 15px 25px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.title-edit {
  width: 400px;
}

.survey-title-input :deep(.el-input__wrapper) {
  box-shadow: none;
  font-size: 20px;
  font-weight: 600;
  padding-left: 0;
}

.survey-title-input :deep(.el-input__wrapper.is-focus) {
  box-shadow: 0 0 0 1px #4F46E5 inset;
  padding-left: 11px;
}

.actions {
  display: flex;
  gap: 15px;
  align-items: center;
}

.status-tag {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.builder-body {
  display: flex;
  gap: 20px;
  flex: 1;
  min-height: 0;
}

.builder-controls {
  flex: 0 0 450px;
  background: white;
  border-radius: 8px;
  padding: 0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.builder-tabs {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.builder-tabs :deep(.el-tabs__header) {
  margin: 0;
  padding: 0 20px;
  background: #fafafa;
  border-bottom: 1px solid #ebeef5;
}

.builder-tabs :deep(.el-tabs__content) {
  flex: 1;
  overflow-y: auto;
  padding: 25px;
}

.help-text {
  color: #606266;
  font-size: 13px;
  margin-bottom: 20px;
}

.question-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-bottom: 20px;
}

.question-card :deep(.el-card__body) {
  padding: 15px;
}

.question-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.drag-handle {
  cursor: grab;
  color: #909399;
}

.question-type-badge {
  background: #f0f2f5;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #606266;
  text-transform: uppercase;
  font-weight: 600;
  flex: 1;
}

.question-settings {
  margin-top: 10px;
  display: flex;
  justify-content: flex-end;
}

.full-width {
  width: 100%;
}

.rule-row {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  align-items: center;
}

.mb-20 { margin-bottom: 20px; }
.mt-10 { margin-top: 10px; }

/* Preview Pane */
.builder-preview {
  flex: 1;
  background: #e5e7eb;
  border-radius: 8px;
  padding: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preview-wrapper {
  background: white;
  width: 100%;
  max-width: 800px;
  height: 600px;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
}

.browser-chrome {
  background: #f3f4f6;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  gap: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.dots {
  display: flex;
  gap: 6px;
}

.dots span {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #d1d5db;
}

.dots span:nth-child(1) { background: #ef4444; }
.dots span:nth-child(2) { background: #f59e0b; }
.dots span:nth-child(3) { background: #10b981; }

.url-bar {
  background: white;
  padding: 4px 15px;
  border-radius: 4px;
  font-size: 13px;
  color: #6b7280;
  flex: 1;
  max-width: 300px;
  text-align: center;
}

.preview-content {
  flex: 1;
  position: relative;
  background: url('data:image/svg+xml;utf8,<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><circle cx="2" cy="2" r="1" fill="%23e5e7eb"/></svg>');
}

/* Simulated Widget */
.live-widget-preview {
  position: absolute;
  width: 320px;
  background: white;
  border-radius: 12px 12px 0 0;
  box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: all 0.3s ease;
}

.live-widget-preview.bottom-right { bottom: 0; right: 30px; }
.live-widget-preview.bottom-left { bottom: 0; left: 30px; }
.live-widget-preview.bottom-center { bottom: 0; left: 50%; transform: translateX(-50%); }

.widget-header {
  padding: 12px 20px;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  font-size: 15px;
}

.widget-body {
  padding: 20px;
}

.question-label {
  margin: 0 0 15px 0;
  font-size: 15px;
  font-weight: 500;
  color: #1f2937;
}

.preview-textarea {
  height: 60px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #f9fafb;
  margin-bottom: 15px;
}

.preview-rating {
  font-size: 24px;
  margin-bottom: 15px;
  letter-spacing: 5px;
}

.preview-radio {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 15px;
}

.preview-radio label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #4b5563;
  font-size: 14px;
}

.preview-btn {
  width: 100%;
}

.empty-state {
  display: flex;
  justify-content: center;
  padding: 40px 0;
}
</style>
