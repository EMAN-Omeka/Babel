**Purpose**

This module aims to provide an elegant way to translate Omeka CONTENT, as opposed to Omeka interface strings, which are handled via the usual gettext technology.

It provides a hopefuly seemless integration of translation features directly into standard Omeka content management forms.


**Installation**

Make sure SwitchLanguage is installed and activated.

Configure available languages at http://yoursite.com/admin/plugins/config? name=SwitchLanguage.

The "Flag only" option is highly reommended, otherwise the admin screens will function badly.

The module installs as usual, but you need to modify a core file.

In  application/libraries/globals.php, the declaration of these two functions must be changed to this :

function metadata($record, $metadata, $options = array())
{
    return get_view()->translatedMetadata($record, $metadata, $options);
}

function all_element_texts($record, $options = array())
{
    return get_view()->translatedElementTexts($record, $options);
}

It ensures the two helpers which have been rewritten for this module are called instead of Omeka's. 

The code changes are minor and shouldn't impact your site performance.

This is a rather drastic choice, as modifying core is usually a bad idea, but thein this case we considered the plugin's added value to be so high it was worth it.

**Usage**

On each content editing page, for each field entry, instead of the usual textfield, you will see one for each activated langue.

You can enable the WYSIWYG editor for each field entry, but you'll need to save the content once after added entries for the editor to become available.

The follonwing elements can be translated :

- Items
- collections
- files
- Simple Pages

The following elements are NOT translated, as of now :

- menus
- site's information
- exhibits

Once an element is trasnlated, the plugin detects the currrent language and displays the matching translation if it exists, the default language string if it doesn't.

