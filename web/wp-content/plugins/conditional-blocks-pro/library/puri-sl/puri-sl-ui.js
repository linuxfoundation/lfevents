/* global conblockpro_puri_sl */
document.addEventListener('DOMContentLoaded', function (e) {
  // eslint-disable-line

	const enterLicenseLink = document.querySelector('.conblockpro-puri-sl-manage-link');

	const licenseRow = document.querySelector('.conblockpro-puri-sl-row');

	const pluginRow = document.querySelector(`tr[data-plugin="${conblockpro_puri_sl.slug}"`);

	if (pluginRow && conblockpro_puri_sl.license_status !== 'valid') {
		pluginRow.classList.add('update');
	}

	/**
	 * Show the license row.
	 */
	enterLicenseLink.addEventListener('click', function (event) {
		event.preventDefault();

		if (licenseRow.style.display === 'none') {
			licenseRow.style.display = '';
		} else {
			licenseRow.style.display = 'none';
		}
	});

	const activateActionButton = document.querySelectorAll('.conblockpro-puri-sl-button');

	for (const button of activateActionButton) {
		button.addEventListener('click', function (event) {
			event.preventDefault();

			const dashicon = this.querySelector('.dashicons');

			dashicon.classList.remove('dashicons-yes-alt');
			dashicon.classList.add('dashicons-update');
			dashicon.classList.add('conblockpro-puri-sl-spin');

			const nonce = this.getAttribute('data-nonce');
			const action = this.getAttribute('data-action');
			const operation = this.getAttribute('data-operation');
			const license = document.querySelector('.conblockpro-puri-sl-license-key').value;

			const params = {
				nonce,
				action,
				operation,
				license
			};

			fetch(conblockpro_puri_sl.ajax_url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'Cache-Control': 'no-cache'
				},
				body: new URLSearchParams(params)
			})
				.then((response) => response.json())
				.then((response) => {
					const message = document.querySelector('.conblockpro-puri-sl-message');

					if (message) {
						message.innerHTML = `<span style="margin-right:3px;" class="dashicons dashicons-info"></span>${response.data.message}`;
					}

					if (response.success === true) {
						dashicon.classList.remove('dashicons-update', 'conblockpro-puri-sl-spin');
						dashicon.classList.add('dashicons-yes-alt');

						if (operation === 'change_beta') {
							this.innerHTML = '<span class="dashicons dashicons-hammer"></span> Revert';
						}

						if (response.data.reload === true) {
							location.reload(false);
						}
						return;
					}
					dashicon.classList.remove('dashicons-update', 'conblockpro-puri-sl-spin');
					dashicon.classList.add('dashicons-dismiss');
				})
				.catch((err) => console.log(err));
		});
	}
});
