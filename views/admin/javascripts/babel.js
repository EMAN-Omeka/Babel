$ = jQuery;
$(window).load(function() {
  $.each($('fieldset.set .field .input-block'), function () {
    tog = $(this).find('.use-html').detach();   
    tog.insertAfter($(this).find('.input textarea:first'));
  });
  $.each($('.use-tiny:checked'), function () {
    el = $(this).attr('name');
    tinyMCE.execCommand('mceAddControl', true, el.substring(0, el.length - 6) + '[text]');
    console.log($(this).attr('name'));
    console.log($(this).attr('checked'));
  });  
    $('.use-tiny').click(function() {
      var el = $(this).attr('name');
      console.log(el);
      var textarea = el.substring(0, el.length - 6) + '[text]';
        if ($(this).is(':checked')) {
            tinyMCE.execCommand('mceAddControl', true, textarea);
        } else {
            tinyMCE.execCommand('mceRemoveControl', true, textarea);
        }
    });
});