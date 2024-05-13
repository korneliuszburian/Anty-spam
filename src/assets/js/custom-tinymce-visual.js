/**
 * Adds a custom TinyMCE button that wraps selected text in a specified HTML tag with specified class names.
 *
 */
(function () {
	tinymce.PluginManager.add('my_mce_button', function (editor, url) {
		var tags = ['Standardowy Tag', 'h1', 'h2', 'h3', 'p', 'div'];
		var styles = ['Standardowy Styl', 'h1', 'h2', 'h3', 'p', 'div'];

		// Add a Dropdown for Tags
		editor.addButton('my_mce_button', {
			type: 'listbox',
			text: 'Tag',
			icon: false,
			onselect: function (e) {
				editor.settings.selectedTag = this.value();
			},
			values: tags.map(function (tag) {
				return { text: tag, value: tag };
			}),
			onPostRender: function () {
				this.value(tags[0]);
				editor.settings.selectedTag = tags[0];
			},
		});

		editor.addButton('style_selector', {
			type: 'listbox',
			text: 'Style',
			icon: false,
			onselect: function (e) {
				editor.settings.selectedStyle = this.value();
			},
			values: styles.map(function (style) {
				return { text: style, value: style };
			}),
			onPostRender: function () {
				this.value(styles[0]);
				editor.settings.selectedStyle = styles[0];
			},
		});
	});
})();
