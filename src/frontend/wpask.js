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
    this.createShadowDom();
    this.renderWidget();
    this.bindEvents();
    
    // Animate in after a short delay
    setTimeout(() => {
      this.widget.classList.add('visible');
    }, 500);
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
  }

  handleNext(question) {
    const body = this.widget.querySelector('#wpask-body');
    let value = null;
    
    if (question.type === 'textarea') {
      value = body.querySelector('.wpask-answer-input').value;
    } else if (question.type === 'radio') {
      const checked = body.querySelector('.wpask-answer-input:checked');
      if (checked) value = checked.value;
    } else if (question.type === 'rating') {
      value = body.querySelector('#wpask-rating-input').value;
    }
    
    if (question.required && (!value || value.trim() === '')) {
      alert('Please answer the question before continuing.');
      return;
    }
    
    this.answers[question.id] = {
      type: question.type,
      label: question.label,
      value: value
    };
    
    this.currentStep++;
    
    if (this.currentStep >= this.survey.questions.length) {
      this.submitSurvey();
    }
    
    this.renderStep();
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
    } catch (e) {
      console.error('Error submitting survey:', e);
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
