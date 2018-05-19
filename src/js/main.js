/**
 * SG Filehoster
 * Main JS File
 */

// CLIPBOARD ACTIONS
const copySelector = '.sg-copy-action';
const clipboard = new ClipboardJS(copySelector);
clipboard.on('success', function(e) {
	msg = e.trigger.getAttribute('data-copy-success');
	showTooltip(e.trigger, msg);
});
clipboard.on('error', function(e) {
	msg = e.trigger.getAttribute('data-copy-error');
	showTooltip(e.trigger, msg);
});

// TOOLTIPS FOR CLIPBOARD ACTIONS
function clearTooltip(elem) {
    elem.classList.remove('sg-tooltipped', 'sg-tooltipped-s');
		elem.removeAttribute('aria-label');
		// when copying multiple links, focus on previous element
		// prevents copy of the following link
		document.firstElementChild.focus();
}

function showTooltip(elem, msg) {
    elem.classList.add('sg-tooltipped', 'sg-tooltipped-s');
		elem.setAttribute('aria-label', msg);
		window.setTimeout(function() {
			clearTooltip(elem);
		}, 2000);
}

// UPLOAD FORM
// file input
const inputs = document.querySelectorAll('.sg-input-file input[type="file"]');
Array.prototype.forEach.call(inputs, function (input) {
	const label = input.nextElementSibling,
		labelVal = label.querySelector('span').innerHTML;

	input.addEventListener('change', function (e) {
		let fileName = '';
		if (this.files && this.files.length > 1) {
			fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
		} else {
			fileName = e.target.value.split('\\').pop();
		}

		if (fileName) {
			label.querySelector('span').innerHTML = fileName;
		} else {
			label.querySelector('span').innerHTML = labelVal;
		}
	});
});