class d{constructor(t,s){this.config=t,this.container=s,this.apiUrl=t.api_url,this.postId=t.post_id,this.type=t.type||"stars",this.summary=null,this.userRating=null,this.submitting=!1,this.init()}async init(){this.hasVotedCookie()&&(this.userRating=this.getVotedCookie()),await this.loadSummary(),this.render(),this.bindEvents()}hasVotedCookie(){return document.cookie.includes(`wpask_pr_${this.postId}=`)}getVotedCookie(){const t=document.cookie.match(new RegExp(`(?:^|; )wpask_pr_${this.postId}=([^;]*)`));return t?parseInt(decodeURIComponent(t[1]),10):null}setVotedCookie(t){document.cookie=`wpask_pr_${this.postId}=${encodeURIComponent(t)}; path=/; max-age=31536000; SameSite=Lax`}async loadSummary(){try{const t=await fetch(`${this.apiUrl}/post-ratings/${this.postId}?type=${this.type}`);if(!t.ok)return;this.summary=await t.json(),this.summary.user_rating!==null&&this.summary.user_rating!==void 0&&(this.userRating=this.summary.user_rating)}catch(t){console.error("WPAsk rating: failed to load summary",t)}}render(){this.container.innerHTML="",this.container.className="wpask-post-rating-root wpask-post-rating";const t=document.createElement("div");t.className="wpask-post-rating-host",this.container.appendChild(t);const s=t.attachShadow({mode:"open"});s.innerHTML=`
      <style>${this.getStyles()}</style>
      <div class="wpask-rating-widget" data-type="${this.type}">
        <div class="wpask-rating-label">${this.config.label||"Rate this post"}</div>
        <div class="wpask-rating-controls"></div>
        <div class="wpask-rating-meta"></div>
        <div class="wpask-rating-thanks" hidden>Thanks for your rating!</div>
      </div>
    `,this.shadow=s,this.renderControls(),this.renderMeta()}getStyles(){const t=this.config.color||"#6366f1";return`
      :host, * { box-sizing: border-box; font-family: Inter, system-ui, sans-serif; }
      .wpask-rating-widget { display: inline-flex; flex-direction: column; gap: 8px; }
      .wpask-rating-label { font-size: 14px; font-weight: 600; color: #1a1d2b; }
      .wpask-rating-controls { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
      .wpask-rating-meta { font-size: 12px; color: #6b7280; min-height: 16px; }
      .wpask-rating-thanks { font-size: 13px; color: ${t}; font-weight: 500; }
      .wpask-star-btn, .wpask-thumb-btn {
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 4px;
        line-height: 1;
        transition: transform 0.15s ease, color 0.15s ease;
      }
      .wpask-star-btn:hover, .wpask-thumb-btn:hover { transform: scale(1.08); }
      .wpask-star-btn svg { width: 24px; height: 24px; fill: #d1d5db; stroke: none; }
      .wpask-star-btn.active svg, .wpask-star-btn.hover svg { fill: ${t}; }
      .wpask-star-btn:disabled, .wpask-thumb-btn:disabled { cursor: default; opacity: 0.7; }
      .wpask-thumb-btn {
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
      .wpask-thumb-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }
      .wpask-thumb-btn.active-up { border-color: ${t}; color: ${t}; background: ${t}14; }
      .wpask-thumb-btn.active-down { border-color: #ef4444; color: #ef4444; background: #ef444414; }
      .wpask-thumb-btn.active-up svg { fill: ${t}; stroke: ${t}; }
      .wpask-thumb-btn.active-down svg { fill: #ef4444; stroke: #ef4444; }
    `}starSvg(){return'<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.77 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>'}thumbSvg(t){return t?'<svg viewBox="0 0 24 24"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>':'<svg viewBox="0 0 24 24"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>'}renderControls(){const t=this.shadow.querySelector(".wpask-rating-controls");t&&(t.innerHTML="",this.type==="thumbs"?this.renderThumbs(t):this.renderStars(t))}renderStars(t){const s=this.userRating!==null&&this.userRating!==void 0;for(let e=1;e<=5;e+=1){const a=document.createElement("button");a.type="button",a.className="wpask-star-btn",a.dataset.rating=String(e),a.innerHTML=this.starSvg(),a.setAttribute("aria-label",`Rate ${e} out of 5 stars`),s&&e<=this.userRating&&a.classList.add("active"),s&&(a.disabled=!0),t.appendChild(a)}}renderThumbs(t){const s=this.userRating!==null&&this.userRating!==void 0;[{value:1,label:"Helpful",up:!0},{value:0,label:"Not helpful",up:!1}].forEach(({value:e,label:a,up:n})=>{const i=document.createElement("button");i.type="button",i.className="wpask-thumb-btn",i.dataset.rating=String(e),i.innerHTML=`${this.thumbSvg(n)} <span>${a}</span>`,s&&this.userRating===e&&i.classList.add(e===1?"active-up":"active-down"),s&&(i.disabled=!0),t.appendChild(i)})}renderMeta(){const t=this.shadow.querySelector(".wpask-rating-meta");if(!(!t||!this.summary)){if(this.type==="thumbs"){const s=(this.summary.up||0)+(this.summary.down||0);t.textContent=s>0?`${this.summary.up||0} found this helpful · ${this.summary.down||0} did not`:"Be the first to rate this post";return}this.summary.count>0?t.textContent=`${this.summary.average} out of 5 · ${this.summary.count} rating${this.summary.count===1?"":"s"}`:t.textContent="Be the first to rate this post"}}bindEvents(){const t=this.shadow.querySelector(".wpask-rating-controls");t&&(t.addEventListener("click",async s=>{const e=s.target.closest("[data-rating]");if(!e||e.disabled||this.submitting)return;const a=parseInt(e.dataset.rating,10);await this.submitRating(a)}),this.type==="stars"&&(t.addEventListener("mouseover",s=>{const e=s.target.closest(".wpask-star-btn");if(!e||e.disabled)return;const a=parseInt(e.dataset.rating,10);t.querySelectorAll(".wpask-star-btn").forEach((n,i)=>{n.classList.toggle("hover",i<a)})}),t.addEventListener("mouseleave",()=>{t.querySelectorAll(".wpask-star-btn").forEach(s=>s.classList.remove("hover"))})))}async submitRating(t){this.submitting=!0;try{const s=await fetch(`${this.apiUrl}/post-ratings`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({post_id:this.postId,rating:t,type:this.type})}),e=await s.json();if(!s.ok)throw new Error(e.message||"Rating failed");this.summary=e.summary,this.userRating=t,this.setVotedCookie(t),this.renderControls(),this.renderMeta();const a=this.shadow.querySelector(".wpask-rating-thanks");a&&(a.hidden=!1)}catch(s){console.error("WPAsk rating submit failed",s)}finally{this.submitting=!1}}}function h(){document.querySelectorAll(".wpask-post-rating[data-wpask-config]").forEach(t=>{if(t.dataset.wpaskBooted!=="1")try{const s=JSON.parse(t.dataset.wpaskConfig||"{}");if(!s.post_id)return;t.dataset.wpaskBooted="1",new d(s,t)}catch(s){console.error("WPAsk rating: invalid config",s)}})}function r(){h()}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",r):r();window.addEventListener("load",r);
