(function(){
 'use strict';
 var config=window.riffsideAccount||{},root=document.querySelector('.riffside-account');
 if(!root)return;
 var policy=['light','dark','both'].includes(config.mode)?config.mode:'light';
 var initial=config.defaultMode==='dark'?'dark':'light',mode=policy==='both'?initial:policy;
 if(policy==='both'){try{var saved=localStorage.getItem('riffside-account-mode');if(saved==='dark'||saved==='light')mode=saved;}catch(error){}}
 function apply(){
  root.dataset.raTheme=mode;
  document.dispatchEvent(new CustomEvent('riffside:appearance',{detail:{mode:mode}}));
  root.querySelectorAll('[data-ra-theme-toggle]').forEach(function(toggle){
   toggle.textContent=mode==='dark'?'تم روشن':'تم تیره';
   toggle.setAttribute('aria-pressed',String(mode==='dark'));
  });
 }
 apply();
 window.addEventListener('storage',function(event){if(policy!=='both'||(event.key!=='riffside-account-mode'&&event.key!==null))return;try{var saved=localStorage.getItem('riffside-account-mode');mode=saved==='light'||saved==='dark'?saved:initial;}catch(error){mode=initial;}apply();});
 root.addEventListener('click',function(event){
  var toggle=event.target.closest('[data-ra-theme-toggle]');
  if(toggle&&policy==='both'){mode=mode==='dark'?'light':'dark';apply();try{localStorage.setItem('riffside-account-mode',mode);}catch(error){}}
  var copy=event.target.closest('[data-ra-copy]');
  if(copy){var status=copy.parentElement.querySelector('[data-ra-copy-status]');if(navigator.clipboard&&window.isSecureContext)navigator.clipboard.writeText(copy.dataset.raCopy).then(function(){status.textContent='کد کپی شد.';},function(){status.textContent='کد را انتخاب و کپی کن.';});else status.textContent='کد را انتخاب و کپی کن.';}
 });
 var sidebar=root.querySelector('#ra-sidebar'),menu=root.querySelector('[data-ra-menu-toggle]'),backdrop=root.querySelector('.ra-menu-backdrop'),workspace=root.querySelector('.ra-workspace'),mobile=matchMedia('(max-width:760px)'),opened=false;
 root.classList.add('ra-enhanced');
 function setMenu(open,restore){
  opened=mobile.matches&&open;root.classList.toggle('ra-menu-is-open',opened);document.body.classList.toggle('ra-menu-open',opened);
  menu.setAttribute('aria-expanded',String(opened));backdrop.hidden=!opened;sidebar.inert=mobile.matches&&!opened;workspace.inert=opened;
  if(mobile.matches){sidebar.setAttribute('role','dialog');sidebar.setAttribute('aria-modal','true');sidebar.setAttribute('aria-hidden',String(!opened));}
  else{sidebar.removeAttribute('role');sidebar.removeAttribute('aria-modal');sidebar.removeAttribute('aria-hidden');}
  if(opened)sidebar.querySelector('[data-ra-menu-close]').focus();else if(restore)menu.focus();
 }
 menu.addEventListener('click',function(){setMenu(!opened,true);});
 root.querySelectorAll('[data-ra-menu-close]').forEach(function(button){button.addEventListener('click',function(){setMenu(false,true);});});
 document.addEventListener('keydown',function(event){
  if(!opened)return;
  if(event.key==='Escape'){event.preventDefault();setMenu(false,true);}
  if(event.key==='Tab'){
   var focusable=Array.from(sidebar.querySelectorAll('a[href],button:not([disabled])')).filter(function(el){return el.getClientRects().length;});
   var first=focusable[0],last=focusable[focusable.length-1];
   if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus();}
   else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();}
  }
 });
 mobile.addEventListener('change',function(){setMenu(false,false);});setMenu(false,false);
}());

