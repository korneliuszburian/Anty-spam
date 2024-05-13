(function () {
	tinymce.PluginManager.add('style_selector', function (editor) {
		var styles = [
			{ title: 'Header 1', format: 'h1' },
			{ title: 'Header 2', format: 'h2' },
			{ title: 'Header 3', format: 'h3' },
			{ title: 'Paragraph', format: 'p' },
			{ title: 'Div', format: 'div' },
		];

		editor.addButton('style_selector', {
			type: 'listbox',
			text: 'Style',
			icon: false,
			onselect: function () {
				var format = this.value();
				var selectedNode = editor.selection.getNode();

				if (format && selectedNode) {
					editor.undoManager.transact(function () {
						editor.formatter.apply(format, {}, selectedNode);
						editor.fire('change');
					});
				}
			},
			values: styles,
			onPostRender: function () {
				this.value(styles[0].format);
			},
		});
	});
})();
