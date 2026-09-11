(function () {
  'use strict';
  if (!window.rbmTestMonitor) {
    return;
  }
  var cfg = window.rbmTestMonitor;
  var lastAction = '';
  var errorCount = 0;
  var MAX_CLIENT_ERRORS = 5;

  function detectContext() {
    var container = document.querySelector('.msch-lessons');
    if (!container) {
      return { module: 'general', view: '', category: '' };
    }
    var tilesWrap = container.querySelector('.msch-lesson-tiles-wrap');
    var select = container.querySelector('.msch-lesson-filter-select');
    var view = (tilesWrap && !tilesWrap.hidden) ? 'Instrument View' : 'Category View';
    var category = '';
    if (select && select.selectedIndex >= 0) {
      var opt = select.options[select.selectedIndex];
      category = opt ? opt.text : '';
    }
    return { module: 'rbm-lessons', view: view, category: category };
  }

  function deviceType() {
    var w = window.innerWidth || 0;
    if (w <= 600) { return 'mobile'; }
    if (w <= 1024) { return 'tablet'; }
    return 'desktop';
  }

  function osGuess() {
    var ua = navigator.userAgent || '';
    if (/Windows/i.test(ua)) { return 'Windows'; }
    if (/Mac OS X/i.test(ua)) { return 'macOS'; }
    if (/Android/i.test(ua)) { return 'Android'; }
    if (/iPhone|iPad|iOS/i.test(ua)) { return 'iOS'; }
    if (/Linux/i.test(ua)) { return 'Linux'; }
    return 'Unknown';
  }

  function logEvent(module, action, extra) {
    try {
      extra = extra || {};
      lastAction = module + ': ' + action + (extra.detail ? ' — ' + extra.detail : '');
      var body = {
        module: module,
        action: action,
        view: extra.view || '',
        object_type: extra.object_type || '',
        object_id: extra.object_id || '',
        result: extra.result || '',
        detail: extra.detail || '',
        page_url: window.location.href,
        page_title: document.title,
        browser: (navigator.userAgent || '').slice(0, 120),
        os: osGuess(),
        device_type: deviceType(),
      };
      fetch(cfg.restUrl + '/event', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
        body: JSON.stringify(body),
      }).catch(function () {});
    } catch (e) {
      // Instrumentation must never break the page.
    }
  }

  function initLessonsInstrumentation() {
    try {
      document.addEventListener('change', function (e) {
        if (!e.target || !e.target.classList || !e.target.classList.contains('msch-lesson-filter-select')) {
          return;
        }
        var ctx = detectContext();
        logEvent('rbm-lessons', 'category_selected', { view: ctx.view, detail: ctx.category });
        logEvent('rbm-lessons', 'view_changed', { view: ctx.view, detail: ctx.category });
      }, false);

      document.addEventListener('click', function (e) {
        var backBtn = e.target.closest ? e.target.closest('.msch-lesson-tiles-back') : null;
        if (!backBtn || backBtn.disabled) {
          return;
        }
        logEvent('rbm-lessons', 'view_changed', { view: 'Category View' });
      }, false);
    } catch (e) {
      // no-op
    }
  }

  function initErrorCapture() {
    window.addEventListener('error', function (e) {
      try {
        if (errorCount >= MAX_CLIENT_ERRORS) {
          return;
        }
        errorCount++;
        var ctx = detectContext();
        var msg = (e && e.message) ? String(e.message).slice(0, 200) : 'Unknown error';
        logEvent(ctx.module, 'client_error', { view: ctx.view, detail: msg });
      } catch (err) {
        // no-op
      }
    });
  }

  function buildFeedbackUI() {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'rbm-test-monitor-btn';
    btn.textContent = 'Suggestions';

    var overlay = document.createElement('div');
    overlay.id = 'rbm-test-monitor-overlay';
    overlay.hidden = true;
    overlay.innerHTML =
      '<div id="rbm-test-monitor-modal" role="dialog" aria-modal="true" aria-label="Suggestions">' +
      '  <h2>Suggestions</h2>' +
      '  <p id="rbm-test-monitor-context"></p>' +
      '  <label for="rbm-tm-comment">What happened?</label>' +
      '  <textarea id="rbm-tm-comment" rows="4"></textarea>' +
      '  <label for="rbm-tm-type">Type</label>' +
      '  <select id="rbm-tm-type">' +
      '    <option>Problem</option><option>Suggestion</option><option>Question</option>' +
      '    <option>Usability</option><option>Data Issue</option><option>Other</option>' +
      '  </select>' +
      '  <label for="rbm-tm-severity">Severity</label>' +
      '  <select id="rbm-tm-severity">' +
      '    <option>Minor</option><option>Problem</option><option>Blocker</option>' +
      '  </select>' +
      '  <label for="rbm-tm-screenshot">Screenshot (optional)</label>' +
      '  <input type="file" id="rbm-tm-screenshot" accept="image/png,image/jpeg,image/gif,image/webp">' +
      '  <p id="rbm-tm-status" aria-live="polite"></p>' +
      '  <div id="rbm-test-monitor-actions">' +
      '    <button type="button" id="rbm-tm-cancel">Cancel</button>' +
      '    <button type="button" id="rbm-tm-submit">Submit</button>' +
      '  </div>' +
      '</div>';

    document.body.appendChild(btn);
    document.body.appendChild(overlay);

    var contextEl = overlay.querySelector('#rbm-test-monitor-context');
    var statusEl = overlay.querySelector('#rbm-tm-status');
    var commentEl = overlay.querySelector('#rbm-tm-comment');
    var typeEl = overlay.querySelector('#rbm-tm-type');
    var severityEl = overlay.querySelector('#rbm-tm-severity');
    var fileEl = overlay.querySelector('#rbm-tm-screenshot');

    function openModal() {
      var ctx = detectContext();
      contextEl.textContent = 'Page: ' + document.title + ' | View: ' + (ctx.view || 'N/A') + ' | Session: ' + (cfg.sessionId || 'N/A');
      statusEl.textContent = '';
      commentEl.value = '';
      overlay.hidden = false;
      commentEl.focus();
    }

    function closeModal() {
      overlay.hidden = true;
    }

    btn.addEventListener('click', openModal);
    overlay.querySelector('#rbm-tm-cancel').addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) {
        closeModal();
      }
    });

    overlay.querySelector('#rbm-tm-submit').addEventListener('click', function () {
      try {
        var comment = commentEl.value.trim();
        if (!comment) {
          statusEl.textContent = 'Please describe what happened.';
          return;
        }
        var ctx = detectContext();
        var fd = new FormData();
        fd.append('comment', comment);
        fd.append('feedback_type', typeEl.value);
        fd.append('severity', severityEl.value);
        fd.append('session_id', cfg.sessionId || '');
        fd.append('module', ctx.module);
        fd.append('view', ctx.view);
        fd.append('last_action', lastAction);
        fd.append('page_url', window.location.href);
        fd.append('page_title', document.title);
        fd.append('browser', (navigator.userAgent || '').slice(0, 120));
        fd.append('os', osGuess());
        fd.append('device_type', deviceType());
        if (fileEl.files && fileEl.files[0]) {
          fd.append('screenshot', fileEl.files[0]);
        }
        statusEl.textContent = 'Submitting...';
        fetch(cfg.restUrl + '/feedback', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'X-WP-Nonce': cfg.nonce },
          body: fd,
        }).then(function (resp) {
          return resp.json().catch(function () { return {}; });
        }).then(function (json) {
          if (json && json.logged) {
            statusEl.textContent = 'Thank you — your report was submitted.';
            setTimeout(closeModal, 1200);
          } else {
            statusEl.textContent = 'Could not submit right now. Please try again.';
          }
        }).catch(function () {
          statusEl.textContent = 'Could not submit right now. Please try again.';
        });
      } catch (e) {
        statusEl.textContent = 'Could not submit right now. Please try again.';
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    try {
      var ctx = detectContext();
      logEvent(ctx.module, 'page_loaded', { view: ctx.view, detail: ctx.category });
      initLessonsInstrumentation();
      initErrorCapture();
      buildFeedbackUI();
    } catch (e) {
      // no-op — never break the page.
    }
  });
})();
