const dialog = document.querySelector('[data-rsvp-confirm]');
if (dialog) {
  document.querySelectorAll('[data-rsvp-delete]').forEach((button) => {
    button.addEventListener('click', () => {
      dialog.querySelector('[data-rsvp-delete-form]').action =
        button.dataset.rsvpDelete;
      dialog.querySelector('[data-rsvp-delete-name]').textContent =
        button.dataset.name;
      dialog.showModal();
      dialog.querySelector('[data-rsvp-close]').focus();
    });
  });
  dialog
    .querySelector('[data-rsvp-close]')
    .addEventListener('click', () => dialog.close());
}
document
  .querySelector('[data-rsvp-per-page]')
  ?.addEventListener('change', (event) => {
    event.target.form.requestSubmit();
  });
