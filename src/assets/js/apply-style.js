(function () {
	tinymce.PluginManager.add('apply_style', function (editor) {
		editor.addButton('apply_style', {
			text: 'Zatwierdź',
			icon: false,
			onclick: function () {
				const getDefaultTag = () => {
					const defaultTagText = document.querySelector(
						'#mceu_383-open .mce-txt'
					).textContent;
					const tagMap = {
						Akapit: 'p',
						'Nagłówek 1': 'h1',
						'Nagłówek 2': 'h2',
						'Nagłówek 3': 'h3',
						'Nagłówek 4': 'h4',
						'Nagłówek 5': 'h5',
						'Nagłówek 6': 'h6',
						'Wstępnie sformatowany': 'pre',
					};
					return tagMap[defaultTagText] || defaultTagText;
				};

				let selectedTag = editor.settings.selectedTag || 'p';
				let selectedStyle = editor.settings.selectedStyle || 'p';
				const selectedText = editor.selection.getContent();

				if (selectedStyle === 'Standardowy Styl') {
					selectedStyle = '';
				}

				if (selectedTag === 'Standardowy Tag') {
					selectedTag = getDefaultTag();
				}

				if (
					selectedTag === 'Standardowy Tag' &&
					selectedStyle === 'Standardowy Styl'
				) {
					return;
				}

				const content = `<${selectedTag} class="${selectedStyle}">${selectedText}</${selectedTag}>`;
				editor.execCommand('mceInsertContent', false, content);
			},
		});
	});
})();
