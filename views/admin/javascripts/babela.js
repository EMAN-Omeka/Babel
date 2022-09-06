$ = jQuery;

/**
 * Enable the WYSIWYG editor for "html-editor" fields on the form, and allow
 * checkboxes to create editors for more fields.
 *
 * @param {Element} element The element to search at and below.
 */
$(document).ready(function() {
    Omeka.Elements.enableWysiwyg = function (element) {
        $(element).find('div.inputs .use-html-checkbox').each(function () {
            var textarea = $(this).parents('.input-block').find('textarea');
            if (textarea.length) {
                var enableIfChecked = function () {
                    checkBox = this;
                    $(textarea).each(function(i, ta) {
                      var textareaId = $(ta).attr('id');
                      if (checkBox.checked) {
                          tinyMCE.EditorManager.execCommand("mceAddEditor", false, textareaId);
                      } else {
                          tinyMCE.EditorManager.execCommand("mceRemoveEditor", false, textareaId);
                      }
                    });
                };

                enableIfChecked.call(this);

                // Whenever the checkbox is toggled, toggle the WYSIWYG editor.
                $(this).click(enableIfChecked);
            }
        });
    };
  Omeka.Elements.enableWysiwyg('#item-form');
})
