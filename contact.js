(function () {
  const form = document.getElementById('contactForm');
  if (!form) return;

  const status = document.getElementById('contactStatus');
  const button = form.querySelector('button[type="submit"]');

  function setStatus(message, isError) {
    status.textContent = message;
    status.classList.toggle('is-error', Boolean(isError));
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    button.disabled = true;
    setStatus('Sending...', false);

    try {
      const response = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.error || 'Unable to send message.');
      form.reset();
      setStatus('Thank you. Your message has been received.', false);
    } catch (error) {
      setStatus(error.message || 'Unable to send message.', true);
    } finally {
      button.disabled = false;
    }
  });
})();
