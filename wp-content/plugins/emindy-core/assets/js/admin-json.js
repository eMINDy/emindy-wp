(function($){
  function validate(id, statusId){
    var txt = $(id).val().trim();
    var $status = $(statusId);
    if(!txt){ $status.text(''); return; }
    try { JSON.parse(txt); $status.text(emindyAdmin.valid).css({color:'#1e7e34'}); }
    catch(e){ $status.text(emindyAdmin.invalid).css({color:'#c00'}); }
  }
  $(document).on('input', '#em_chapters_json', function(){ validate('#em_chapters_json','#em_chapters_json_status'); });
  $(document).on('input', '#em_steps_json', function(){ validate('#em_steps_json','#em_steps_json_status'); });
  $(function(){ validate('#em_chapters_json','#em_chapters_json_status'); validate('#em_steps_json','#em_steps_json_status'); });
})(jQuery);
