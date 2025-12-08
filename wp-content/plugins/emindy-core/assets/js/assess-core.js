// Shared helpers for assessments
window.emindyAssess = window.emindyAssess || {};

(function(NS){
  const FORM_HEADERS = { 'Content-Type': 'application/x-www-form-urlencoded' };

  async function sendRequest(params){
    if(!NS || !NS.ajax){
      throw new Error('Missing AJAX endpoint');
    }

    const response = await fetch(NS.ajax, {
      method: 'POST',
      headers: FORM_HEADERS,
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
      const j = await sendRequest({
        action: 'emindy_sign_result',
        _ajax_nonce: NS.nonce,
        type: type || '',
        score: score ?? ''
      });
      if(j && j.success && j.data && j.data.url){
        return j.data.url;
      }
    }catch(e){}
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

    try{
      const j = await sendRequest({
        action: 'emindy_send_assessment',
        _ajax_nonce: NS.nonce,
        kind: kind || '',
        summary: summary || '',
        email
      });
      if(j && j.success){
        alert('Sent ✔');
        return { ok: true };
      }
      alert((j && j.data) ? String(j.data) : 'Failed');
    }catch(e){
      alert('Failed');
    }
    return { ok: false };
  }

  NS.helpers = { signURL, emailSummary };
})(window.emindyAssess);

(function(NS){
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
    try{
      await fetch(NS.ajax, {
        method:'POST',
        headers: FORM_HEADERS,
        body: new URLSearchParams({
          action:'emindy_track',
          _ajax_nonce: NS.nonce,
          type,
          label,
          value,
          post
        })
      });
    }catch(e){}
  }
  NS.helpers = NS.helpers || {};
  NS.helpers.track = track;
})(window.emindyAssess = window.emindyAssess || {});
