/**
 * WPAsk Frontend Entry Point
 */

class SurveyController {
  constructor(config) {
    this.config = config;
    this.init();
  }

  init() {
    console.log('WPAsk Widget initialized with config:', this.config);
    // Logic to render shadow DOM and handle survey display goes here
  }
}

// Boot up if config exists
if (window.WPAskConfig) {
  new SurveyController(window.WPAskConfig);
}
