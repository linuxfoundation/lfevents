document.addEventListener('DOMContentLoaded', () => {
  const manager = conblockpro_license_manager;
  const prefix = manager.prefix;
  const modal = document.getElementById(`${prefix}-license-modal`);
  const triggers = document.querySelectorAll(`.manage-license-trigger[data-prefix="${prefix}"]`);

  // Early exit if the modal container doesn't exist for this prefix
  if (!modal) {
    console.error(`License modal container not found for prefix: ${prefix}`);
    return;
  }

  // Add update class to plugin row
  const updateRow = document.querySelector(`.plugin-update-tr[data-plugin-slug="${manager.pluginSlug}"]`);
  if (updateRow) {
    const pluginRow = updateRow.previousElementSibling;
    if (pluginRow && pluginRow.getAttribute('data-plugin') === manager.pluginSlug) {
      pluginRow.classList.add('update');
    }
  }

  // Show modal
  for (const trigger of triggers) {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();

      // Query modal elements *after* it's confirmed to be interacted with
      const closeBtn = modal.querySelector('.license-modal-close');
      const activateBtn = modal.querySelector('.activate-license');
      const deactivateBtn = modal.querySelector('.deactivate-license');
      const licenseInput = modal.querySelector(`#${prefix}-license-key`);
      const messageDiv = modal.querySelector('.license-message');
      const statusDiv = modal.querySelector('.license-status');
      const checkBtn = modal.querySelector('.check-license');

      // Ensure close button exists before adding listener
      if (closeBtn) {
        // Ensure we don't add multiple listeners if clicked repeatedly
        if (!closeBtn.dataset.listenerAttached) {
          closeBtn.addEventListener('click', closeModal);
          closeBtn.dataset.listenerAttached = 'true';
        }
      } else {
        console.error('Modal close button not found.');
      }

      // Setup listeners for buttons inside the modal (only once)
      setupModalButtonListeners(activateBtn, deactivateBtn, checkBtn, licenseInput, messageDiv, statusDiv);

      modal.style.display = 'block';
    });
  }

  // Close modal function
  function closeModal() {
    modal.style.display = 'none';
  }

  // Close modal if clicking outside the content
  window.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeModal();
    }
  });

  // Function to setup button listeners (prevents duplicates)
  let listenersAttached = false;
  function setupModalButtonListeners(activateBtn, deactivateBtn, checkBtn, licenseInput, messageDiv, statusDiv) {
    if (listenersAttached) return;

    // Activate license
    if (activateBtn) {
      activateBtn.addEventListener('click', () => {
        const license = licenseInput.value.trim();
        if (!license) {
          showMessage(messageDiv, 'Please enter a license key', 'has-error');
          return;
        }

        activateBtn.textContent = manager.i18n.activating;
        activateBtn.disabled = true;

        performAjaxAction(
          'activate_license',
          { license_key: license },
          activateBtn,
          'Activate License',
          messageDiv,
          statusDiv,
        );
      });
    }

    // Deactivate license
    if (deactivateBtn) {
      deactivateBtn.addEventListener('click', () => {
        deactivateBtn.textContent = manager.i18n.deactivating;
        deactivateBtn.disabled = true;

        performAjaxAction('deactivate_license', {}, deactivateBtn, 'Deactivate License', messageDiv, statusDiv);
      });
    }

    // Check license status
    if (checkBtn) {
      checkBtn.addEventListener('click', () => {
        checkBtn.textContent = 'Checking...';
        checkBtn.disabled = true;

        performAjaxAction('check_license_status', {}, checkBtn, 'Check Status', messageDiv, statusDiv);
      });
    }

    listenersAttached = true; // Mark listeners as attached
  }

  // Refactored AJAX call logic
  function performAjaxAction(actionSuffix, additionalData, buttonElement, defaultButtonText, messageDiv, statusDiv) {
    const data = new FormData();
    data.append('action', `${prefix}_${actionSuffix}`);
    data.append('nonce', manager.nonce);
    for (const key in additionalData) {
      data.append(key, additionalData[key]);
    }

    fetch(manager.ajaxurl, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
    })
      .then((response) => response.json())
      .then((response) => {
        if (response.success) {
          showMessage(messageDiv, response.data.message, 'success');
          if (statusDiv && actionSuffix === 'check_license_status') {
            updateStatusDisplay(statusDiv, response.data.status);
          }
          // Reload only on activation/deactivation success
          if (actionSuffix === 'activate_license' || actionSuffix === 'deactivate_license') {
            setTimeout(() => window.location.reload(), 1500);
          }
        } else {
          showMessage(messageDiv, response.data.message, 'has-error');
        }
        // Always re-enable the button unless reload is happening
        if (!(response.success && (actionSuffix === 'activate_license' || actionSuffix === 'deactivate_license'))) {
          buttonElement.textContent = defaultButtonText;
          buttonElement.disabled = false;
        }
      })
      .catch((error) => {
        console.error('AJAX Error:', error);
        showMessage(messageDiv, manager.i18n.error, 'has-error');
        buttonElement.textContent = defaultButtonText;
        buttonElement.disabled = false;
      });
  }

  // Helper to update status display
  function updateStatusDisplay(statusDiv, status) {
    const statusText =
      status === 'valid'
        ? 'License Active'
        : status === 'expired'
          ? 'License Expired'
          : status === 'disabled' || status === 'revoked'
            ? 'License Revoked'
            : 'License Inactive';
    const statusClass =
      status === 'valid'
        ? 'valid'
        : status === 'expired'
          ? 'expired'
          : status === 'disabled' || status === 'revoked'
            ? 'revoked'
            : 'inactive';

    statusDiv.innerHTML = `<span class="status ${statusClass}">${statusText}</span>`;
  }

  // Helper to show messages
  function showMessage(messageDiv, message, type) {
    if (!messageDiv) return;
    messageDiv.textContent = message;
    messageDiv.className = `license-message ${type}`;
    messageDiv.style.display = 'block';
  }
});
