<?php

/*
 * Babel Plugin
 *
 * Translate Omeka content
 *
 */

class BabelaPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_filters = array(
        'admin_navigation_global',
        'admin_navigation_main',
    );

    protected $_hooks = array(
        'initialize',
        'install',
        'uninstall',
        'after_save_item',
        'after_delete_item',
        'after_save_collection',
        'after_save_file',
        'public_items_show',
        'define_routes',
        'admin_head',
        'define_acl',
    );

    public function hookAdminHead()
    {
        $url = $_SERVER['REQUEST_URI'];
        preg_match('@^\/admin\/(items|collections|files)\/edit/@', $url, $matches);
        if ($matches) {
            queue_js_file('babel');
        }
    }

    function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $babelAdmin = new Zend_Acl_Resource('Babel_Page');
        $acl->add($babelAdmin);
    }

    /**
     * Add the pages to the public main navigation options.
     *
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Babel'),
            'uri' => url('babel/help'),
            'resource' => 'Babel_Page',
        );
        return $nav;
    }

    public function hookInitialize()
    {
        // Get languages list from SwitchLanguage
        $languages = get_option('languages_options');
        $this->languages = explode('#', $languages);
        foreach ($this->languages as $i => $language) {
            $this->languages[$i] = substr($language, 0, 2);
        }
        // Get current language from SwitchLanguage
        $lang = getLanguageForOmekaSwitch();
        $this->current_language = substr($lang, 0, 2);
        // Remove default language from language list
        $locale = get_option('locale_lang_code');
        if (($key = array_search($locale, $this->languages)) !== false) {
            unset($this->languages[$key]);
        }
        // Pour index quand multivalue
        $this->elementCounters = array();

        // Loop sur tous les champs pour créer un champ de traduction pour chaque
        $db = get_db();
        $elements = $db->query("SELECT e.id id, es.name esName, e.name eName FROM `$db->Element` e LEFT JOIN `$db->ElementSets` es ON e.element_set_id = es.id")->fetchAll();

        foreach ($elements as $i => $element) {
            // Pour saisie
            add_filter(array('ElementInput', 'Item', $element['esName'], $element['eName']), array($this, 'translateField'), 1000);
            add_filter(array('ElementInput', 'Collection', $element['esName'], $element['eName']), array($this, 'translateField'), 0);
            add_filter(array('ElementInput', 'File', $element['esName'], $element['eName']), array($this, 'translateField'), 0);
        };
    }

    public function filterAdminNavigationGlobal($nav)
    {
        $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        if (isset($params['module'])) {
            if ($params['module'] == 'simple-pages' && isset($params['id'])) {
                $nav[] = array(
                    'label' => __('Translate this Simple Page'),
                    'uri' => url('babel/simple-page/' . $params['id'])
                );
            }
            if ($params['module'] == 'exhibit-builder' && isset($params['id'])) {
                $nav[] = array(
                    'label' => __('Translate this Exhibit Page'),
                    'uri' => url('babel/exhibit/' . $params['id'])
                );
            }
        }
        return $nav;
    }

    function hookDefineRoutes($args)
    {
        $router = $args['router'];
        $router->addRoute(
            'babel_help',
            new Zend_Controller_Router_Route(
                'babel/help',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'help',
                )
            )
        );
        $router->addRoute(
            'babel_translate_menus',
            new Zend_Controller_Router_Route(
                'babel/menus',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'translate-menus',
                    'id' => '',
                )
            )
        );
        $router->addRoute(
            'babel_translate_simple_vocab',
            new Zend_Controller_Router_Route(
                'babel/simple-vocab/:id',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'translate-simple-vocab',
                    'id' => '',
                )
            )
        );
        $router->addRoute(
            'babel_translate_simple_page',
            new Zend_Controller_Router_Route(
                'babel/simple-page/:id',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'translate-simple-page',
                    'id' => '',
                )
            )
        );
        $router->addRoute(
            'babel_translate_terms',
            new Zend_Controller_Router_Route(
                'babel/terms',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'translate-terms',
                )
            )
        );
        $router->addRoute(
            'babel_list_simple_pages',
            new Zend_Controller_Router_Route(
                'babel/list-simple-pages',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'list-simple-pages',
                )
            )
        );
        $router->addRoute(
            'babel_list_exhibits_pages',
            new Zend_Controller_Router_Route(
                'babel/list-exhibits-pages',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'list-exhibits-pages',
                )
            )
        );
        $router->addRoute(
            'babel_translate_exhibit_page',
            new Zend_Controller_Router_Route(
                'babel/exhibit/:id',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'translate-exhibit',
                    'id' => '',
                )
            )
        );
        $router->addRoute(
            'babel_translate_exhibit_page_page',
            new Zend_Controller_Router_Route(
                'babel/exhibit/page/:id',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'translate-exhibit-page',
                    'id' => '',
                )
            )
        );
        $router->addRoute(
            'babel_translate_tags',
            new Zend_Controller_Router_Route(
                'babel/tags',
                array(
                    'module' => 'babel',
                    'controller' => 'page',
                    'action' => 'translate-tags',
                    'id' => '',
                )
            )
        );
    }

    public function hookAfterSaveFile($args)
    {
        $elements = $args['record']->Elements;
        if (is_object($args['post'])) {
            $post = get_object_vars($args['post']);
            $this::hookAfterSaveItem($args);
        }
    }

    public function hookAfterSaveCollection($args)
    {
        $elements = $args['record']->Elements;
        if (is_object($args['post'])) {
            $post = get_object_vars($args['post']);
            $this::hookAfterSaveItem($args);
        }
    }

    public function hookAfterDeleteItem($args)
    {
        $record = $args['record'];
        $type = get_class($record);
        $db = get_db();
        $db->query("DELETE FROM `$db->TranslationRecord` WHERE record_id = $record->id AND record_type = '$type'");
    }

    public function hookAfterSaveItem($args)
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $record = $args['record'];
        $elements = $args['record']->Elements;
        // Is the item created via IHM ? If not, quit immediately
        if ($args['post']) {
            $data = get_object_vars($args['post']);
        } else {
            return;
        }
        $insert = $args['insert'];
        $type = get_class($record);
        $db = get_db();
        $db->query("DELETE FROM `$db->TranslationRecord` WHERE record_id = $record->id AND record_type = '$type'");
        foreach ($data['Elements'] as $id => $elementGroup) {
            foreach ($elementGroup as $elementNumber => $element) {
                foreach ($element['translation'] as $lang => $translation) {
                    $isHtml = 0;
                    if (isset($translation['html'])) {
                        $isHtml = 1;
                    }
                    $setId = $db->query("SELECT element_set_id FROM `$db->Elements` WHERE id = " . $id)->fetchAll();
                    $setId = array_pop($setId);
                    if ($translation['text'] <> "") {
                        $t = new TranslationRecord;
                        $t->record_id = $record->id;
                        $t->record_type = "$type";
                        $t->element_id = $id;
                        $t->element_number = $elementNumber;
                        $t->element_set = $setId['element_set_id'];
                        $t->lang = $lang;
                        $t->text = $translation['text'];
                        $t->html = $isHtml;
                        $t->save();
                    }
                }
            }
        }
        $flashMessenger->addMessage('Traduction sauvegardée.');
    }

    public function translateField($components, $args)
    {
        $record = $args['record'];
        $element = $args['element'];
        $stem = $args['input_name_stem'];
        $type = get_class($record);
        $db = get_db();
        // Extraction du numéro d'ordre
        preg_match_all('/\d+/', $stem, $sub);
        $num = end($sub[0]);
        if ($record->id == 0) {
            return $components;
        }
        foreach ($this->languages as $language) {
            $langDisplay = ucfirst(Locale::getDisplayLanguage($language, $this->current_language));
            $query = "SELECT text, lang, element_number, html FROM `$db->TranslationRecord` WHERE
                      element_id = $element->id AND
                      element_set = $element->element_set_id AND
                      record_id = $record->id AND
                      element_number = $num AND
                      lang = '$language' AND
                      record_type = '$type'";
            $text = $db->query($query)->fetchAll();
            $checked = '';
            if (isset($text[0])) {
                $text = $text[0];
                $text['html'] == 1 ? $checked = "checked" : $checked = '';
                $element_number = $text['element_number'];
                $content = $text['text'];
            } else {
                $content = '';
            }

            // Locale sur première occurrence
            $elementId = $stem . '[translation][' . $language . '][text]';
            $htmlBoxName = $stem . '[translation][' . $language . '][html]';
            $defLangDisplay = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), $this->current_language));
            if (substr_count($components['input'], '<textarea') >= 1) {
                if (strpos($components['input'], $defLangDisplay) == 0) {
                    $components['input'] = str_replace('<textarea', "<span style='font-style:italic;'>$defLangDisplay</span><textarea", $components['input']);
                }
                $components['input'] .= "<span style='font-style:italic;clear:left;display:block;'>" . $langDisplay . "</span><textarea name='" . $elementId . "' id='" . $elementId . "' rows='3' cols='50' aria-hidden='true'>" . $content . "</textarea>";
            } elseif (substr_count($components['input'], '<select') >= 1) {
                if (strpos($components['input'], $defLangDisplay) == 0) {
                    $components['input'] = str_replace('<select', "<span style='font-style:italic;width:300px;'>$defLangDisplay</span><select", $components['input']);
                }
                $options = "";
                $translations = $db->query("SELECT text FROM `$db->TranslationRecords` WHERE lang = '$language' AND record_type = 'SimpleVocab' AND element_id = " . $element->id)->fetchAll();
                if (isset($translations[0]['text'])) {
                    $translations = explode(PHP_EOL, $translations[0]['text']);
                    foreach ($translations as $i => $translation) {
                        trim($content) == trim($translation) ? $selected = 'selected' : $selected = '';
                        $options .= "<option $selected>" . $translation . "</option>";
                    }
                }
                $components['input'] .= "<span style='font-style:italic;clear:left;display:block;'>" . $langDisplay . "</span><select name='" . $elementId . "' id='" . $elementId . "'>$options</select>";
            }
        }
        return $components;
    }

    public function hookInstall()
    {
        $db = $this->_db;
        $sql = "
    CREATE TABLE IF NOT EXISTS `$db->TranslationRecord` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `record_id` int(10) unsigned NOT NULL,
      `record_type` tinytext COLLATE utf8_unicode_ci NOT NULL,
      `element_set` tinytext COLLATE utf8_unicode_ci NOT NULL,
      `element_id` int(10) unsigned NOT NULL,
      `element_number` int(10) unsigned NOT NULL,
      `lang` tinytext COLLATE utf8_unicode_ci NOT NULL,
      `text` mediumtext COLLATE utf8_unicode_ci,
      `html` tinyint NOT NULL default 0,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $db->query($sql);
    }

    public function hookUnInstall()
    {
        $db = $this->_db;
        $db->query("DROP TABLE `$db->TranslationRecord`");
        delete_option('babel_terms_translations');
    }

    public function hookPublicItemsShow($args)
    {
        $view = $args['view'];
        $view->addHelperPath(PLUGIN_DIR . '/Babel/views/helpers', 'Babel_View_Helper_');
    }

    public function translate($string)
    {
        if (!$strings = unserialize(base64_decode(get_option('babel_terms_translations')))) {
            include(PLUGIN_DIR . '/Babel/themeStrings.php');
        }
        $translations = [];
        foreach (array_values($strings) as $group) {
            foreach ($group as $index => $value) {
                $translations[$index] = $value;
            }
        }
        $originalStrings = array_filter($translations, function ($key) {
            return strstr($key, get_option('locale_lang_code'));
        }, ARRAY_FILTER_USE_KEY);
        $translationKey = array_search($string, $originalStrings);
        $translationKey = str_replace('fr', substr(getLanguageForOmekaSwitch(), 0, 2), $translationKey);
        if (isset($translations[$translationKey])) {
            return $translations[$translationKey];
        }
        return $string;
    }

    public function translateMenu($menuString)
    {
        $current_lang = substr(getLanguageForOmekaSwitch(), 0, 2);
        $default_lang = substr(get_option('locale_lang_code'), 0, 2);
        if ($current_lang == $default_lang) {
            return $menuString;
        }
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $menuString);
        $elements = $dom->getElementsByTagName('li');
        $originals = [];
        foreach ($elements as $i => $li) {
            $text = trim($li->nodeValue);
            if (strpos($text, "\n")) {
                $text = substr($text, 0, strpos($text, "\n"));
            }
            $originals[$i] = $text;
        }
        unset($dom);
        $db = get_db();
        $menuTranslations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'Menu' AND lang = '" . $current_lang . "'")->fetchAll();
        $translations = [];
        foreach ($menuTranslations as $i => $menuTranslation) {
            if (trim($menuTranslations[$i]['text']) <> '' && isset($menuTranslations[$i]['text'])) {
                $translations[] = $menuTranslations[$i]['text'];
            } else {
                if (isset($originals[$i])) {
                    $translations[] = $originals[$i];
                }
            }
        }
        $menu = str_replace($originals, $translations, $menuString);
        return $menu;
    }

    /**
     * Returns a breadcrumb for a given page.
     *
     * @param integer|null The id of the page.  If null, it uses the current simple page.
     * @param string $separator The string used to separate each section of the breadcrumb.
     * @param boolean $includePage Whether to include the title of the current page.
     * @uses public_url(), html_escape()
     */
    function simple_pages_display_breadcrumbs_translate($pageId = null, $seperator = ' > ', $includePage = true)
    {
        $db = get_db();
        $html = '';

        if ($pageId === null) {
            $page = get_current_record('simple_pages_page', false);
        } else {
            $page = $db->getTable('SimplePagesPage')->find($pageId);
        }

        if ($page) {
            $ancestorPages = $db->getTable('SimplePagesPage')->findAncestorPages($page->id);
            $bPages = array_merge(array($page), $ancestorPages);

            // make sure all of the ancestors and the current page are published
            foreach ($bPages as $bPage) {
                if (!$bPage->is_published) {
                    $html = '';
                    return $html;
                }
            }

            $current_lang = substr(getLanguageForOmekaSwitch(), 0, 2);

            // find the page links
            $pageLinks = array();
            foreach ($bPages as $bPage) {

                // We try to get a title translation
                $resQueryTitle = $db->query("SELECT text FROM `$db->TranslationRecords` WHERE record_type LIKE 'SimplePageTitle' AND lang = '" . $current_lang . "' AND record_id = '" . $bPage->id . "'")->fetch();
                $pageTitle = $resQueryTitle['text'];
                // If no title translation, we use the original title
                if (!$pageTitle) {
                    $pageTitle = $bPage->title;
                }

                if ($bPage->id == $page->id) {
                    if ($includePage) {
                        $pageLinks[] = html_escape($pageTitle);
                    }
                } else {
                    $pageLinks[] = '<a href="' . public_url($bPage->slug) . '">' . html_escape($pageTitle) . '</a>';
                }

            }
            $pageLinks[] = '<a href="' . public_url('') . '">' . __('Home') . '</a>';

            $seperator = '<span class="separator" aria-hidden="true">' . html_escape($seperator) . '</span>';

            // create the bread crumb
            $html .= implode($seperator, array_reverse($pageLinks));
        }
        return $html;
    }

    /**
     * Return a link to an exhibit.
     *
     * @param Exhibit $exhibit If null, it uses the current exhibit
     * @param string $text The text of the link
     * @param array $props Link attributes
     * @param ExhibitPage $exhibitPage A specific page to link to
     * @return string
     */
    function exhibit_builder_link_to_translate($exhibit = null, $text = null, $props = array(), $exhibitPage = null)
    {

        if (!$exhibit) {
           $exhibit = get_current_record('exhibit');
        }
        $uri = exhibit_builder_exhibit_uri($exhibit, $exhibitPage);
        // We try to get a title translation
        $db = get_db();
        $current_lang = substr(getLanguageForOmekaSwitch(), 0, 2);
        $resQueryTitle = $db->query("SELECT text FROM `$db->TranslationRecords` WHERE record_type LIKE 'ExhibitTitle' AND lang = '" . $current_lang . "' AND record_id = '" . $exhibit->id . "'")->fetch();
        if(is_array($resQueryTitle) && isset($resQueryTitle['text'])){
        $pageTitle = $resQueryTitle['text'];
        }

        // If no title translation, we use the original title
        if (!isset($pageTitle)) {
            $text = !empty($text) ? $text : html_escape($exhibit->title);
        } else {
            $text = $pageTitle;
        }

        return '<a href="' . html_escape($uri) . '" ' . tag_attributes($props) . '>' . $text . '</a>';
    }

}



