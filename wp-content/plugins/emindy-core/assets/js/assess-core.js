// Shared helpers for assessments
window.emindyAssess = window.emindyAssess || {};
(function(NS){
  async function signURL(type, score){
    try{
      const res = await fetch(NS.ajax, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action:'emindy_sign_result',
          _ajax_nonce: NS.nonce,
          type, score
        })
      });
      const j = await res.json();
      if(j && j.success && j.data && j.data.url) return j.data.url;
    }catch(e){}
    throw new Error('sign failed');
  }

  async function emailSummary(kind, summary){
    const email = prompt('Enter your email:'); if(!email) return {cancel:true};
    const res = await fetch(NS.ajax, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        action:'emindy_send_assessment',
        _ajax_nonce: NS.nonce,
        kind, summary, email
      })
    });
    const j = await res.json();
    if(j && j.success) { alert('Sent ✔'); return {ok:true}; }
    alert( (j && j.data) ? j.data : 'Failed' );
    return {ok:false};
  }

  NS.helpers = { signURL, emailSummary };
})(window.emindyAssess);

(function(NS){
  async function track(type, label='', value='', post=0){
    try{
      await fetch(NS.ajax, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action:'emindy_track', _ajax_nonce:NS.nonce,
          type, label, value, post
        })
      });
    }catch(e){}
  }
  NS.helpers = NS.helpers || {};
  NS.helpers.track = track;
})(window.emindyAssess = window.emindyAssess || {});

