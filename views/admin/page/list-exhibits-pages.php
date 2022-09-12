<?php
$head = array('bodyclass' => 'babela exhibitss browse',
              'title' => __('Babela | Exhibits'));
echo head($head);
echo flash(); 
?>
<nav id="section-nav" class="navigation vertical">
<ul class="dropdown">
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babela/help'>Fonctionnement du plugin</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babela/menus'>Menus</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babela/simple-vocab'>Simple Vocab</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babela/list-simple-pages'>Simple Pages</a>
  </li>
  <li>
    <a href='<?php echo WEB_ROOT; ?>/admin/babela/terms'>Termes</a>
  </li>
    <!--
    <li class='active'>
        <a href='<?php echo WEB_ROOT; ?>/admin/babela/list-exhibits-pages'>Expositions</a>
    </li>
    <li>
        <a href='<?php echo WEB_ROOT; ?>/admin/babela/tags'>Tags</a>
    </li>
    -->
</ul>
</nav>

<?php
if (plugin_is_active('ExhibitBuilder')) {
    ?>
    <h2>Cliquez sur une exposition pour la traduire</h2>
    <?php
    echo $content;
} else {
    echo "<h1>Exhibit Builder n'est pas actif</h1>";
}
echo foot();
?>

