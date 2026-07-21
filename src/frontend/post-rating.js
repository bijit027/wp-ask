/**
 * WPAsk Post Rating Widget
 * Embeddable star or thumbs rating via [wpask_rating] shortcode.
 */

class PostRatingWidget {
  constructor(config, container) {
    this.config = config;
    this.container = container;
    this.apiUrl = config.api_url;
    this.postId = config.post_id;
    this.type = config.type || 'stars';
    this.summary = null;
    this.userRating = null;
    this.submitting = false;

    this.init();
  }

  async init() {
    if (this.hasVotedCookie()) {
      this.userRating = this.getVotedCookie();
    }

    await this.loadSummary();
    this.render();
    this.bindEvents();
  }

  hasVotedCookie() {
    return document.cookie.includes(`wpask_pr_${this.postId}=`);
  }

  getVotedCookie() {
    const match = document.cookie.match(new RegExp(`(?:^|; )wpask_pr_${this.postId}=([^;]*)`));
    return match ? parseInt(decodeURIComponent(match[1]), 10) : null;
  }

  setVotedCookie(rating) {
    document.cookie = `wpask_pr_${this.postId}=${encodeURIComponent(rating)}; path=/; max-age=31536000; SameSite=Lax`;
  }

  async loadSummary() {
    try {
      const res = await fetch(`${this.apiUrl}/post-ratings/${this.postId}?type=${this.type}`);
      if (!res.ok) return;
      this.summary = await res.json();
      if (this.summary.user_rating !== null && this.summary.user_rating !== undefined) {
        this.userRating = this.summary.user_rating;
      }
    } catch (e) {
      console.error('WPAsk rating: failed to load summary', e);
    }
  }

  render() {
    this.container.innerHTML = '';
    this.container.className = 'wpask-post-rating-root wpask-post-rating';

    const host = document.createElement('div');
    host.className = 'wpask-post-rating-host';
    this.container.appendChild(host);

    const shadow = host.attachShadow({ mode: 'open' });
    shadow.innerHTML = `
      <style>${this.getStyles()}</style>
      <div class="wpask-rating-widget" data-type="${this.type}">
        <div class="wpask-rating-label">${this.config.label || 'Rate this post'}</div>
        <div class="wpask-rating-controls"></div>
        <div class="wpask-rating-meta"></div>
        <div class="wpask-rating-thanks" hidden>Thanks for your rating!</div>
      </div>
    `;

    this.shadow = shadow;
    this.renderControls();
    this.renderMeta();
  }

  getStyles() {
    const color = this.config.color || '#6366f1';
    return `
      :host, * { box-sizing: border-box; font-family: Inter, system-ui, sans-serif; }
      .wpask-rating-widget { display: inline-flex; flex-direction: column; gap: 8px; }
      .wpask-rating-label { font-size: 14px; font-weight: 600; color: #1a1d2b; }
      .wpask-rating-controls { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
      .wpask-rating-meta { font-size: 12px; color: #6b7280; min-height: 16px; }
      .wpask-rating-thanks { font-size: 13px; color: ${color}; font-weight: 500; }
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
      .wpask-star-btn.active svg, .wpask-star-btn.hover svg { fill: ${color}; }
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
      .wpask-thumb-btn.active-up { border-color: ${color}; color: ${color}; background: ${color}14; }
      .wpask-thumb-btn.active-down { border-color: #ef4444; color: #ef4444; background: #ef444414; }
      .wpask-thumb-btn.active-up svg { fill: ${color}; stroke: ${color}; }
      .wpask-thumb-btn.active-down svg { fill: #ef4444; stroke: #ef4444; }
    `;
  }

  starSvg() {
    return '<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.77 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>';
  }

  thumbSvg(up) {
    if (up) {
      return '<svg viewBox="0 0 24 24"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>';
    }
    return '<svg viewBox="0 0 24 24"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>';
  }

  renderControls() {
    const controls = this.shadow.querySelector('.wpask-rating-controls');
    if (!controls) return;

    controls.innerHTML = '';

    if (this.type === 'thumbs') {
      this.renderThumbs(controls);
    } else {
      this.renderStars(controls);
    }
  }

  renderStars(controls) {
    const voted = this.userRating !== null && this.userRating !== undefined;

    for (let i = 1; i <= 5; i += 1) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'wpask-star-btn';
      btn.dataset.rating = String(i);
      btn.innerHTML = this.starSvg();
      btn.setAttribute('aria-label', `Rate ${i} out of 5 stars`);

      if (voted && i <= this.userRating) {
        btn.classList.add('active');
      }

      if (voted) {
        btn.disabled = true;
      }

      controls.appendChild(btn);
    }
  }

  renderThumbs(controls) {
    const voted = this.userRating !== null && this.userRating !== undefined;

    [
      { value: 1, label: 'Helpful', up: true },
      { value: 0, label: 'Not helpful', up: false },
    ].forEach(({ value, label, up }) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'wpask-thumb-btn';
      btn.dataset.rating = String(value);
      btn.innerHTML = `${this.thumbSvg(up)} <span>${label}</span>`;

      if (voted && this.userRating === value) {
        btn.classList.add(value === 1 ? 'active-up' : 'active-down');
      }

      if (voted) {
        btn.disabled = true;
      }

      controls.appendChild(btn);
    });
  }

  renderMeta() {
    const meta = this.shadow.querySelector('.wpask-rating-meta');
    if (!meta || !this.summary) return;

    if (this.type === 'thumbs') {
      const total = (this.summary.up || 0) + (this.summary.down || 0);
      meta.textContent = total > 0
        ? `${this.summary.up || 0} found this helpful · ${this.summary.down || 0} did not`
        : 'Be the first to rate this post';
      return;
    }

    if (this.summary.count > 0) {
      meta.textContent = `${this.summary.average} out of 5 · ${this.summary.count} rating${this.summary.count === 1 ? '' : 's'}`;
    } else {
      meta.textContent = 'Be the first to rate this post';
    }
  }

  bindEvents() {
    const controls = this.shadow.querySelector('.wpask-rating-controls');
    if (!controls) return;

    controls.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-rating]');
      if (!btn || btn.disabled || this.submitting) return;

      const rating = parseInt(btn.dataset.rating, 10);
      await this.submitRating(rating);
    });

    if (this.type === 'stars') {
      controls.addEventListener('mouseover', (e) => {
        const btn = e.target.closest('.wpask-star-btn');
        if (!btn || btn.disabled) return;
        const hoverValue = parseInt(btn.dataset.rating, 10);
        controls.querySelectorAll('.wpask-star-btn').forEach((star, index) => {
          star.classList.toggle('hover', index < hoverValue);
        });
      });

      controls.addEventListener('mouseleave', () => {
        controls.querySelectorAll('.wpask-star-btn').forEach(star => star.classList.remove('hover'));
      });
    }
  }

  async submitRating(rating) {
    this.submitting = true;

    try {
      const res = await fetch(`${this.apiUrl}/post-ratings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          post_id: this.postId,
          rating,
          type: this.type,
        }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || 'Rating failed');
      }

      this.summary = data.summary;
      this.userRating = rating;
      this.setVotedCookie(rating);
      this.renderControls();
      this.renderMeta();

      const thanks = this.shadow.querySelector('.wpask-rating-thanks');
      if (thanks) {
        thanks.hidden = false;
      }
    } catch (e) {
      console.error('WPAsk rating submit failed', e);
    } finally {
      this.submitting = false;
    }
  }
}

function bootPostRatings() {
  const nodes = document.querySelectorAll('.wpask-post-rating[data-wpask-config]');

  nodes.forEach((container) => {
    if (container.dataset.wpaskBooted === '1') {
      return;
    }

    try {
      const config = JSON.parse(container.dataset.wpaskConfig || '{}');
      if (!config.post_id) {
        return;
      }
      container.dataset.wpaskBooted = '1';
      new PostRatingWidget(config, container);
    } catch (e) {
      console.error('WPAsk rating: invalid config', e);
    }
  });
}

function scheduleBoot() {
  bootPostRatings();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', scheduleBoot);
} else {
  scheduleBoot();
}

window.addEventListener('load', scheduleBoot);
