<?php
queue_js_file('vendor/tinymce/tinymce.min');
$head = array('bodyclass' => 'babela primary browse',
              'title' => __('Babela | Translate Simple Page'));
echo head($head);
echo flash();
?>

<script type="text/javascript">
$ = jQuery;

$(window).on( "load", function() {
        // Default parameters
/*
        initParams = {
            convert_urls: false,
            selector: "textarea",
            menubar: false,
            statusbar: true,
            toolbar_items_size: "small",
            toolbar: ["bold italic underline strikethrough | sub sup | forecolor backcolor | link | formatselect code | superscript subscript ", "hr | alignleft aligncenter alignright alignjustify | indent outdent | bullist numlist | pastetext, pasteword | charmap | media | image | anchor"],
            plugins: "lists,link,code,paste,autoresize,media,charmap,hr,textcolor,image,anchor",
            autoresize_max_height: 500,
            entities: "160,nbsp,173,shy,8194,ensp,8195,emsp,8201,thinsp,8204,zwnj,8205,zwj,8206,lrm,8207,rlm",
            verify_html: false,
            add_unload_trigger: false

        };

        tinymce.init($.extend(initParams));
*/
    Omeka.wysiwyg({
        selector: 'babel-use-html',
        menubar: 'edit view insert format table',
//         plugins: 'lists link code paste media autoresize image table charmap hr',
            toolbar: ["bold italic underline strikethrough | sub sup | forecolor backcolor | link | anchor | formatselect code | superscript subscript", "hr | alignleft aligncenter alignright alignjustify | indent outdent | bullist numlist | table | pastetext, pasteword | charmap | media | image"],
            plugins: "lists,link,code,paste,autoresize,media,charmap,hr,table,textcolor,image, anchor",
        browser_spellcheck: true
    });

    $('.babel-use-tiny-mce').each(function(e, val) {
      var el = $(this).attr('id');
      var textareaId = 'text-' + el.slice(-2) + '-text'  + el.slice(-2);
      if ($(this).is(':checked')) {
        tinyMCE.EditorManager.execCommand("mceAddEditor", false, textareaId);
      } else {
        tinyMCE.EditorManager.execCommand("mceRemoveEditor", false, textareaId);
      }
    });

    // Add or remove TinyMCE control.
    $('.babel-use-tiny-mce').on( "click", function() {
      var el = $(this).attr('name');
      var textareaId = 'text-' + el.slice(-2) + '-text'  + el.slice(-2);
        if ($(this).is(':checked')) {
          tinyMCE.EditorManager.execCommand("mceAddEditor", false, textareaId);
        } else {
          tinyMCE.EditorManager.execCommand("mceRemoveEditor", false, textareaId);
        }
    });
});
</script>

<?php
 echo $form;
 echo foot();
?>

