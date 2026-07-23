class b{constructor(e){this.config=e,this.survey=e.survey,this.apiUrl=e.api_url,this.sessionUid=e.session?e.session.uid:null,this.currentStep=0,this.answers={},this.init()}init(){if(console.log("PollQuest Widget initialized"),!this.checkFrontendTargeting()){console.log("Frontend targeting rules not met, widget will not show");return}this.setupDynamicTargeting(),this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500)}setupDynamicTargeting(){const e=this.survey.targeting;if(!e||!e.rules)return;const s=e.rules.some(l=>l.type==="scroll_depth"),t=e.rules.some(l=>l.type==="exit_intent"),i=e.rules.some(l=>l.type==="time_on_page");s&&this.setupScrollDepthListener(),t&&this.setupExitIntentListener(),i&&this.setupTimeOnPageListener()}setupScrollDepthListener(){const e=()=>{!this.widget||this.widget.classList.contains("visible")||this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500),window.removeEventListener("scroll",e))};window.addEventListener("scroll",e)}setupExitIntentListener(){const e=s=>{!this.widget||this.widget.classList.contains("visible")||s.clientY<=0&&this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500),document.removeEventListener("mouseleave",e))};document.addEventListener("mouseleave",e)}setupTimeOnPageListener(){setInterval(()=>{!this.widget||this.widget.classList.contains("visible")||this.checkFrontendTargeting()&&(this.createShadowDom(),this.renderWidget(),this.bindEvents(),setTimeout(()=>{this.widget.classList.add("visible")},500))},1e3)}checkFrontendTargeting(){const e=this.survey.targeting;if(!e||!e.rules)return!0;const s=e.rules,t=e.rule_match||"all";for(const i of s){const l=this.evaluateFrontendRule(i);if(t==="all"&&!l)return!1;if(t==="any"&&l)return!0}return t!=="any"}evaluateFrontendRule(e){const s=e.type,t=e.operator,i=e.value;if(["url","post_type","page","user_status","referrer","device"].includes(s))return!0;switch(s){case"time_on_page":return this.evaluateTimeOnPage(t,i);case"scroll_depth":return this.evaluateScrollDepth(t,i);case"exit_intent":return this.evaluateExitIntent(t,i);default:return!0}}evaluateTimeOnPage(e,s){const t=(Date.now()-window.performance.timing.navigationStart)/1e3,i=parseInt(s,10);return e==="greater_than"?t>=i:e==="less_than"?t<=i:!0}evaluateScrollDepth(e,s){const t=Math.round(window.scrollY/(document.body.scrollHeight-window.innerHeight)*100),i=parseInt(s,10);return e==="greater_than"?t>=i:e==="less_than"?t<=i:!0}evaluateExitIntent(e,s){return!0}createShadowDom(){this.host=document.getElementById("pollquest-widget-root"),this.host||(this.host=document.createElement("div"),this.host.id="pollquest-widget-root",document.body.appendChild(this.host)),this.shadow=this.host.attachShadow({mode:"open"});const e=document.createElement("style");e.textContent=this.getStyles(),this.shadow.appendChild(e),this.widget=document.createElement("div"),this.widget.className=`pollquest-widget ${this.survey.settings.position||"bottom-right"}`,this.shadow.appendChild(this.widget)}getStyles(){const e=this.survey.settings.color||"#4F46E5";return`
      .pollquest-widget {
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
      .pollquest-widget.visible {
        transform: translateY(0);
      }
      .pollquest-widget.bottom-right { bottom: 0; right: 30px; }
      .pollquest-widget.bottom-left { bottom: 0; left: 30px; }
      .pollquest-widget.bottom-center { bottom: 0; left: 50%; margin-left: -170px; }
      
      .pollquest-header {
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
      
      .pollquest-close {
        background: none;
        border: none;
        color: rgba(255,255,255,0.8);
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
      }
      .pollquest-close:hover { color: #fff; }
      
      .pollquest-body {
        padding: 20px;
        background: #fff;
        max-height: 400px;
        overflow-y: auto;
      }
      
      .pollquest-question-label {
        font-size: 15px;
        font-weight: 500;
        color: #1a1d2b;
        margin: 0 0 15px 0;
        line-height: 1.4;
      }
      
      .pollquest-input-text {
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
      .pollquest-input-text:focus {
        outline: none;
        border-color: ${e};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .pollquest-radio-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
      }
      
      .pollquest-radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
      }
      
      .pollquest-checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
      }
      
      .pollquest-checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
      }
      
      .pollquest-select {
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
      .pollquest-select:focus {
        outline: none;
        border-color: ${e};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .pollquest-date {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
      }
      .pollquest-date:focus {
        outline: none;
        border-color: ${e};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .pollquest-email {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
      }
      .pollquest-email:focus {
        outline: none;
        border-color: ${e};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .pollquest-number {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        margin-bottom: 15px;
      }
      .pollquest-number:focus {
        outline: none;
        border-color: ${e};
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
      }
      
      .pollquest-file-upload {
        margin-bottom: 15px;
      }
      .pollquest-file-input {
        display: none;
      }
      .pollquest-file-dropzone {
        padding: 20px;
        border: 2px dashed #d1d5db;
        border-radius: 6px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s;
      }
      .pollquest-file-dropzone:hover {
        border-color: ${e};
      }
      .pollquest-file-icon {
        font-size: 24px;
        margin-bottom: 8px;
      }
      .pollquest-file-text {
        font-size: 14px;
        color: #374151;
        margin-bottom: 4px;
      }
      .pollquest-file-hint {
        font-size: 12px;
        color: #6b7280;
      }
      .pollquest-file-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        background: #f3f4f6;
        border-radius: 6px;
        margin-top: 10px;
      }
      .pollquest-file-name {
        font-size: 13px;
        color: #374151;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
      }
      .pollquest-file-remove {
        background: none;
        border: none;
        font-size: 20px;
        color: #6b7280;
        cursor: pointer;
        padding: 0 5px;
      }
      .pollquest-file-remove:hover {
        color: #ef4444;
      }
      
      .pollquest-btn {
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
      .pollquest-btn:hover { opacity: 0.9; }
      .pollquest-btn:disabled { opacity: 0.5; cursor: not-allowed; }
      
      .pollquest-footer {
        padding: 10px 20px;
        text-align: center;
        font-size: 11px;
        color: #9ca3af;
        background: #f9fafb;
        border-top: 1px solid #f3f4f6;
      }
      .pollquest-footer a { color: #6b7280; text-decoration: none; }
      
      .pollquest-rating {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
      }
      .pollquest-star {
        font-size: 28px;
        color: #d1d5db;
        cursor: pointer;
        transition: color 0.2s;
      }
      .pollquest-star.active { color: #fbbf24; }
      
      .pollquest-success {
        text-align: center;
        padding: 20px 0;
      }
      .pollquest-success-icon {
        font-size: 40px;
        color: #10b981;
        margin-bottom: 10px;
      }
    `}renderWidget(){const e=this.survey.title||"Survey";this.widget.innerHTML=`
      <div class="pollquest-header">
        <span class="pollquest-title">${e}</span>
        <button class="pollquest-close">&times;</button>
      </div>
      <div class="pollquest-body" id="pollquest-body"></div>
    `,this.renderStep()}renderStep(){var r,c;const e=this.widget.querySelector("#pollquest-body");if(this.shouldSkipQuestion(this.currentStep)){this.currentStep++,this.currentStep>=this.survey.questions.length?this.submitSurvey():this.renderStep();return}if(this.currentStep>=this.survey.questions.length){e.innerHTML=`
        <div class="pollquest-success">
          <div class="pollquest-success-icon">✓</div>
          <h3>${((c=(r=this.survey.settings)==null?void 0:r.confirmation)==null?void 0:c.message)||"Thank you!"}</h3>
        </div>
      `,setTimeout(()=>this.closeWidget(),3e3);return}const s=this.survey.questions[this.currentStep];let t=`<p class="pollquest-question-label">${s.label}</p>`;if(s.type==="textarea")t+='<textarea class="pollquest-input-text pollquest-answer-input" rows="3" placeholder="Your answer..."></textarea>';else if(s.type==="radio"){const n=s.options||["Yes","No"];t+='<div class="pollquest-radio-group">',n.forEach(o=>{t+=`
          <label class="pollquest-radio-label">
            <input type="radio" name="pollquest_q_${s.id}" value="${o}" class="pollquest-answer-input">
            ${o}
          </label>
        `}),t+="</div>"}else if(s.type==="checkbox"){const n=s.options||["Option 1","Option 2"];t+='<div class="pollquest-checkbox-group">',n.forEach(o=>{t+=`
          <label class="pollquest-checkbox-label">
            <input type="checkbox" name="pollquest_q_${s.id}" value="${o}" class="pollquest-answer-input">
            ${o}
          </label>
        `}),t+="</div>"}else if(s.type==="dropdown"){const n=s.options||["Option 1","Option 2"];t+='<select class="pollquest-select pollquest-answer-input">',t+='<option value="">Select an option...</option>',n.forEach(o=>{t+=`<option value="${o}">${o}</option>`}),t+="</select>"}else if(s.type==="date")t+='<input type="date" class="pollquest-date pollquest-answer-input">';else if(s.type==="email")t+='<input type="email" class="pollquest-email pollquest-answer-input" placeholder="user@example.com">';else if(s.type==="number")t+='<input type="number" class="pollquest-number pollquest-answer-input" placeholder="0">';else if(s.type==="file_upload"){const n=s.allowed_types||"",o=s.max_file_size||5;t+=`
        <div class="pollquest-file-upload" id="pollquest-file-upload-container">
          <input type="file" class="pollquest-answer-input pollquest-file-input" 
                 ${n?`accept=".${n.split(",").join(",.")}"`:""} 
                 data-max-size="${o}">
          <div class="pollquest-file-dropzone">
            <div class="pollquest-file-icon">📁</div>
            <div class="pollquest-file-text">Click or drag file to upload</div>
            <div class="pollquest-file-hint">Max ${o}MB${n?` (${n})`:""}</div>
          </div>
          <div class="pollquest-file-preview" style="display:none;">
            <div class="pollquest-file-name"></div>
            <button type="button" class="pollquest-file-remove">×</button>
          </div>
        </div>
      `}else s.type==="rating"&&(t+=`
        <div class="pollquest-rating" id="pollquest-rating-container">
          <span class="pollquest-star" data-val="1">★</span>
          <span class="pollquest-star" data-val="2">★</span>
          <span class="pollquest-star" data-val="3">★</span>
          <span class="pollquest-star" data-val="4">★</span>
          <span class="pollquest-star" data-val="5">★</span>
        </div>
        <input type="hidden" class="pollquest-answer-input" id="pollquest-rating-input">
      `);const i=this.currentStep===this.survey.questions.length-1?"Submit":"Next";if(t+=`<button class="pollquest-btn" id="pollquest-next-btn">${i}</button>`,e.innerHTML=t,e.querySelector("#pollquest-next-btn").addEventListener("click",()=>this.handleNext(s)),s.type==="rating"){const n=e.querySelectorAll(".pollquest-star"),o=e.querySelector("#pollquest-rating-input");n.forEach(d=>{d.addEventListener("click",h=>{const f=parseInt(h.target.dataset.val,10);o.value=f,n.forEach(p=>{parseInt(p.dataset.val,10)<=f?p.classList.add("active"):p.classList.remove("active")})})})}if(s.type==="file_upload"){const n=e.querySelector(".pollquest-file-input"),o=e.querySelector(".pollquest-file-dropzone"),d=e.querySelector(".pollquest-file-preview"),h=e.querySelector(".pollquest-file-name"),f=e.querySelector(".pollquest-file-remove"),p=parseInt(n.dataset.maxSize,10)||5;o.addEventListener("click",()=>n.click()),n.addEventListener("change",a=>{const u=a.target.files[0];if(u){if(u.size>p*1024*1024){alert(`File size exceeds ${p}MB limit`),n.value="";return}h.textContent=u.name,o.style.display="none",d.style.display="flex"}}),f.addEventListener("click",a=>{a.stopPropagation(),n.value="",o.style.display="block",d.style.display="none"}),o.addEventListener("dragover",a=>{a.preventDefault(),o.style.borderColor=this.survey.settings.color||"#4F46E5"}),o.addEventListener("dragleave",a=>{a.preventDefault(),o.style.borderColor="#d1d5db"}),o.addEventListener("drop",a=>{a.preventDefault(),o.style.borderColor="#d1d5db";const u=a.dataTransfer.files[0];if(u){if(u.size>p*1024*1024){alert(`File size exceeds ${p}MB limit`);return}n.files=a.dataTransfer.files,h.textContent=u.name,o.style.display="none",d.style.display="flex"}})}}handleNext(e){const s=this.widget.querySelector("#pollquest-body");let t=null;if(e.type==="textarea")t=s.querySelector(".pollquest-answer-input").value;else if(e.type==="radio"){const i=s.querySelector(".pollquest-answer-input:checked");i&&(t=i.value)}else if(e.type==="checkbox"){const i=s.querySelectorAll(".pollquest-answer-input:checked");t=Array.from(i).map(l=>l.value)}else if(e.type==="dropdown"){const i=s.querySelector(".pollquest-answer-input");i&&(t=i.value)}else if(e.type==="date"){const i=s.querySelector(".pollquest-answer-input");i&&(t=i.value)}else if(e.type==="email"){const i=s.querySelector(".pollquest-answer-input");i&&(t=i.value)}else if(e.type==="number"){const i=s.querySelector(".pollquest-answer-input");i&&(t=i.value)}else if(e.type==="rating")t=s.querySelector("#pollquest-rating-input").value;else if(e.type==="file_upload"){const i=s.querySelector(".pollquest-answer-input");if(i.files.length>0){const l=i.files[0],r=new FileReader;r.onload=c=>{t={name:l.name,size:l.size,type:l.type,data:c.target.result},this.answers[e.id]={type:e.type,label:e.label,value:t},this.moveToNextStep(e)},r.readAsDataURL(l);return}}if(e.required){if(e.type==="checkbox"){if(!t||t.length===0){alert("Please select at least one option before continuing.");return}}else if(e.type==="email"){if(!t||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t)){alert("Please enter a valid email address.");return}}else if(e.type==="file_upload"){if(s.querySelector(".pollquest-answer-input").files.length===0){alert("Please upload a file before continuing.");return}}else if(!t||t.trim()===""){alert("Please answer the question before continuing.");return}}this.answers[e.id]={type:e.type,label:e.label,value:t},this.moveToNextStep()}moveToNextStep(){for(this.currentStep++;this.currentStep<this.survey.questions.length&&this.shouldSkipQuestion(this.currentStep);)this.currentStep++;this.currentStep>=this.survey.questions.length?this.submitSurvey():this.renderStep()}shouldSkipQuestion(e){const s=this.survey.questions[e];if(!s.logic||!s.logic.enabled||!s.logic.conditions||s.logic.conditions.length===0)return!1;const t=this.evaluateConditions(s.logic.conditions);return s.logic.action==="show"?!t:s.logic.action==="hide"||s.logic.action==="skip"?t:!1}evaluateConditions(e){return e.every(s=>this.evaluateCondition(s))}evaluateCondition(e){const{questionId:s,operator:t,value:i}=e,l=this.answers[s];if(!l)return!1;const r=l.value;switch(t){case"=":return String(r)===String(i);case"!=":return String(r)!==String(i);case"in":return Array.isArray(r)&&r.includes(i);default:return!1}}async submitSurvey(){var s,t;const e={survey_id:this.survey.id,answers:this.answers,uid:this.sessionUid||""};try{const l=await(await fetch(`${this.apiUrl}/submit`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})).json();console.log("Survey submitted successfully:",l);const r=this.widget.querySelector("#pollquest-body");r.innerHTML=`
        <div class="pollquest-success">
          <div class="pollquest-success-icon">✓</div>
          <h3>${((t=(s=this.survey.settings)==null?void 0:s.confirmation)==null?void 0:t.message)||"Thank you!"}</h3>
        </div>
      `,setTimeout(()=>this.closeWidget(),3e3)}catch(i){console.error("Error submitting survey:",i),alert("Error submitting survey. Please try again.")}}bindEvents(){const e=this.widget.querySelector(".pollquest-close"),s=this.widget.querySelector(".pollquest-header");e.addEventListener("click",t=>{t.stopPropagation(),this.closeWidget()}),s.addEventListener("click",()=>{const t=this.widget.querySelector(".pollquest-body"),i=this.widget.querySelector(".pollquest-footer");t.style.display==="none"?(t.style.display="block",i.style.display="block"):(t.style.display="none",i.style.display="none")})}closeWidget(){this.widget.classList.remove("visible"),setTimeout(()=>{this.host.remove()},400)}}window.PollQuestConfig&&window.PollQuestConfig.survey&&(window.PollQuestConfig.api_url&&fetch(`${window.PollQuestConfig.api_url}/surveys/${window.PollQuestConfig.survey.id}/impression`,{method:"POST",headers:{"Content-Type":"application/json"}}).catch(g=>console.error(g)),new b(window.PollQuestConfig));
