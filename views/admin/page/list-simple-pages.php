<?php
$head = array('bodyclass' => 'babel simple-pages browse',
              'title' => __('Babel | Simple Pages'));
echo head($head);
echo flash();
?>
<nav id="section-nav" class="navigation vertical">
<ul class="dropdown">
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/menus'>Menus</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/simple-vocab'>Simple Vocab</a>
  </li>
  <li class='active'>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/list-simple-pages'>Simple Pages</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/terms'>Termes</a>
  </li>
</ul>
</nav>

<h2>Cliquez sur une Simple Page pour la traduire</h2>

<?php
 echo $content;
 echo foot();
?>

