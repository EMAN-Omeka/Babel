<?php
$head = array('bodyclass' => 'babel exhibitss browse',
              'title' => __('Babel | Exhibits'));
echo head($head);
echo flash(); 
?>
<nav id="section-nav" class="navigation vertical">
<ul class="dropdown">
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/help'>Fonctionnement du plugin</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/menus'>Menus</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/simple-vocab'>Simple Vocab</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/list-simple-pages'>Simple Pages</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babel/terms'>Termes</a>  
  </li>
    <li class='active'>
        <a href='<?php echo WEB_ROOT; ?>/admin/babel/list-exhibits-pages'>Expositions</a>
    </li>
</ul>
</nav>

<h2>Cliquez sur une exposition pour la traduire</h2>

<?php
 echo $content;
 echo foot();
?>

