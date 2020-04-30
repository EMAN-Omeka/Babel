<?php
queue_js_file('vendor/tinymce/tinymce.min');
$head = array('bodyclass' => 'babel primary browse', 
              'title' => __('Babel | Translate Simple Page'));
echo head($head);
echo flash(); 
?>
 
<script type="text/javascript"> 
$ = jQuery;
$(window).load(function() {
    var selector;
    if ($('#babel-use-tiny-mce-it').is(':checked')) {
        var el = '#babel-use-tiny-mce-it';
        var textareaId = 'text-' + el.slice(-2) + '-text'  + el.slice(-2);   
        selector = '#' . textareaId;
    } else {
        selector = false;
    }  

    // Add or remove TinyMCE control.
    $('.babel-use-tiny-mce').click(function() {
      var el = jQuery(this).attr('name');
      var textareaId = 'text-' + el.slice(-2) + '-text'  + el.slice(-2);
        if (jQuery(this).is(':checked')) {
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

