class l{constructor(t,e){this.config=t,this.container=e,this.apiUrl=t.api_url,this.postId=t.post_id,this.type=t.type||"stars",this.summary=null,this.userRating=null,this.submitting=!1,this.init()}async init(){this.hasVotedCookie()&&(this.userRating=this.getVotedCookie()),await this.loadSummary(),this.render(),this.bindEvents()}hasVotedCookie(){return document.cookie.includes(`pollquest_pr_${this.postId}=`)}getVotedCookie(){const t=document.cookie.match(new RegExp(`(?:^|; )pollquest_pr_${this.postId}=([^;]*)`));return t?parseInt(decodeURIComponent(t[1]),10):null}setVotedCookie(t){document.cookie=`pollquest_pr_${this.postId}=${encodeURIComponent(t)}; path=/; max-age=31536000; SameSite=Lax`}async loadSummary(){try{const t=await fetch(`${this.apiUrl}/post-ratings/${this.postId}?type=${this.type}`);if(!t.ok)return;this.summary=await t.json(),this.summary.user_rating!==null&&this.summary.user_rating!==void 0&&(this.userRating=this.summary.user_rating)}catch(t){console.error("PollQuest rating: failed to load summary",t)}}render(){this.container.innerHTML="",this.container.className="pollquest-post-rating-root pollquest-post-rating";const t=document.createElement("div");t.className="pollquest-post-rating-host",this.container.appendChild(t);const e=t.attachShadow({mode:"open"});e.innerHTML=`
      <style>${this.getStyles()}</style>
      <div class="pollquest-rating-widget" data-type="${this.type}">
        <div class="pollquest-rating-label">${this.config.label||"Rate this post"}</div>
        <div class="pollquest-rating-controls"></div>
        <div class="pollquest-rating-meta"></div>
        <div class="pollquest-rating-thanks" hidden>Thanks for your rating!</div>
      </div>
    `,this.shadow=e,this.renderControls(),this.renderMeta()}getStyles(){const t=this.config.color||"#6366f1";return`
      :host, * { box-sizing: border-box; font-family: Inter, system-ui, sans-serif; }
      .pollquest-rating-widget { display: inline-flex; flex-direction: column; gap: 8px; }
      .pollquest-rating-label { font-size: 14px; font-weight: 600; color: #1a1d2b; }
      .pollquest-rating-controls { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
      .pollquest-rating-meta { font-size: 12px; color: #6b7280; min-height: 16px; }
      .pollquest-rating-thanks { font-size: 13px; color: ${t}; font-weight: 500; }
      .pollquest-star-btn, .pollquest-thumb-btn {
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 4px;
        line-height: 1;
        transition: transform 0.15s ease, color 0.15s ease;
      }
      .pollquest-star-btn:hover, .pollquest-thumb-btn:hover { transform: scale(1.08); }
      .pollquest-star-btn svg { width: 24px; height: 24px; fill: #d1d5db; stroke: none; }
      .pollquest-star-btn.active svg, .pollquest-star-btn.hover svg { fill: ${t}; }
      .pollquest-star-btn:disabled, .pollquest-thumb-btn:disabled { cursor: default; opacity: 0.7; }
      .pollquest-thumb-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 13px;
        color: #374151;
        background: #fff;
      }
      .pollquest-thumb-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }
      .pollquest-thumb-btn.active-up { border-color: ${t}; color: ${t}; background: ${t}14; }
      .pollquest-thumb-btn.active-down { border-color: #ef4444; color: #ef4444; background: #ef444414; }
      .pollquest-thumb-btn.active-up svg { fill: ${t}; stroke: ${t}; }
      .pollquest-thumb-btn.active-down svg { fill: #ef4444; stroke: #ef4444; }
    `}starSvg(){return'<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.77 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>'}thumbSvg(t){return t?'<svg viewBox="0 0 24 24"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>':'<svg viewBox="0 0 24 24"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>'}renderControls(){const t=this.shadow.querySelector(".pollquest-rating-controls");t&&(t.innerHTML="",this.type==="thumbs"?this.renderThumbs(t):this.renderStars(t))}renderStars(t){const e=this.userRating!==null&&this.userRating!==void 0;for(let s=1;s<=5;s+=1){const o=document.createElement("button");o.type="button",o.className="pollquest-star-btn",o.dataset.rating=String(s),o.innerHTML=this.starSvg(),o.setAttribute("aria-label",`Rate ${s} out of 5 stars`),e&&s<=this.userRating&&o.classList.add("active"),e&&(o.disabled=!0),t.appendChild(o)}}renderThumbs(t){const e=this.userRating!==null&&this.userRating!==void 0;[{value:1,label:"Helpful",up:!0},{value:0,label:"Not helpful",up:!1}].forEach(({value:s,label:o,up:n})=>{const i=document.createElement("button");i.type="button",i.className="pollquest-thumb-btn",i.dataset.rating=String(s),i.innerHTML=`${this.thumbSvg(n)} <span>${o}</span>`,e&&this.userRating===s&&i.classList.add(s===1?"active-up":"active-down"),e&&(i.disabled=!0),t.appendChild(i)})}renderMeta(){const t=this.shadow.querySelector(".pollquest-rating-meta");if(!(!t||!this.summary)){if(this.type==="thumbs"){const e=(this.summary.up||0)+(this.summary.down||0);t.textContent=e>0?`${this.summary.up||0} found this helpful · ${this.summary.down||0} did not`:"Be the first to rate this post";return}this.summary.count>0?t.textContent=`${this.summary.average} out of 5 · ${this.summary.count} rating${this.summary.count===1?"":"s"}`:t.textContent="Be the first to rate this post"}}bindEvents(){const t=this.shadow.querySelector(".pollquest-rating-controls");t&&(t.addEventListener("click",async e=>{const s=e.target.closest("[data-rating]");if(!s||s.disabled||this.submitting)return;const o=parseInt(s.dataset.rating,10);await this.submitRating(o)}),this.type==="stars"&&(t.addEventListener("mouseover",e=>{const s=e.target.closest(".pollquest-star-btn");if(!s||s.disabled)return;const o=parseInt(s.dataset.rating,10);t.querySelectorAll(".pollquest-star-btn").forEach((n,i)=>{n.classList.toggle("hover",i<o)})}),t.addEventListener("mouseleave",()=>{t.querySelectorAll(".pollquest-star-btn").forEach(e=>e.classList.remove("hover"))})))}async submitRating(t){this.submitting=!0;try{const e=await fetch(`${this.apiUrl}/post-ratings`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({post_id:this.postId,rating:t,type:this.type})}),s=await e.json();if(!e.ok)throw new Error(s.message||"Rating failed");this.summary=s.summary,this.userRating=t,this.setVotedCookie(t),this.renderControls(),this.renderMeta();const o=this.shadow.querySelector(".pollquest-rating-thanks");o&&(o.hidden=!1)}catch(e){console.error("PollQuest rating submit failed",e)}finally{this.submitting=!1}}}function u(){document.querySelectorAll(".pollquest-post-rating[data-pollquest-config]").forEach(t=>{if(t.dataset.pollquestBooted!=="1")try{const e=JSON.parse(t.dataset.pollquestConfig||"{}");if(!e.post_id)return;t.dataset.pollquestBooted="1",new l(e,t)}catch(e){console.error("PollQuest rating: invalid config",e)}})}function a(){u()}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",a):a();window.addEventListener("load",a);
