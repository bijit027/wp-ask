/**
 * WPAsk Frontend Entry Point
 * Lightweight Vanilla JS Widget using Shadow DOM
 */

class SurveyController {
  constructor(config) {
    this.config = config;
    this.survey = config.survey;
    this.apiUrl = config.api_url;
    this.sessionUid = config.session ? config.session.uid : null;
    
    this.currentStep = 0;
    this.answers = {};
    
    this.init();
  }

  init() {
    console.log('WPAsk Widget initialized');
    
    // Check frontend targeting rules before rendering
    if (!this.checkFrontendTargeting()) {
      console.log('Frontend targeting rules not met, widget will not show');
      return;
    }
    
    // Set up scroll and exit intent listeners for dynamic targeting
    this.setupDynamicTargeting();
    
    this.createShadowDom();
    this.renderWidget();
    this.bindEvents();
    
    // Animate in after a short delay
    setTimeout(() => {
      this.widget.classList.add('visible');
    }, 500);
  }

  setupDynamicTargeting() {
    const targeting = this.survey.targeting;
    if (!targeting || !targeting.rules) return;

    const hasScrollDepth = targeting.rules.some(r => r.type === 'scroll_depth');
    const hasExitIntent = targeting.rules.some(r => r.type === 'exit_intent');
    const hasTimeOnPage = targeting.rules.some(r => r.type === 'time_on_page');

    if (hasScrollDepth) {
      this.setupScrollDepthListener();
    }

    if (hasExitIntent) {
      this.setupExitIntentListener();
    }

    if (hasTimeOnPage) {
      this.setupTimeOnPageListener();
    }
  }

  setupScrollDepthListener() {
    const checkScroll = () => {
      if (!this.widget || this.widget.classList.contains('visible')) return;
      
      if (this.checkFrontendTargeting()) {
        this.createShadowDom();
        this.renderWidget();
        this.bindEvents();
        setTimeout(() => {
          this.widget.classList.add('visible');
        }, 500);
        window.removeEventListener('scroll', checkScroll);
      }
    };
    
    window.addEventListener('scroll', checkScroll);
  }

  setupExitIntentListener() {
    const checkExitIntent = (e) => {
      if (!this.widget || this.widget.classList.contains('visible')) return;
      
      if (e.clientY <= 0) {
        if (this.checkFrontendTargeting()) {
          this.createShadowDom();
          this.renderWidget();
          this.bindEvents();
          setTimeout(() => {
            this.widget.classList.add('visible');
          }, 500);
          document.removeEventListener('mouseleave', checkExitIntent);
        }
      }
    };
    
    document.addEventListener('mouseleave', checkExitIntent);
  }

  setupTimeOnPageListener() {
    const checkTime = () => {
      if (!this.widget || this.widget.classList.contains('visible')) return;
      
      if (this.checkFrontendTargeting()) {
        this.createShadowDom();
        this.renderWidget();
        this.bindEvents();
        setTimeout(() => {
          this.widget.classList.add('visible');
        }, 500);
      }
    };
    
    // Check every second
    setInterval(checkTime, 1000);
  }

  checkFrontendTargeting() {
    const targeting = this.survey.targeting;
    if (!targeting || !targeting.rules) return true;

    const rules = targeting.rules;
    const ruleMatch = targeting.rule_match || 'all';
    let isMatch = ruleMatch === 'all';

    for (const rule of rules) {
      const ruleResult = this.evaluateFrontendRule(rule);

      if (ruleMatch === 'all' && !ruleResult) {
        return false;
      }

      if (ruleMatch === 'any' && ruleResult) {
        return true;
      }
    }

    if (ruleMatch === 'any') return false;
    return true;
  }

  evaluateFrontendRule(rule) {
    const type = rule.type;
    const operator = rule.operator;
    const value = rule.value;

    // Server-side rules are already evaluated, skip them
    if (['url', 'post_type', 'page', 'user_status', 'referrer', 'device'].includes(type)) {
      return true;
    }

    switch (type) {
      case 'time_on_page':
        return this.evaluateTimeOnPage(operator, value);
      case 'scroll_depth':
        return this.evaluateScrollDepth(operator, value);
      case 'exit_intent':
        return this.evaluateExitIntent(operator, value);
      default:
        return true;
    }
  }

  evaluateTimeOnPage(operator, value) {
    const timeOnPage = (Date.now() - window.performance.timing.navigationStart) / 1000;
    const targetTime = parseInt(value, 10);

    if (operator === 'greater_than') {
      return timeOnPage >= targetTime;
    }
    if (operator === 'less_than') {
      return timeOnPage <= targetTime;
    }
    return true;
  }

  evaluateScrollDepth(operator, value) {
    const scrollPercent = Math.round(
      (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
    );
    const targetDepth = parseInt(value, 10);

    if (operator === 'greater_than') {
      return scrollPercent >= targetDepth;
    }
    if (operator === 'less_than') {
      return scrollPercent <= targetDepth;
    }
    return true;
  }

  evaluateExitIntent(operator, value) {
    // Exit intent is handled via event listener, return true to allow initial check
    return true;
  }

  createShadowDom() {
    this.host = document.getElementById('wpask-widget-root');
    if (!this.host) {
      this.host = document.createElement('div');
      this.host.id = 'wpask-widget-root';
      document.body.appendChild(this.host);
    }
    
    this.shadow = this.host.attachShadow({ mode: 'open' });
    
    // Inject styles
    const style = document.createElement('style');
    style.textContent = this.getStyles();
    this.shadow.appendChild(style);
    
    this.widget = document.createElement('div');
    this.widget.className = `wpask-widget ${this.survey.settings.position || 'bottom-right'}`;
    this.shadow.appendChild(this.widget);
  }

  getStyles() {
    const color = this.survey.settings.color || '#4F46E5';
    return `
      .wpask-widget {
        position: fixed;
        width: 340px;
        background: #fff;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        z-index: 999999;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        flex-direction: column;
      }
      .wpask-widget.visible {
        transform: translateY(0);
      }
      .wpask-widget.bottom-right { bottom: 0; right: 30px; }
      .wpask-widget.bottom-left { bottom: 0; left: 30px; }
      .wpask-widget.bottom-center { bottom: 0; left: 50%; margin-left: -170px; }
      
      .wpask-header {
        background: ${color};
        color: #fff;
        padding: 15px 20px;
        border-radius: 12px 12px 0 0;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
      }
      
      .wpask-close {
        background: none;
        border: none;
        color: rgba(255,255,255,0.8);
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
      }
      .wpask-close:hover { color: #fff; }
      
      .wpask-body {
        padding: 20px;
        background: #fff;
        max-height: 400px;
        overflow-y: auto;
      }
      
      .wpask-question-label {
        font-size: 15px;
        font-weight: 500;
        color: #1a1d2b;
        margin: 0 0 15px 0;
        line-height: 1.4;
      }
      
      .wpask-input-text {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
        resize: vertical;
      }
      .wpask-input-text:focus {
        outline: none;
        border-color: ${color};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .wpask-radio-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
      }
      
      .wpask-radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
      }
      
      .wpask-checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
      }
      
      .wpask-checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
      }
      
      .wpask-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
        background: #fff;
      }
      .wpask-select:focus {
        outline: none;
        border-color: ${color};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .wpask-date {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
      }
      .wpask-date:focus {
        outline: none;
        border-color: ${color};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .wpask-email {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
      }
      .wpask-email:focus {
        outline: none;
        border-color: ${color};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .wpask-number {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
      }
      .wpask-number:focus {
        outline: none;
        border-color: ${color};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .wpask-file-upload {
        margin-bottom: 15px;
      }
      .wpask-file-input {
        display: none;
      }
      .wpask-file-dropzone {
        padding: 20px;
        border: 2px dashed #d1d5db;
        border-radius: 6px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s;
      }
      .wpask-file-dropzone:hover {
        border-color: ${color};
      }
      .wpask-file-icon {
        font-size: 24px;
        margin-bottom: 8px;
      }
      .wpask-file-text {
        font-size: 14px;
        color: #374151;
        margin-bottom: 4px;
      }
      .wpask-file-hint {
        font-size: 12px;
        color: #6b7280;
      }
      .wpask-file-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        background: #f3f4f6;
        border-radius: 6px;
        margin-top: 10px;
      }
      .wpask-file-name {
        font-size: 13px;
        color: #374151;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
      }
      .wpask-file-remove {
        background: none;
        border: none;
        font-size: 20px;
        color: #6b7280;
        cursor: pointer;
        padding: 0 5px;
      }
      .wpask-file-remove:hover {
        color: #ef4444;
      }
      
      .wpask-btn {
        width: 100%;
        background: ${color};
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: opacity 0.2s;
      }
      .wpask-btn:hover { opacity: 0.9; }
      .wpask-btn:disabled { opacity: 0.5; cursor: not-allowed; }
      
      .wpask-footer {
        padding: 10px 20px;
        text-align: center;
        font-size: 11px;
        color: #9ca3af;
        background: #f9fafb;
        border-top: 1px solid #f3f4f6;
      }
      .wpask-footer a { color: #6b7280; text-decoration: none; }
      
      .wpask-rating {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
      }
      .wpask-star {
        font-size: 28px;
        color: #d1d5db;
        cursor: pointer;
        transition: color 0.2s;
      }
      .wpask-star.active { color: #fbbf24; }
      
      .wpask-success {
        text-align: center;
        padding: 20px 0;
      }
      .wpask-success-icon {
        font-size: 40px;
        color: #10b981;
        margin-bottom: 10px;
      }
    `;
  }

  renderWidget() {
    const title = this.survey.title || 'Survey';
    
    this.widget.innerHTML = `
      <div class="wpask-header">
        <span class="wpask-title">${title}</span>
        <button class="wpask-close">&times;</button>
      </div>
      <div class="wpask-body" id="wpask-body"></div>
      <div class="wpask-footer">
        Powered by <a href="https://wpask.io" target="_blank">WPAsk</a>
      </div>
    `;
    
    this.renderStep();
  }

  renderStep() {
    const body = this.widget.querySelector('#wpask-body');
    
    // Check if we should skip this question based on logic
    if (this.shouldSkipQuestion(this.currentStep)) {
      this.currentStep++;
      if (this.currentStep >= this.survey.questions.length) {
        this.submitSurvey();
      } else {
        this.renderStep();
      }
      return;
    }
    
    if (this.currentStep >= this.survey.questions.length) {
      // Completed
      body.innerHTML = `
        <div class="wpask-success">
          <div class="wpask-success-icon">✓</div>
          <h3>${this.survey.settings?.confirmation?.message || 'Thank you!'}</h3>
        </div>
      `;
      setTimeout(() => this.closeWidget(), 3000);
      return;
    }
    
    const question = this.survey.questions[this.currentStep];
    let html = `<p class="wpask-question-label">${question.label}</p>`;
    
    if (question.type === 'textarea') {
      html += `<textarea class="wpask-input-text wpask-answer-input" rows="3" placeholder="Your answer..."></textarea>`;
    } else if (question.type === 'radio') {
      const options = question.options || ['Yes', 'No']; // Fallback
      html += `<div class="wpask-radio-group">`;
      options.forEach(opt => {
        html += `
          <label class="wpask-radio-label">
            <input type="radio" name="wpask_q_${question.id}" value="${opt}" class="wpask-answer-input">
            ${opt}
          </label>
        `;
      });
      html += `</div>`;
    } else if (question.type === 'checkbox') {
      const options = question.options || ['Option 1', 'Option 2'];
      html += `<div class="wpask-checkbox-group">`;
      options.forEach(opt => {
        html += `
          <label class="wpask-checkbox-label">
            <input type="checkbox" name="wpask_q_${question.id}" value="${opt}" class="wpask-answer-input">
            ${opt}
          </label>
        `;
      });
      html += `</div>`;
    } else if (question.type === 'dropdown') {
      const options = question.options || ['Option 1', 'Option 2'];
      html += `<select class="wpask-select wpask-answer-input">`;
      html += `<option value="">Select an option...</option>`;
      options.forEach(opt => {
        html += `<option value="${opt}">${opt}</option>`;
      });
      html += `</select>`;
    } else if (question.type === 'date') {
      html += `<input type="date" class="wpask-date wpask-answer-input">`;
    } else if (question.type === 'email') {
      html += `<input type="email" class="wpask-email wpask-answer-input" placeholder="user@example.com">`;
    } else if (question.type === 'number') {
      html += `<input type="number" class="wpask-number wpask-answer-input" placeholder="0">`;
    } else if (question.type === 'file_upload') {
      const allowedTypes = question.allowed_types || '';
      const maxSize = question.max_file_size || 5;
      html += `
        <div class="wpask-file-upload" id="wpask-file-upload-container">
          <input type="file" class="wpask-answer-input wpask-file-input" 
                 ${allowedTypes ? `accept=".${allowedTypes.split(',').join(',.')}"` : ''} 
                 data-max-size="${maxSize}">
          <div class="wpask-file-dropzone">
            <div class="wpask-file-icon">📁</div>
            <div class="wpask-file-text">Click or drag file to upload</div>
            <div class="wpask-file-hint">Max ${maxSize}MB${allowedTypes ? ` (${allowedTypes})` : ''}</div>
          </div>
          <div class="wpask-file-preview" style="display:none;">
            <div class="wpask-file-name"></div>
            <button type="button" class="wpask-file-remove">×</button>
          </div>
        </div>
      `;
    } else if (question.type === 'rating') {
      html += `
        <div class="wpask-rating" id="wpask-rating-container">
          <span class="wpask-star" data-val="1">★</span>
          <span class="wpask-star" data-val="2">★</span>
          <span class="wpask-star" data-val="3">★</span>
          <span class="wpask-star" data-val="4">★</span>
          <span class="wpask-star" data-val="5">★</span>
        </div>
        <input type="hidden" class="wpask-answer-input" id="wpask-rating-input">
      `;
    }
    
    const btnText = this.currentStep === this.survey.questions.length - 1 ? 'Submit' : 'Next';
    html += `<button class="wpask-btn" id="wpask-next-btn">${btnText}</button>`;
    
    body.innerHTML = html;
    
    // Bind step events
    const nextBtn = body.querySelector('#wpask-next-btn');
    nextBtn.addEventListener('click', () => this.handleNext(question));
    
    // Rating logic
    if (question.type === 'rating') {
      const stars = body.querySelectorAll('.wpask-star');
      const input = body.querySelector('#wpask-rating-input');
      stars.forEach(star => {
        star.addEventListener('click', (e) => {
          const val = parseInt(e.target.dataset.val, 10);
          input.value = val;
          stars.forEach(s => {
            if (parseInt(s.dataset.val, 10) <= val) {
              s.classList.add('active');
            } else {
              s.classList.remove('active');
            }
          });
        });
      });
    }

    // File upload logic
    if (question.type === 'file_upload') {
      const fileInput = body.querySelector('.wpask-file-input');
      const dropzone = body.querySelector('.wpask-file-dropzone');
      const preview = body.querySelector('.wpask-file-preview');
      const fileName = body.querySelector('.wpask-file-name');
      const removeBtn = body.querySelector('.wpask-file-remove');
      const maxSizeMB = parseInt(fileInput.dataset.maxSize, 10) || 5;

      // Click to upload
      dropzone.addEventListener('click', () => fileInput.click());

      // File selection
      fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          if (file.size > maxSizeMB * 1024 * 1024) {
            alert(`File size exceeds ${maxSizeMB}MB limit`);
            fileInput.value = '';
            return;
          }
          fileName.textContent = file.name;
          dropzone.style.display = 'none';
          preview.style.display = 'flex';
        }
      });

      // Remove file
      removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        dropzone.style.display = 'block';
        preview.style.display = 'none';
      });

      // Drag and drop
      dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = this.survey.settings.color || '#4F46E5';
      });

      dropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = '#d1d5db';
      });

      dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = '#d1d5db';
        const file = e.dataTransfer.files[0];
        if (file) {
          if (file.size > maxSizeMB * 1024 * 1024) {
            alert(`File size exceeds ${maxSizeMB}MB limit`);
            return;
          }
          fileInput.files = e.dataTransfer.files;
          fileName.textContent = file.name;
          dropzone.style.display = 'none';
          preview.style.display = 'flex';
        }
      });
    }
  }

  handleNext(question) {
    const body = this.widget.querySelector('#wpask-body');
    let value = null;
    
    if (question.type === 'textarea') {
      value = body.querySelector('.wpask-answer-input').value;
    } else if (question.type === 'radio') {
      const checked = body.querySelector('.wpask-answer-input:checked');
      if (checked) value = checked.value;
    } else if (question.type === 'checkbox') {
      const checked = body.querySelectorAll('.wpask-answer-input:checked');
      value = Array.from(checked).map(cb => cb.value);
    } else if (question.type === 'dropdown') {
      const select = body.querySelector('.wpask-answer-input');
      if (select) value = select.value;
    } else if (question.type === 'date') {
      const dateInput = body.querySelector('.wpask-answer-input');
      if (dateInput) value = dateInput.value;
    } else if (question.type === 'email') {
      const emailInput = body.querySelector('.wpask-answer-input');
      if (emailInput) value = emailInput.value;
    } else if (question.type === 'number') {
      const numberInput = body.querySelector('.wpask-answer-input');
      if (numberInput) value = numberInput.value;
    } else if (question.type === 'rating') {
      value = body.querySelector('#wpask-rating-input').value;
    } else if (question.type === 'file_upload') {
      const fileInput = body.querySelector('.wpask-answer-input');
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        // Convert file to base64 for submission
        const reader = new FileReader();
        reader.onload = (e) => {
          value = {
            name: file.name,
            size: file.size,
            type: file.type,
            data: e.target.result
          };
          this.answers[question.id] = {
            type: question.type,
            label: question.label,
            value: value
          };
          this.moveToNextStep(question);
        };
        reader.readAsDataURL(file);
        return; // Wait for file to be read
      }
    }
    
    if (question.required) {
      if (question.type === 'checkbox') {
        if (!value || value.length === 0) {
          alert('Please select at least one option before continuing.');
          return;
        }
      } else if (question.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value || !emailRegex.test(value)) {
          alert('Please enter a valid email address.');
          return;
        }
      } else if (question.type === 'file_upload') {
        const fileInput = body.querySelector('.wpask-answer-input');
        if (fileInput.files.length === 0) {
          alert('Please upload a file before continuing.');
          return;
        }
      } else if (!value || value.trim() === '') {
        alert('Please answer the question before continuing.');
        return;
      }
    }
    
    this.answers[question.id] = {
      type: question.type,
      label: question.label,
      value: value
    };
    
    // Move to next step, checking for skip logic
    this.moveToNextStep();
  }
  
  moveToNextStep() {
    this.currentStep++;
    
    // Skip questions that should be hidden based on logic
    while (this.currentStep < this.survey.questions.length && this.shouldSkipQuestion(this.currentStep)) {
      this.currentStep++;
    }
    
    if (this.currentStep >= this.survey.questions.length) {
      this.submitSurvey();
    } else {
      this.renderStep();
    }
  }
  
  shouldSkipQuestion(questionIndex) {
    const question = this.survey.questions[questionIndex];
    
    // If logic is not enabled, don't skip
    if (!question.logic || !question.logic.enabled) {
      return false;
    }
    
    // If no conditions, don't skip
    if (!question.logic.conditions || question.logic.conditions.length === 0) {
      return false;
    }
    
    // Evaluate all conditions
    const conditionsMatch = this.evaluateConditions(question.logic.conditions);
    
    // Determine if we should skip based on action
    if (question.logic.action === 'show') {
      return !conditionsMatch; // Skip if conditions don't match (we want to show only when they match)
    } else if (question.logic.action === 'hide') {
      return conditionsMatch; // Skip if conditions match (we want to hide when they match)
    } else if (question.logic.action === 'skip') {
      return conditionsMatch; // Skip if conditions match
    }
    
    return false;
  }
  
  evaluateConditions(conditions) {
    // All conditions must match (AND logic)
    return conditions.every(cond => this.evaluateCondition(cond));
  }
  
  evaluateCondition(condition) {
    const { questionId, operator, value } = condition;
    const answer = this.answers[questionId];
    
    if (!answer) return false;
    
    const answerValue = answer.value;
    
    switch (operator) {
      case '=':
        return String(answerValue) === String(value);
      case '!=':
        return String(answerValue) !== String(value);
      case 'in':
        return Array.isArray(answerValue) && answerValue.includes(value);
      default:
        return false;
    }
  }

  async submitSurvey() {
    const payload = {
      survey_id: this.survey.id,
      answers: this.answers,
      uid: this.sessionUid || ''
    };
    
    try {
      const res = await fetch(`${this.apiUrl}/submit`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      
      const data = await res.json();
      console.log('Survey submitted successfully:', data);
      
      // Show success message
      const body = this.widget.querySelector('#wpask-body');
      body.innerHTML = `
        <div class="wpask-success">
          <div class="wpask-success-icon">✓</div>
          <h3>${this.survey.settings?.confirmation?.message || 'Thank you!'}</h3>
        </div>
      `;
      setTimeout(() => this.closeWidget(), 3000);
    } catch (e) {
      console.error('Error submitting survey:', e);
      alert('Error submitting survey. Please try again.');
    }
  }

  bindEvents() {
    const closeBtn = this.widget.querySelector('.wpask-close');
    const header = this.widget.querySelector('.wpask-header');
    
    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      this.closeWidget();
    });
    
    // Toggle minimize
    header.addEventListener('click', () => {
      const body = this.widget.querySelector('.wpask-body');
      const footer = this.widget.querySelector('.wpask-footer');
      if (body.style.display === 'none') {
        body.style.display = 'block';
        footer.style.display = 'block';
      } else {
        body.style.display = 'none';
        footer.style.display = 'none';
      }
    });
  }
  
  closeWidget() {
    this.widget.classList.remove('visible');
    setTimeout(() => {
      this.host.remove();
    }, 400);
  }
}

// Boot up if config exists
if (window.WPAskConfig && window.WPAskConfig.survey) {
  // Check if we need to load tracking/analytics impression
  if (window.WPAskConfig.api_url) {
      fetch(`${window.WPAskConfig.api_url}/surveys/${window.WPAskConfig.survey.id}/impression`, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json'
          }
      }).catch(e => console.error(e));
  }
  
  new SurveyController(window.WPAskConfig);
}
