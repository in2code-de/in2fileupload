const target = document.getElementById('files-drag-drop');

if (target !== null) {
	const configuration = JSON.parse(target.dataset.configuration);

	// keep the configuration available as global for integrators
	window.in2fileupload = configuration;

	const showError = (title, message, duration) => {
		top.TYPO3.Notification.error(title, message, duration);
	};

	// TYPO3 sets data-color-scheme="light|dark" on <html>, "auto" (no attribute) follows the OS setting.
	// "auto" is resolved here, as Uppy only listens to OS changes if its theme was "auto" on initialization
	const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
	const getTheme = () => {
		const colorScheme = document.documentElement.dataset.colorScheme;
		if (colorScheme === 'light' || colorScheme === 'dark') {
			return colorScheme;
		}

		return darkModeMediaQuery.matches ? 'dark' : 'light';
	};

	let uppyConfiguration = {
		restrictions: {
			maxFileSize: configuration.maxFileSize,
			requiredMetaFields: configuration.requiredMetaFields,
		},
	};

	if (configuration.backendLanguage === 'de') {
		uppyConfiguration.locale = Uppy.locales.de_DE;
	}

	if (configuration.allowedFileTypes.length > 0) {
		uppyConfiguration.restrictions.allowedFileTypes = configuration.allowedFileTypes;
	}

	const uppy = new Uppy.Uppy(uppyConfiguration)
		.use(Uppy.Dashboard, {
			inline: true,
			target: target,
			showProgressDetails: true,
			proudlyDisplayPoweredByUppy: false,
			disableThumbnailGenerator: true,
			width: '100%',
			height: '800px',
			singleFileFullScreen: false,
			metaFields: configuration.metaFields,
			theme: getTheme(),
		});

	// follow color scheme changes from the TYPO3 user menu and the OS without reload
	const updateTheme = () => {
		uppy.getPlugin('Dashboard').setOptions({theme: getTheme()});
	};
	new MutationObserver(updateTheme)
		.observe(document.documentElement, {attributes: true, attributeFilter: ['data-color-scheme']});
	darkModeMediaQuery.addEventListener('change', updateTheme);
	uppy.setMeta({in2fileupload__folderIdentifier: configuration.targetFolder});
	uppy.use(Uppy.XHRUpload, {
		endpoint: top.TYPO3.settings.ajaxUrls.in2fileupload_upload,
		allowedMetaFields: configuration.allowedMetaFields,
		validateStatus(status, responseText, response) {
			const result = JSON.parse(responseText);
			if (status === 200) {
				if (result.success) {
					return true;
				}

				if (result.errors) {
					for (let key in result.errors) {
						showError(result.errors[key].title, result.errors[key].message);
					}
				}
			}

			return false;
		},
	});
	uppy.on('info-visible', () => {
		const {info} = uppy.getState();
		info.forEach((information) => {
			if (information.type === 'error') {
				showError(information.message, information.details, 5);
			}
		});
	});

	// workaround for issue: https://github.com/transloadit/uppy/issues/3769
	uppy.on('dashboard:file-edit-complete', (file) => {
		if (file === undefined) {
			return;
		}

		if (file.missingRequiredMetaFields !== undefined && file.missingRequiredMetaFields.length > 0) {
			const validate = (file) => {
				const requiredMetaFields = configuration.requiredMetaFields;

				requiredMetaFields.forEach((requiredField) => {
					if (file.meta[requiredField] !== '') {
						const index = file.missingRequiredMetaFields.indexOf(requiredField);
						if (index !== -1) {
							file.missingRequiredMetaFields.splice(index, 1);
						}
					}
				});
			}
			validate(file);
			uppy.resetProgress();
		}
	});
}
