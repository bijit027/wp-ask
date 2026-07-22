class g{constructor(e){this.config=e,this.survey=e.survey,this.apiUrl=e.api_url,this.sessionUid=e.session?e.session.uid:null,this.currentStep=0,this.answers={},this.init()}init(){if(console.log("WPAsk Widget initialized"),!this.checkFrontendTargeting()){console.log("Frontend targeting rules not met, widget will not show");return}this.setupDynamicTargeting(),this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500)}setupDynamicTargeting(){const e=this.survey.targeting;if(!e||!e.rules)return;const s=e.rules.some(a=>a.type==="scroll_depth"),t=e.rules.some(a=>a.type==="exit_intent"),i=e.rules.some(a=>a.type==="time_on_page");s&&this.setupScrollDepthListener(),t&&this.setupExitIntentListener(),i&&this.setupTimeOnPageListener()}setupScrollDepthListener(){const e=()=>{!this.widget||this.widget.classList.contains("visible")||this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500),window.removeEventListener("scroll",e))};window.addEventListener("scroll",e)}setupExitIntentListener(){const e=s=>{!this.widget||this.widget.classList.contains("visible")||s.clientY<=0&&this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500),document.removeEventListener("mouseleave",e))};document.addEventListener("mouseleave",e)}setupTimeOnPageListener(){setInterval(()=>{!this.widget||this.widget.classList.contains("visible")||this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500))},1e3)}checkFrontendTargeting(){const e=this.survey.targeting;if(!e||!e.rules)return!0;const s=e.rules,t=e.rule_match||"all";for(const i of s){const a=this.evaluateFrontendRule(i);if(t==="all"&&!a)return!1;if(t==="any"&&a)return!0}return t!=="any"}evaluateFrontendRule(e){const s=e.type,t=e.operator,i=e.value;if(["url","post_type","page","user_status","referrer","device"].includes(s))return!0;switch(s){case"time_on_page":return this.evaluateTimeOnPage(t,i);case"scroll_depth":return this.evaluateScrollDepth(t,i);case"exit_intent":return this.evaluateExitIntent(t,i);default:return!0}}evaluateTimeOnPage(e,s){const t=(Date.now()-window.performance.timing.navigationStart)/1e3,i=parseInt(s,10);return e==="greater_than"?t>=i:e==="less_than"?t<=i:!0}evaluateScrollDepth(e,s){const t=Math.round(window.scrollY/(document.body.scrollHeight-window.innerHeight)*100),i=parseInt(s,10);return e==="greater_than"?t>=i:e==="less_than"?t<=i:!0}evaluateExitIntent(e,s){return!0}createShadowDom(){this.host=document.getElementById("wpask-widget-root"),this.host||(this.host=document.createElement("div"),this.host.id="wpask-widget-root",document.body.appendChild(this.host)),this.shadow=this.host.attachShadow({mode:"open"});const e=document.createElement("style");e.textContent=this.getStyles(),this.shadow.appendChild(e),this.widget=document.createElement("div"),this.widget.className=`wpask-widget ${this.survey.settings.position||"bottom-right"}`,this.shadow.appendChild(this.widget)}getStyles(){const e=this.survey.settings.color||"#4F46E5";return`
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
        border-color: ${e};
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
    `,this.renderStep()}renderStep(){var o,u;const e=this.widget.querySelector("#wpask-body");if(this.shouldSkipQuestion(this.currentStep)){this.currentStep++,this.currentStep>=this.survey.questions.length?this.submitSurvey():this.renderStep();return}if(this.currentStep>=this.survey.questions.length){e.innerHTML=`
        <div class="wpask-success">
          <div class="wpask-success-icon">✓</div>
          <h3>${((u=(o=this.survey.settings)==null?void 0:o.confirmation)==null?void 0:u.message)||"Thank you!"}</h3>
        </div>
      `,setTimeout(()=>this.closeWidget(),3e3);return}const s=this.survey.questions[this.currentStep];let t=`<p class="wpask-question-label">${s.label}</p>`;if(s.type==="textarea")t+='<textarea class="wpask-input-text wpask-answer-input" rows="3" placeholder="Your answer..."></textarea>';else if(s.type==="radio"){const r=s.options||["Yes","No"];t+='<div class="wpask-radio-group">',r.forEach(n=>{t+=`
          <label class="wpask-radio-label">
            <input type="radio" name="wpask_q_${s.id}" value="${n}" class="wpask-answer-input">
            ${n}
          </label>
        `}),t+="</div>"}else if(s.type==="checkbox"){const r=s.options||["Option 1","Option 2"];t+='<div class="wpask-checkbox-group">',r.forEach(n=>{t+=`
          <label class="wpask-checkbox-label">
            <input type="checkbox" name="wpask_q_${s.id}" value="${n}" class="wpask-answer-input">
            ${n}
          </label>
        `}),t+="</div>"}else if(s.type==="dropdown"){const r=s.options||["Option 1","Option 2"];t+='<select class="wpask-select wpask-answer-input">',t+='<option value="">Select an option...</option>',r.forEach(n=>{t+=`<option value="${n}">${n}</option>`}),t+="</select>"}else if(s.type==="date")t+='<input type="date" class="wpask-date wpask-answer-input">';else if(s.type==="email")t+='<input type="email" class="wpask-email wpask-answer-input" placeholder="user@example.com">';else if(s.type==="number")t+='<input type="number" class="wpask-number wpask-answer-input" placeholder="0">';else if(s.type==="file_upload"){const r=s.allowed_types||"",n=s.max_file_size||5;t+=`
        <div class="wpask-file-upload" id="wpask-file-upload-container">
          <input type="file" class="wpask-answer-input wpask-file-input" 
                 ${r?`accept=".${r.split(",").join(",.")}"`:""} 
                 data-max-size="${n}">
          <div class="wpask-file-dropzone">
            <div class="wpask-file-icon">📁</div>
            <div class="wpask-file-text">Click or drag file to upload</div>
            <div class="wpask-file-hint">Max ${n}MB${r?` (${r})`:""}</div>
          </div>
          <div class="wpask-file-preview" style="display:none;">
            <div class="wpask-file-name"></div>
            <button type="button" class="wpask-file-remove">×</button>
          </div>
        </div>
      `}else s.type==="rating"&&(t+=`
        <div class="wpask-rating" id="wpask-rating-container">
          <span class="wpask-star" data-val="1">★</span>
          <span class="wpask-star" data-val="2">★</span>
          <span class="wpask-star" data-val="3">★</span>
          <span class="wpask-star" data-val="4">★</span>
          <span class="wpask-star" data-val="5">★</span>
        </div>
        <input type="hidden" class="wpask-answer-input" id="wpask-rating-input">
      `);const i=this.currentStep===this.survey.questions.length-1?"Submit":"Next";if(t+=`<button class="wpask-btn" id="wpask-next-btn">${i}</button>`,e.innerHTML=t,e.querySelector("#wpask-next-btn").addEventListener("click",()=>this.handleNext(s)),s.type==="rating"){const r=e.querySelectorAll(".wpask-star"),n=e.querySelector("#wpask-rating-input");r.forEach(c=>{c.addEventListener("click",h=>{const f=parseInt(h.target.dataset.val,10);n.value=f,r.forEach(p=>{parseInt(p.dataset.val,10)<=f?p.classList.add("active"):p.classList.remove("active")})})})}if(s.type==="file_upload"){const r=e.querySelector(".wpask-file-input"),n=e.querySelector(".wpask-file-dropzone"),c=e.querySelector(".wpask-file-preview"),h=e.querySelector(".wpask-file-name"),f=e.querySelector(".wpask-file-remove"),p=parseInt(r.dataset.maxSize,10)||5;n.addEventListener("click",()=>r.click()),r.addEventListener("change",l=>{const d=l.target.files[0];if(d){if(d.size>p*1024*1024){alert(`File size exceeds ${p}MB limit`),r.value="";return}h.textContent=d.name,n.style.display="none",c.style.display="flex"}}),f.addEventListener("click",l=>{l.stopPropagation(),r.value="",n.style.display="block",c.style.display="none"}),n.addEventListener("dragover",l=>{l.preventDefault(),n.style.borderColor=this.survey.settings.color||"#4F46E5"}),n.addEventListener("dragleave",l=>{l.preventDefault(),n.style.borderColor="#d1d5db"}),n.addEventListener("drop",l=>{l.preventDefault(),n.style.borderColor="#d1d5db";const d=l.dataTransfer.files[0];if(d){if(d.size>p*1024*1024){alert(`File size exceeds ${p}MB limit`);return}r.files=l.dataTransfer.files,h.textContent=d.name,n.style.display="none",c.style.display="flex"}})}}handleNext(e){const s=this.widget.querySelector("#wpask-body");let t=null;if(e.type==="textarea")t=s.querySelector(".wpask-answer-input").value;else if(e.type==="radio"){const i=s.querySelector(".wpask-answer-input:checked");i&&(t=i.value)}else if(e.type==="checkbox"){const i=s.querySelectorAll(".wpask-answer-input:checked");t=Array.from(i).map(a=>a.value)}else if(e.type==="dropdown"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else if(e.type==="date"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else if(e.type==="email"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else if(e.type==="number"){const i=s.querySelector(".wpask-answer-input");i&&(t=i.value)}else if(e.type==="rating")t=s.querySelector("#wpask-rating-input").value;else if(e.type==="file_upload"){const i=s.querySelector(".wpask-answer-input");if(i.files.length>0){const a=i.files[0],o=new FileReader;o.onload=u=>{t={name:a.name,size:a.size,type:a.type,data:u.target.result},this.answers[e.id]={type:e.type,label:e.label,value:t},this.moveToNextStep(e)},o.readAsDataURL(a);return}}if(e.required){if(e.type==="checkbox"){if(!t||t.length===0){alert("Please select at least one option before continuing.");return}}else if(e.type==="email"){if(!t||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t)){alert("Please enter a valid email address.");return}}else if(e.type==="file_upload"){if(s.querySelector(".wpask-answer-input").files.length===0){alert("Please upload a file before continuing.");return}}else if(!t||t.trim()===""){alert("Please answer the question before continuing.");return}}this.answers[e.id]={type:e.type,label:e.label,value:t},this.moveToNextStep()}moveToNextStep(){for(this.currentStep++;this.currentStep<this.survey.questions.length&&this.shouldSkipQuestion(this.currentStep);)this.currentStep++;this.currentStep>=this.survey.questions.length?this.submitSurvey():this.renderStep()}shouldSkipQuestion(e){const s=this.survey.questions[e];if(!s.logic||!s.logic.enabled||!s.logic.conditions||s.logic.conditions.length===0)return!1;const t=this.evaluateConditions(s.logic.conditions);return s.logic.action==="show"?!t:s.logic.action==="hide"||s.logic.action==="skip"?t:!1}evaluateConditions(e){return e.every(s=>this.evaluateCondition(s))}evaluateCondition(e){const{questionId:s,operator:t,value:i}=e,a=this.answers[s];if(!a)return!1;const o=a.value;switch(t){case"=":return String(o)===String(i);case"!=":return String(o)!==String(i);case"in":return Array.isArray(o)&&o.includes(i);default:return!1}}async submitSurvey(){var s,t;const e={survey_id:this.survey.id,answers:this.answers,uid:this.sessionUid||""};try{const a=await(await fetch(`${this.apiUrl}/submit`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})).json();console.log("Survey submitted successfully:",a);const o=this.widget.querySelector("#wpask-body");o.innerHTML=`
        <div class="wpask-success">
          <div class="wpask-success-icon">✓</div>
          <h3>${((t=(s=this.survey.settings)==null?void 0:s.confirmation)==null?void 0:t.message)||"Thank you!"}</h3>
        </div>
      `,setTimeout(()=>this.closeWidget(),3e3)}catch(i){console.error("Error submitting survey:",i),alert("Error submitting survey. Please try again.")}}bindEvents(){const e=this.widget.querySelector(".wpask-close"),s=this.widget.querySelector(".wpask-header");e.addEventListener("click",t=>{t.stopPropagation(),this.closeWidget()}),s.addEventListener("click",()=>{const t=this.widget.querySelector(".wpask-body"),i=this.widget.querySelector(".wpask-footer");t.style.display==="none"?(t.style.display="block",i.style.display="block"):(t.style.display="none",i.style.display="none")})}closeWidget(){this.widget.classList.remove("visible"),setTimeout(()=>{this.host.remove()},400)}}window.WPAskConfig&&window.WPAskConfig.survey&&(window.WPAskConfig.api_url&&fetch(`${window.WPAskConfig.api_url}/surveys/${window.WPAskConfig.survey.id}/impression`,{method:"POST",headers:{"Content-Type":"application/json"}}).catch(w=>console.error(w)),new g(window.WPAskConfig));
