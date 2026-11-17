(function(){
 'use strict';
 var form=document.getElementById('ra-settings-form');if(!form)return;
 var tabs=Array.from(form.querySelectorAll('[data-ra-settings-tab]')),panels=Array.from(form.querySelectorAll('[data-ra-settings-panel]'));
 form.querySelector('.ra-settings-tabs').setAttribute('role','tablist');
 function show(name){
  tabs.forEach(function(tab){var active=tab.dataset.raSettingsTab===name;tab.setAttribute('aria-selected',String(active));tab.tabIndex=active?0:-1;});
  panels.forEach(function(panel){panel.hidden=panel.dataset.raSettingsPanel!==name;});
 }
 tabs.forEach(function(tab,index){
  var name=tab.dataset.raSettingsTab;tab.id='ra-tab-'+name;tab.setAttribute('role','tab');tab.setAttribute('aria-controls','ra-'+name);
  tab.addEventListener('click',function(e){e.preventDefault();show(name);});
  tab.addEventListener('keydown',function(e){var rtl=getComputedStyle(form).direction==='rtl',next;if(e.key==='ArrowRight')next=index+(rtl?-1:1);else if(e.key==='ArrowLeft')next=index+(rtl?1:-1);else if(e.key==='Home')next=0;else if(e.key==='End')next=tabs.length-1;else return;e.preventDefault();next=(next+tabs.length)%tabs.length;show(tabs[next].dataset.raSettingsTab);tabs[next].focus();});
 });
 panels.forEach(function(panel){panel.setAttribute('role','tabpanel');panel.setAttribute('aria-labelledby','ra-tab-'+panel.dataset.raSettingsPanel);});show('features');
 function preview(panel){var box=panel.querySelector('[data-ra-preview]');panel.querySelectorAll('[data-ra-color]').forEach(function(input){box.style.setProperty('--preview-'+input.dataset.raColor,input.value+(input.dataset.raColor==='button_radius'?'px':''));});}
 function statuses(){form.querySelectorAll('[data-ra-feature-card]').forEach(function(card){var enabled=card.querySelector('input[type=checkbox]').checked;card.dataset.enabled=String(enabled);card.querySelector('[data-ra-feature-status]').textContent=enabled?'فعال':'غیرفعال';});}
 function dirty(){form.querySelector('[data-ra-save-status]').textContent='تغییرات هنوز ذخیره نشده‌اند';}
 form.querySelectorAll('[data-ra-palette]').forEach(preview);statuses();
 form.addEventListener('input',function(event){var panel=event.target.closest('[data-ra-palette]');if(panel)preview(panel);statuses();dirty();});
 form.addEventListener('change',function(){statuses();dirty();});
 form.querySelector('[data-ra-apply-preset]').addEventListener('click',function(){
  var preset=(window.raPresets||{})[document.getElementById('ra-preset').value];if(!preset)return;
  form.querySelectorAll('[data-ra-palette]').forEach(function(panel){var palette=preset.palettes[panel.dataset.raPalette];panel.querySelectorAll('[data-ra-color]').forEach(function(input){input.value=palette[input.dataset.raColor];});preview(panel);});dirty();
 });
}());
