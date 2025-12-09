// Shared helpers for assessments
window.emindyAssess = window.emindyAssess || {};

(function(NS){
  'use strict';

  const FORM_HEADERS = Object.freeze({ 'Content-Type': 'application/x-www-form-urlencoded' });

  /**
   * Send a POST request to the configured AJAX endpoint.
   * @param {Record<string, string|number>} params Request parameters.
   * @returns {Promise<any>} Parsed JSON response.
   */
  async function sendRequest(params){
    if(!NS || typeof NS.ajax !== 'string' || !NS.ajax){
      throw new Error('Missing AJAX endpoint');
    }

    const response = await fetch(NS.ajax, {
      method: 'POST',
      headers: FORM_HEADERS,
      credentials: 'same-origin',
      body: new URLSearchParams(params)
    });

    if(!response.ok){
      throw new Error('Request failed');
    }

    return response.json();
  }

  /**
   * Sign and return a shareable URL for an assessment score.
   * @param {string} type Assessment type key.
   * @param {(string|number)} score Numeric score to sign.
   * @returns {Promise<string>} Signed URL string.
   */
  async function signURL(type, score){
    try{
      const safeType = typeof type === 'string' ? type : '';
      const safeScore = typeof score === 'number' || typeof score === 'string' ? score : '';

      const j = await sendRequest({
        action: 'emindy_sign_result',
        _ajax_nonce: typeof NS.nonce === 'string' ? NS.nonce : '',
        type: safeType,
        score: safeScore
      });
      if(j && j.success && j.data && j.data.url){
        return j.data.url;
      }
    }catch(e){
      console.error(e);
    }
    throw new Error('sign failed');
  }

  /**
   * Prompt for an email address and request the assessment summary be sent.
   * @param {string} kind Assessment kind.
   * @param {string} summary Summary body text.
   * @returns {Promise<{ok?:boolean,cancel?:boolean}>} Result status.
   */
  async function emailSummary(kind, summary){
    const emailInput = prompt('Enter your email:');
    const email = typeof emailInput === 'string' ? emailInput.trim() : '';

    if(!email){
      return { cancel: true };
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailPattern.test(email)){
      alert('Please enter a valid email.');
      return { cancel: true };
    }

    try{
      const safeKind = typeof kind === 'string' ? kind : '';
      const safeSummary = typeof summary === 'string' ? summary : String(summary ?? '');

      const j = await sendRequest({
        action: 'emindy_send_assessment',
        _ajax_nonce: typeof NS.nonce === 'string' ? NS.nonce : '',
        kind: safeKind,
        summary: safeSummary,
        email
      });
      if(j && j.success){
        alert('Sent ✔');
        return { ok: true };
      }
      alert((j && j.data) ? String(j.data) : 'Failed');
    }catch(e){
      console.error(e);
      alert('Failed');
    }
    return { ok: false };
  }

  NS.helpers = { signURL, emailSummary };
})(window.emindyAssess);

(function(NS){
  'use strict';

  const FORM_HEADERS = Object.freeze({ 'Content-Type': 'application/x-www-form-urlencoded' });

  /**
   * Track an assessment-related interaction via AJAX.
   * @param {string} type Event type.
   * @param {string} [label]
   * @param {string|number} [value]
   * @param {number} [post]
   * @returns {Promise<void>}
   */
  async function track(type, label = '', value = '', post = 0){
    if(!type){
      return;
    }
    if(!NS || typeof NS.ajax !== 'string' || !NS.ajax){
      return;
    }
    try{
      await fetch(NS.ajax, {
        method:'POST',
        headers: FORM_HEADERS,
        body: new URLSearchParams({
          action:'emindy_track',
          _ajax_nonce: typeof NS.nonce === 'string' ? NS.nonce : '',
          type: typeof type === 'string' ? type : '',
          label: typeof label === 'string' ? label : '',
          value: typeof value === 'string' || typeof value === 'number' ? value : '',
          post: Number.isFinite(post) ? post : 0
        })
      });
    }catch(e){
      console.error(e);
    }
  }
  NS.helpers = NS.helpers || {};
  NS.helpers.track = track;
})(window.emindyAssess = window.emindyAssess || {});
