/**
 * Adds a custom QuickTags button that wraps selected text in a specified HTML tag with optional class names.
 *
 * @param {string} id - The unique identifier for the QuickTags button.
 * @param {string} name - The name or label for the QuickTags button.
 */
function addQButton(id, name) {
	QTags.addButton(id, name, () => {
		const tag = prompt('Wpisz tag [div, span, p...]: ', '');
		const className = prompt('Nazwy klas: ', '');
		wrapWithClass(tag, className);
	});
}

/**
 * Wraps the selected text in the specified HTML tag with optional class names within all textareas on the page.
 *
 * @param {string} tag - The HTML tag to wrap the selected text with (e.g., 'div', 'span', 'p').
 * @param {string} className - The class names to apply to the wrapped element.
 */
function wrapWithClass(tag, className) {
	const textAreas = document.getElementsByTagName('textarea');
	for (const textArea of textAreas) {
		const { selectionStart, selectionEnd } = textArea;
		const selectedText = textArea.value.substring(selectionStart, selectionEnd);

		if (selectedText) {
			const wrappedText = wrapSelectedText(selectedText, tag, className);
			textArea.value =
				textArea.value.substring(0, selectionStart) +
				wrappedText +
				textArea.value.substring(selectionEnd);
		}
	}
}

/**
 * Wraps the selected text in the specified HTML tag with optional class names.
 * If the selected text is already wrapped in a tag, it updates the tag and class attributes.
 *
 * @param {string} text - The selected text to wrap.
 * @param {string} tag - The HTML tag to wrap the selected text with (e.g., 'div', 'span', 'p').
 * @param {string} className - The class names to apply to the wrapped element.
 * @returns {string} The wrapped text.
 */
function wrapSelectedText(text, tag, className) {
	const regex = /<(\w+)([^>]*)>(.*?)<\/\1>/;
	if (regex.test(text)) {
		return text.replace(regex, (_, currentTag, attributes, innerText) => {
			const updatedTag = tag || currentTag;
			const newClass = updateClass(attributes, className);
			return `<${updatedTag}${newClass}>${innerText}</${updatedTag}>`;
		});
	}

	const defaultTag = tag || 'p';
	const classAttr = className ? ` class="${className}"` : '';
	return `<${defaultTag}${classAttr}>${text}</${defaultTag}>`;
}

/**
 * Updates the class attribute in the provided attributes string, adding the new class name.
 *
 * @param {string} attributes - The HTML tag attributes string.
 * @param {string} newClass - The class name to add or update.
 * @returns {string} The updated attributes string with the new class name.
 */
function updateClass(attributes, newClass) {
	const classMatch = /class=['"]([^'"]*)['"]/;
	const existingClasses = classMatch.test(attributes)
		? classMatch.exec(attributes)[1].split(' ')
		: [];

	// Remove any existing duplicates to prevent from
	// multiple classes to have the same name
	const uniqueClasses = existingClasses.filter(
		(className) => className !== newClass
	);

	if (newClass) {
		uniqueClasses.push(newClass);
	}

	const updatedClass = uniqueClasses.join(' ');
	return updatedClass ? ` class="${updatedClass}"` : '';
}

addQButton('custom_class_btn', 'custom');
