/**
 * SG Filehoster
 * Main JS File
 */

// CLIPBOARD ACTIONS
const clipboard = new Clipboard('.sg-copy-action');
clipboard.on('success', function(e) {
	// remove focus on copy element, because other clipboard actions
	// else might not work afterwards
	document.focus();
});

// UPLOAD FORM
// file input
const inputs = document.querySelectorAll('.sg-input-file input[type="file"]');
Array.prototype.forEach.call(inputs, function (input) {
	const label = input.nextElementSibling,
		labelVal = label.innerHTML;

	input.addEventListener('change', function (e) {
		let fileName = '';
		if (this.files && this.files.length > 1) {
			fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
		} else {
			fileName = e.target.value.split('\\').pop();
		}

		if (fileName) {
			label.innerHTML = fileName;
		} else {
			label.innerHTML = labelVal;
		}
	});
});