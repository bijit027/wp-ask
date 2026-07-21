class w{constructor(e){this.config=e,this.survey=e.survey,this.apiUrl=e.api_url,this.sessionUid=e.session?e.session.uid:null,this.currentStep=0,this.answers={},this.init()}init(){if(console.log("WPAsk Widget initialized"),!this.checkFrontendTargeting()){console.log("Frontend targeting rules not met, widget will not show");return}this.setupDynamicTargeting(),this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500)}setupDynamicTargeting(){const e=this.survey.targeting;if(!e||!e.rules)return;const s=e.rules.some(n=>n.type==="scroll_depth"),t=e.rules.some(n=>n.type==="exit_intent"),i=e.rules.some(n=>n.type==="time_on_page");s&&this.setupScrollDepthListener(),t&&this.setupExitIntentListener(),i&&this.setupTimeOnPageListener()}setupScrollDepthListener(){const e=()=>{!this.widget||this.widget.classList.contains("visible")||this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500),window.removeEventListener("scroll",e))};window.addEventListener("scroll",e)}setupExitIntentListener(){const e=s=>{!this.widget||this.widget.classList.contains("visible")||s.clientY<=0&&this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500),document.removeEventListener("mouseleave",e))};document.addEventListener("mouseleave",e)}setupTimeOnPageListener(){setInterval(()=>{!this.widget||this.widget.classList.contains("visible")||this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500))},1e3)}checkFrontendTargeting(){const e=this.survey.targeting;if(!e||!e.rules)return!0;const s=e.rules,t=e.rule_match||"all";for(const i of s){const n=this.evaluateFrontendRule(i);if(t==="all"&&!n)return!1;if(t==="any"&&n)return!0}return t!=="any"}evaluateFrontendRule(e){const s=e.type,t=e.operator,i=e.value;if(["url","post_type","page","user_status","referrer","device"].includes(s))return!0;switch(s){case"time_on_page":return this.evaluateTimeOnPage(t,i);case"scroll_depth":return this.evaluateScrollDepth(t,i);case"exit_intent":return this.evaluateExitIntent(t,i);default:return!0}}evaluateTimeOnPage(e,s){const t=(Date.now()-window.performance.timing.navigationStart)/1e3,i=parseInt(s,10);return e==="greater_than"?t>=i:e==="less_than"?t<=i:!0}evaluateScrollDepth(e,s){const t=Math.round(window.scrollY/(document.body.scrollHeight-window.innerHeight)*100),i=parseInt(s,10);return e==="greater_than"?t>=i:e==="less_than"?t<=i:!0}evaluateExitIntent(e,s){return!0}createShadowDom(){this.host=document.getElementById("wpask-widget-root"),this.host||(this.host=document.createElement("div"),this.host.id="wpask-widget-root",document.body.appendChild(this.host)),this.shadow=this.host.attachShadow({mode:"open"});const e=document.createElement("style");e.textContent=this.getStyles(),this.shadow.appendChild(e),this.widget=document.createElement("div"),this.widget.className=`wpask-widget ${this.survey.settings.position||"bottom-right"}`,this.shadow.appendChild(this.widget)}getStyles(){const e=this.survey.settings.color||"#4F46E5";return`
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
        background: ${e};
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
        border-color: ${e};
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
        border-color: ${e};
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
        border-color: ${e};
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
        border-color: ${e};
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
        border-color: ${e};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .wpask-btn {
        width: 100%;
        background: ${e};
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
    `}renderWidget(){const e=this.survey.title||"Survey";this.widget.innerHTML=`
      <div class="wpask-header">
        <span class="wpask-title">${e}</span>
        <button class="wpask-close">&times;</button>
      </div>
      <div class="wpask-body" id="wpask-body"></div>
      <div class="wpask-footer">
        Powered by <a href="https://wpask.io" target="_blank">WPAsk</a>
      </div>
    `,this.renderStep()}renderStep(){var o,d;const e=this.widget.querySelector("#wpask-body");if(this.shouldSkipQuestion(this.currentStep)){this.currentStep++,this.currentStep>=this.survey.questions.length?this.submitSurvey():this.renderStep();return}if(this.currentStep>=this.survey.questions.length){e.innerHTML=`
        <div class="wpask-success">
          <div class="wpask-success-icon">✓</div>
          <h3>${((d=(o=this.survey.settings)==null?void 0:o.confirmation)==null?void 0:d.message)||"Thank you!"}</h3>
        </div>
      `,setTimeout(()=>this.closeWidget(),3e3);return}const s=this.survey.questions[this.currentStep];let t=`<p class="wpask-question-label">${s.label}</p>`;if(s.type==="textarea")t+='<textarea class="wpask-input-text wpask-answer-input" rows="3" placeholder="Your answer..."></textarea>';else if(s.type==="radio"){const a=s.options||["Yes","No"];t+='<div class="wpask-radio-group">',a.forEach(r=>{t+=`
          <label class="wpask-radio-label">
            <input type="radio" name="wpask_q_${s.id}" value="${r}" class="wpask-answer-input">
            ${r}
          </label>
        `}),t+="</div>"}else if(s.type==="checkbox"){const a=s.options||["Option 1","Option 2"];t+='<div class="wpask-checkbox-group">',a.forEach(r=>{t+=`
          <label class="wpask-checkbox-label">
            <input type="checkbox" name="wpask_q_${s.id}" value="${r}" class="wpask-answer-input">
            ${r}
          </label>
        `}),t+="</div>"}else if(s.type==="dropdown"){const a=s.options||["Option 1","Option 2"];t+='<select class="wpask-select wpask-answer-input">',t+='<option value="">Select an option...</option>',a.forEach(r=>{t+=`<option value="${r}">${r}</option>`}),t+="</select>"}else s.type==="date"?t+='<input type="date" class="wpask-date wpask-answer-input">':s.type==="email"?t+='<input type="email" class="wpask-email wpask-answer-input" placeholder="user@example.com">':s.type==="number"?t+='<input type="number" class="wpask-number wpask-answer-input" placeholder="0">':s.type==="rating"&&(t+=`
        <div class="wpask-rating" id="wpask-rating-container">
          <span class="wpask-star" data-val="1">★</span>
          <span class="wpask-star" data-val="2">★</span>
          <span class="wpask-star" data-val="3">★</span>
          <span class="wpask-star" data-val="4">★</span>
          <span class="wpask-star" data-val="5">★</span>
        </div>
        <input type="hidden" class="wpask-answer-input" id="wpask-rating-input">
      `);const i=this.currentStep===this.survey.questions.length-1?"Submit":"Next";if(t+=`<button class="wpask-btn" id="wpask-next-btn">${i}</button>`,e.innerHTML=t,e.querySelector("#wpask-next-btn").addEventListener("click",()=>this.handleNext(s)),s.type==="rating"){const a=e.querySelectorAll(".wpask-star"),r=e.querySelector("#wpask-rating-input");a.forEach(u=>{u.addEventListener("click",h=>{const c=parseInt(h.target.dataset.val,10);r.value=c,a.forEach(l=>{parseInt(l.dataset.val,10)<=c?l.classList.add("active"):l.classList.remove("active")})})})}}handleNext(e){const s=this.widget.querySelector("#wpask-body");let t=null;if(e.type==="textarea")t=s.querySelector(".wpask-answer-input").value;else if(e.type==="radio"){const i=s.querySelector(".wpask-answer-input:checked");i&&(t=i.value)}else if(e.type==="checkbox"){const i=s.querySelectorAll(".wpask-answer-input:checked");t=Array.from(i).map(n=>n.value)}else if(e.type==="dropdown"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else if(e.type==="date"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else if(e.type==="email"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else if(e.type==="number"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else e.type==="rating"&&(t=s.querySelector("#wpask-rating-input").value);if(e.required){if(e.type==="checkbox"){if(!t||t.length===0){alert("Please select at least one option before continuing.");return}}else if(e.type==="email"){if(!t||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t)){alert("Please enter a valid email address.");return}}else if(!t||t.trim()===""){alert("Please answer the question before continuing.");return}}this.answers[e.id]={type:e.type,label:e.label,value:t},this.moveToNextStep()}moveToNextStep(){for(this.currentStep++;this.currentStep<this.survey.questions.length&&this.shouldSkipQuestion(this.currentStep);)this.currentStep++;this.currentStep>=this.survey.questions.length?this.submitSurvey():this.renderStep()}shouldSkipQuestion(e){const s=this.survey.questions[e];if(!s.logic||!s.logic.enabled||!s.logic.conditions||s.logic.conditions.length===0)return!1;const t=this.evaluateConditions(s.logic.conditions);return s.logic.action==="show"?!t:s.logic.action==="hide"||s.logic.action==="skip"?t:!1}evaluateConditions(e){return e.every(s=>this.evaluateCondition(s))}evaluateCondition(e){const{questionId:s,operator:t,value:i}=e,n=this.answers[s];if(!n)return!1;const o=n.value;switch(t){case"=":return String(o)===String(i);case"!=":return String(o)!==String(i);case"in":return Array.isArray(o)&&o.includes(i);default:return!1}}async submitSurvey(){const e={survey_id:this.survey.id,answers:this.answers,uid:this.sessionUid||""};try{const t=await(await fetch(`${this.apiUrl}/submit`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})).json();console.log("Survey submitted successfully:",t)}catch(s){console.error("Error submitting survey:",s)}}bindEvents(){const e=this.widget.querySelector(".wpask-close"),s=this.widget.querySelector(".wpask-header");e.addEventListener("click",t=>{t.stopPropagation(),this.closeWidget()}),s.addEventListener("click",()=>{const t=this.widget.querySelector(".wpask-body"),i=this.widget.querySelector(".wpask-footer");t.style.display==="none"?(t.style.display="block",i.style.display="block"):(t.style.display="none",i.style.display="none")})}closeWidget(){this.widget.classList.remove("visible"),setTimeout(()=>{this.host.remove()},400)}}window.WPAskConfig&&window.WPAskConfig.survey&&(window.WPAskConfig.api_url&&fetch(`${window.WPAskConfig.api_url}/surveys/${window.WPAskConfig.survey.id}/impression`,{method:"POST",headers:{"Content-Type":"application/json"}}).catch(p=>console.error(p)),new w(window.WPAskConfig));
