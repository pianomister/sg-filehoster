/**
 * SG Filehoster
 * Main JS File
 */

// UPLOAD FORM
// drop area
const dropArea = document.getElementById('drop-area');

if (dropArea) {

	// prevent default behavior for drag&drop
	['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
		dropArea.addEventListener(eventName, (e) => {
			e.preventDefault()
			e.stopPropagation()
		}, false);
	});

	// add/remove highlight effect on drag&drop
	['dragenter', 'dragover'].forEach(eventName => {
		dropArea.addEventListener(eventName, (e) => dropArea.classList.add('sg-active'), false);
	});

	['dragleave', 'drop'].forEach(eventName => {
		dropArea.addEventListener(eventName, (e) => dropArea.classList.remove('sg-active'), false);
	});

	// catch file via drop event
	dropArea.addEventListener('drop', handleFiles, false);

	// catch file via button event
	const input = document.querySelector('#drop-area input');
	input.addEventListener('change', handleFiles, false);

	function handleFiles(e) {
		const label = document.querySelector('#drop-area label'),
			input = document.querySelector('#drop-area input'),
			labelVal = label.innerHTML;
		let fileName = '',
			files;

		if (e.dataTransfer) {
			files = e.dataTransfer.files;
		} else if (e.target) {
			files = e.target.files;
		}

		console.log(files, e);

		// set form value
		input.files = files;

		if (files && files.length > 1) {
			fileName = (input.getAttribute('data-multiple-caption') || '').replace('{count}', files.length);
		} else {
			fileName = files[0].name;
		}

		if (fileName) {
			label.innerHTML = fileName;
		} else {
			label.innerHTML = labelVal;
		}
	}
}