<?php
queue_js_file('vendor/tiny_mce/tiny_mce');
$head = array('bodyclass' => 'babel primary', 
              'title' => __('Babel | Translate Simple Page'));
echo head($head);
echo flash(); 
?>
 
<script type="text/javascript">
jQuery(window).load(function() {
    // Initialize and configure TinyMCE.
    tinyMCE.init({
        // Assign TinyMCE a textarea:
        mode : 'exact',
        elements: 'babel-use-html',
        // Add plugins:
        plugins: 'media,paste,inlinepopups',
        // Configure theme:
        theme: 'advanced',
        theme_advanced_toolbar_location: 'top',
        theme_advanced_toolbar_align: 'left',
        theme_advanced_buttons3_add : 'pastetext,pasteword,selectall',
        // Allow object embed. Used by media plugin
        // See http://www.tinymce.com/forum/viewtopic.php?id=24539
        media_strict: false,
        // General configuration:
        convert_urls: false,
    });
    // Add or remove TinyMCE control.
    jQuery('.babel-use-tiny-mce').click(function() {
      var el = jQuery(this).attr('name');
      var textarea = 'text-' + el.slice(-2) + '-text'  + el.slice(-2);
        if (jQuery(this).is(':checked')) {
            tinyMCE.execCommand('mceAddControl', true, textarea);
        } else {
            tinyMCE.execCommand('mceRemoveControl', true, textarea);
        }
    });
});
</script>
 
<?php
 echo $form;
?>