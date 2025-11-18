(function () {
    tinymce.PluginManager.add('factbox_buttons', function (editor, url) {
        editor.addButton('factboxright_button', {
            text: 'Faktaboks - Højre',
            icon: false,
            onclick: function () {
                var html = '' +
                    '<div class="factboxright">\n' +
                    '<p><strong>Overskrift</strong></p>\n' +
                    '<ul>\n' +
                    '<li>Listepunkt 1</li>\n' +
                    '<li>Listepunkt 2</li>\n' +
                    '<li>...</li>\n' +
                    '<li>Listepunkt x</li>\n' +
                    '</ul>\n' +
                    '</div>\n';

                editor.insertContent(html);
            }
        });

        editor.addButton('factboxleft_button', {
            text: 'Faktaboks - Venstre',
            icon: false,
            onclick: function () {
                var html = '' +
                    '<div class="factboxleft">\n' +
                    '<p><strong>Overskrift</strong></p>\n' +
                    '<ul>\n' +
                    '<li>Listepunkt 1</li>\n' +
                    '<li>Listepunkt 2</li>\n' +
                    '<li>...</li>\n' +
                    '<li>Listepunkt x</li>\n' +
                    '</ul>\n' +
                    '</div>\n';

                editor.insertContent(html);
            }
        });
    });
})();
