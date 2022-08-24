<?php
echo head(array('title' => "Babel"));
echo flash();
?>
<nav id="section-nav" class="navigation vertical">
    <ul class="dropdown">
        <li class='active'>
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
        <li>
            <a href='<?php echo WEB_ROOT; ?>/admin/babel/list-exhibits-pages'>Expositions</a>
        </li>
        <li>
            <a href='<?php echo WEB_ROOT; ?>/admin/babel/tags'>Tags</a>
        </li>
    </ul>
</nav>
<h2>Fonctionnement du plugin</h2>

<div>
    Texte de la documentation à saisir ici
</div>
<?php

echo foot();

?>

