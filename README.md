# Babela - Content Translation for Omeka Classic

Babela has as starting point Babel https://github.com/EMAN-Omeka/Babel

## Warning

This plugin is a work in progress. Most things should work provided you follow the instructions below, but the code may still be faulty in some places. It shouldn't affect your website in any critical way though.

Feedback is much appreciated.

## Purpose

This module aims to provide an elegant way to translate Omeka **content**, as opposed to Omeka interface strings, which are handled via the usual gettext technology.

It provides a hopefully seamless integration of translation features directly into standard Omeka content management forms.

## Prerequisites

Make sure [SwitchLanguage](https://gitlab.com/TIME_LAS/Omeka_Plugin_SwitchLang/) is installed and activated.

## Installation

Configure available languages via the SwitchLanguage interface at `http://yoursite.com/admin/plugins/config?name=SwitchLanguage`.

The "Flag only" option is highly recommended, otherwise the admin screens will function badly.

The module must be installed as usual, but you need to modify some files.

#### Core

In  `application/libraries/globals.php`, the declaration of these two functions must be changed to this :

```php
function metadata($record, $metadata, $options = array())
{
    return get_view()->translatedMetadata($record, $metadata, $options);
}

function all_element_texts($record, $options = array())
{
    return get_view()->translatedElementTexts($record, $options);
}
```

The function "tag_string" must be changed as well

```php
/**
 * Return a tag string given an Item, Exhibit, or a set of tags.
 *
 * @param Omeka_Record_AbstractRecord|array $recordOrTags The record to retrieve
 * tags from, or the actual array of tags
 * @param string|null $link The URL to use for links to the tags (if null, tags
 * aren't linked)
 * @param string $delimiter ', ' (comma and whitespace) is the default tag_delimiter option. Configurable in Settings
 * @return string HTML
 * @package Omeka\Function\View\Tag
 */
function tag_string($recordOrTags = null, $link = 'items/browse', $delimiter = null)
{
    // Set the tag_delimiter option if no delimiter was passed.
    if (is_null($delimiter)) {
        $delimiter = get_option('tag_delimiter') . ' ';
    }

    if (!$recordOrTags) {
        $tags = array();
    } elseif (is_string($recordOrTags)) {
        $tags = get_current_record($recordOrTags)->Tags;
    } elseif ($recordOrTags instanceof Omeka_Record_AbstractRecord) {
        $tags = $recordOrTags->Tags;
    } else {
        $tags = $recordOrTags;
    }

    if (empty($tags)) {
        return '';
    }

    $tagStrings = array();
    foreach ($tags as $tag) {
        $name = $tag['name'];
        /* Begin Babel */
        $lang = getLanguageForOmekaSwitch();
        $currentLang = substr($lang, 0, 2);
        $recordId = $tag['id'];
        $db = get_db();
        $query = "SELECT text FROM `$db->TranslationRecord` WHERE
                    lang = '$currentLang' AND
                    element_id = 0 AND
                    record_id = $recordId AND
                    element_number = 0 AND
                    record_type = 'Tag'";
        if ($translations = $db->query($query)->fetchAll()) {
            if (isset($translations[0])) {
                $displayedName = $translations[0]['text'];
            }
        } else {
            $displayedName = $tag['name'];
        }
        /* End Babel */
        if (!$link) {
            $tagStrings[] = html_escape($name);
        } else {
            //$tagStrings[] = '<a href="' . html_escape(url($link, array('tags' => $name))) . '" rel="tag">' . html_escape($name) . '</a>'; Original
            $tagStrings[] = '<a href="' . html_escape(url($link, array('tags' => $name))) . '" rel="tag">' . html_escape($displayedName) . '</a>'; // Babel $displayedName
        }
    }
    return join(html_escape($delimiter), $tagStrings);
}
```



It ensures the two helpers which have been rewritten for this module are called instead of Omeka's. 

The code changes are minor and shouldn't impact your site performance.

This is a rather drastic choice, as modifying core is usually a bad idea, but in this case we considered the plugin's added value to be so high it was worth it.

#### Theme

To fully enjoy all the plugin's functionalities, you have to make some modifications to your theme.

First, all strings you want to be translated must be passed to the function ```t()```.

For example, if you have a sentence somewhere in your theme : ``This page is about blah``, you should change the code from :

```php
echo "This page is about blah";
```

to :

```php
echo t("This page is about blah");
```

The list of translatable strings is in the file ``themeStrings.php`` , in the plugin's base directory. A future version will include a ``t()``  self learning function to add new strings. In the meantime, this file must contain all the strings which are in your theme and you want to translate by this mean.

The array is split into categories to clarify the resulting form at ``/admin/babel/terms`` .

You can create as many categories and strings as you want, provided you respect the basic structure of the array.

If you happen to deactivate the plugin at some point in the future, the translation won't work, obviously, but as long as you don't deactivate the module, your theme shouldn't throw errors, as the function declaration would still be there.

To have the breadcrumb of Simple Pages translated, you have to copy "plugins/SimplePages/views/public/page/show.php" in your theme, in "themes/yourtheme/simple-pages/page/show.php". And then you put

```php
<?php
$babela = new BabelaPlugin();
?>
```

at the top of the file and

```php
<?php echo $babela->simple_pages_display_breadcrumbs_translate(); ?>
```

instead of

```php
<?php echo simple_pages_display_breadcrumbs(); ?>
```
We have also implemented a methode "exhibit_builder_link_to_translate" to replace the function "exhibit_builder_link_to". To use it, you have to :

put 

```php
<?php
$babela = new BabelaPlugin();
?>
```

at the top of the file and

```php
<?php echo $babela->exhibit_builder_link_to_translate($exhibit); ?>
```

instead of

```php
<?php echo exhibit_builder_link_to($exhibit); ?>
```
#### Menus

Menus translation is a bit tricky, because it depends on how yours are set up in your theme.

Basically, you have to pass your menu string to the appropriate Babel function.

Where you would usually call, for example : 

```php
echo public_nav_main()->setUlClass('menu-tabs')->render();
```

... you will instead write :

```php
echo BabelaPlugin::translateMenu(public_nav_main()->setUlClass('menu-tabs')->render());
```

or

at the top of your file 

```php
<?php
$babela = new BabelaPlugin();
?>
```

and then

```php
    echo $babela->translateMenu(public_nav_main()->setUlClass('menu-tabs')->render());
```

## Usage

On each content editing page, for each field entry, instead of the usual textfield, you will see one for each activated language.

Enabling the HTML editor will affect the original field and all the translation fields as well.

The following elements can be translated :

- Items
- Files
- Simple Pages
- Menus
- Strings echoed in theme files (via the ``t()`` function)

The following elements are NOT translated, as of now :

- Site's information
- Exhibits
- Collections

Once an element is translated, the plugin detects the current language and displays the matching translation if it exists, the default language string if it doesn't.

## Credits

Based on a plugin coded for the EMAN platform (Item, ENS-CNRS) by Vincent Buard [(Numerizen)](http://numerizen.com).
Improved by Gilles Lengy for Artaban (https://artaban.fr)
