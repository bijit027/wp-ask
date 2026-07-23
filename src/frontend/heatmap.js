/**
 * PollQuest Heatmap Click Tracker
 * Records normalized click coordinates for heatmap visualization.
 */

class HeatmapTracker {
  constructor(config) {
    this.config = config;
    this.apiUrl = config.api_url;
    this.heatmapId = config.heatmap_id;
    this.queue = [];
    this.flushTimer = null;
    this.boundClick = this.handleClick.bind(this);
    this.init();
  }

  init() {
    document.addEventListener('click', this.boundClick, true);
    window.addEventListener('beforeunload', () => this.flush(true));
    this.flushTimer = setInterval(() => this.flush(), 10000);
  }

  handleClick(event) {
    if (event.target.closest('.pollquest-widget-root, .pollquest-post-rating-root, #pollquest-widget-root')) {
      return;
    }

    const docHeight = Math.max(
      document.documentElement.scrollHeight,
      document.body ? document.body.scrollHeight : 0,
      1
    );
    const scrollY = window.scrollY || window.pageYOffset || 0;
    const x = event.clientX / Math.max(window.innerWidth, 1);
    const y = (scrollY + event.clientY) / docHeight;

    this.queue.push({
      x: Math.max(0, Math.min(1, x)),
      y: Math.max(0, Math.min(1, y)),
      scrollY: Math.round(scrollY),
      vw: window.innerWidth,
      vh: window.innerHeight,
    });

    if (this.queue.length >= 10) {
      this.flush();
    }
  }

  async flush(sync = false) {
    if (this.queue.length === 0) {
      return;
    }

    const clicks = this.queue.splice(0, 20);
    const payload = {
      heatmap_id: this.heatmapId,
      clicks,
    };

    try {
      if (sync && navigator.sendBeacon) {
        const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
        navigator.sendBeacon(`${this.apiUrl}/heatmaps/record`, blob);
        return;
      }

      await fetch(`${this.apiUrl}/heatmaps/record`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        keepalive: true,
      });
    } catch (e) {
      console.error('PollQuest heatmap: failed to send clicks', e);
      this.queue.unshift(...clicks);
    }
  }
}

if (window.PollQuestHeatmapConfig && window.PollQuestHeatmapConfig.heatmap_id) {
  new HeatmapTracker(window.PollQuestHeatmapConfig);
}
