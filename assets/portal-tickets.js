(() => {
  'use strict';
  document.querySelectorAll('.rp-form').forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) return;
      if (form.dataset.submitting) { event.preventDefault(); return; }
      form.dataset.submitting = 'true';
      // Keep the submitted button value when disabling duplicate submits.
      if (event.submitter && event.submitter.name) {
        const hidden = document.createElement('input'); hidden.type='hidden'; hidden.name=event.submitter.name; hidden.value=event.submitter.value; form.appendChild(hidden);
      }
      form.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = true; });
    });
  });
})();
