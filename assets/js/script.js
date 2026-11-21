// Small interface enhancements only. Authentication always happens in PHP.
(function () {
  'use strict';

  const toggleButton = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');

  if (toggleButton && passwordInput) {
    toggleButton.addEventListener('click', function () {
      const showPassword = passwordInput.type === 'password';
      passwordInput.type = showPassword ? 'text' : 'password';
      toggleButton.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
      toggleButton.setAttribute('aria-pressed', String(showPassword));
      const showIcon = toggleButton.querySelector('.eye-icon--show');
      const hideIcon = toggleButton.querySelector('.eye-icon--hide');
      if (showIcon) showIcon.style.display = showPassword ? 'none' : '';
      if (hideIcon) hideIcon.style.display = showPassword ? '' : 'none';
    });
  }

  const loginForm = document.getElementById('loginForm');
  const submitButton = document.getElementById('submitBtn');
  if (loginForm && submitButton) {
    loginForm.addEventListener('submit', function () {
      if (loginForm.checkValidity()) {
        submitButton.classList.add('btn--loading');
        submitButton.disabled = true;
      }
    });
  }

  const logoutButton = document.getElementById('logoutBtn');
  if (logoutButton) {
    logoutButton.addEventListener('click', function (event) {
      if (!window.confirm('Are you sure you want to sign out?')) event.preventDefault();
    });
  }
})();
