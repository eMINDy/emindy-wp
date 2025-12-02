(function(){
  function apply(theme){
    document.documentElement.setAttribute('data-theme', theme);
    try{ localStorage.setItem('emindy_theme', theme); }catch(e){}
    document.querySelectorAll('[data-action="toggle-theme"]').forEach(function(btn){
      btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
      btn.textContent = theme === 'dark' ? '🌞' : '🌓';
      btn.title = theme === 'dark' ? 'Switch to Light' : 'Switch to Dark';
    });
  }
  function current(){
    return document.documentElement.getAttribute('data-theme') || 'light';
  }
  function toggle(){ apply( current() === 'dark' ? 'light' : 'dark' ); }

  // روی کلیک دکمه‌های سوییچ
  document.addEventListener('click', function(e){
    var t = e.target.closest('[data-action="toggle-theme"]');
    if(!t) return;
    e.preventDefault();
    toggle();
  });

  // همگام با تغییر سیستم
  try{
    var mm = window.matchMedia('(prefers-color-scheme: dark)');
    mm.addEventListener('change', function(ev){
      var stored = localStorage.getItem('emindy_theme');
      if (!stored) apply(ev.matches ? 'dark' : 'light'); // فقط اگر کاربر دستی عوض نکرده
    });
  }catch(e){}

  // آماده‌سازی اولیه بعد از DOM
  document.addEventListener('DOMContentLoaded', function(){
    apply( current() );
  });
})();
