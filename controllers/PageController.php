<?php

class Babel_PageController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        // Get current language from SwitchLanguage
        $this->current_language = substr(getLanguageForOmekaSwitch(), 0, 2);
        $this->languages = explode("#", get_option('languages_options'));
        foreach ($this->languages as $i => $language) {
            $this->languages[$i] = substr($language, 0, 2);
        }
        // Remove default language from language list
        $locale = get_option('locale_lang_code');
        if (($key = array_search($locale, $this->languages)) !== false) {
            unset($this->languages[$key]);
        }
    }

    public function helpAction()
    {

    }

    public function translateMenusAction()
    {
        $form = $this->getMenusForm();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'Menu'");
                foreach ($this->languages as $lang) {
                    foreach ($texts as $element_id => $translations) {
                        $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $element_id, 'Menu', 0, $element_id, 0, '" . substr($translations['lang_' . $element_id . '_' . $lang], 0, 2) . "', " . $db->quote($translations['ElementMenuTranslation_' . $element_id . '_' . $lang]) . ", 0)";
                        $db->query($query);
                    }
                }
            }
        }
        $this->view->form = $form;
    }

    public function getMenusForm()
    {
        $db = get_db();
        $menuTranslations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'Menu'")->fetchAll();
        $translations = [];
        foreach ($menuTranslations as $x => $translationRecord) {
            $translations[$translationRecord['element_id']][$translationRecord['lang']] = $translationRecord['text'];
        }
        $form = new Zend_Form();
        $form->setName('BabelTranslationMenuForm');

        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . public_nav_main()->setUlClass('auteur-onglets')->render());
        $elements = $dom->getElementsByTagName('li');
        $default_language = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), Zend_Registry::get('Zend_Locale')));
        foreach ($elements as $i => $li) {
            $j = $i + 1;
            $text = trim($li->nodeValue);
            if (strpos($text, "\n")) {
                $text = substr($text, 0, strpos($text, "\n"));
            }
            $original = new Zend_Form_Element_Note('ElementMenu_' . $j);
            $original->setLabel($default_language . ' : ');
            $original->setValue("<h4>" . $text . "</h4>");
            $original->setBelongsto($j);
            $form->addElement($original);

            foreach ($this->languages as $lang) {
                $language = new Zend_Form_Element_Hidden('lang_' . $j . '_' . $lang . ' : ');
                $language->setValue($lang);
                $language->setBelongsto($j);
                $form->addElement($language);

                // Corps
                $textMenu = new Zend_Form_Element_Text('texte');
                $textMenu->setLabel(ucfirst(Locale::getDisplayLanguage($lang, $this->current_language)) . ' : ');
                $textMenu->setName('ElementMenuTranslation_' . $j . '_' . $lang);
                if (isset($translations[$j][$lang])) {
                    $textMenu->setValue($translations[$j][$lang]);
                }
                $textMenu->setBelongsto($j);
                $form->addElement($textMenu);
            }
        }
        unset($dom);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translations');
        $submit->setValue('');
        $form->addElement($submit);

        $this->prettifyForm2($form);
        return $form;
    }

    public function listSimplePagesAction()
    {
        $db = get_db();
        $simplePages = $db->query("SELECT title, id FROM `$db->SimplePagesPage`")->fetchAll();
        $list = "<ul>";
        foreach ($simplePages as $i => $page) {
            $list .= "<li><a href='" . WEB_ROOT . "/admin/babel/simple-page/" . $page['id'] . "' target='_blank'>" . $page['title'] . "</a></li>";
        }
        $list .= "</ul>";

        $this->view->content = $list;
    }

    public function listExhibitsPagesAction()
    {
        $db = get_db();
        $exhibitsPages = $db->query("SELECT title, id FROM `$db->Exhibits`")->fetchAll();
        $list = "<ul>";
        foreach ($exhibitsPages as $i => $page) {
            $list .= "<li><a href='" . WEB_ROOT . "/admin/babel/exhibit/" . $page['id'] . "' target='_blank'>" . $page['title'] . "</a></li>";
        }
        $list .= "</ul>";

        $this->view->content = $list;
    }

    public function translateSimpleVocabAction()
    {
        $form = $this->getSimpleVocabForm();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'SimpleVocab'");
                foreach ($this->languages as $lang) {
                    foreach ($texts as $element_id => $translations) {
//             Zend_Debug::dump($translations);
                        $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $element_id, 'SimpleVocab', 0, $element_id, 0, '" . $translations['lang_' . $element_id . '_' . $lang] . "', " . $db->quote($translations['ElementNameTranslation_' . $element_id . '_' . $lang]) . ", 0)";
                        $db->query($query);
                    }
                }
            }
        }
        $this->view->form = $form;
    }

    public function translateSimplePageAction()
    {
        $id = $this->getParam('id');
        $form = $this->getSimplePageForm($id);
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type LIKE 'SimplePage%' AND record_id = " . $id);
                foreach ($texts as $fieldName => $translations) {
                    if (is_array($translations)) {
                        foreach ($translations as $lang => $field) {
                            $value = array_values($field);
                            $value = $db->quote($value[0]);
                            if ($value) {
                                if (isset($texts["use_tiny_mce_" . $this->current_language]) && $texts["use_tiny_mce_" . $this->current_language] == 1) {
                                    $useHtml = 1;
                                } else {
                                    $useHtml = 0;
                                }
                                $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'SimplePage" . ucfirst($fieldName) . "', 0, 0, 0, '$lang', $value, $useHtml)";
                                $db->query($query);
                            }
                        }
                    }
                    $useHtml = 0;
                }
            }
        }
        // Retrieve orignal texts from DB
        $db = get_db();
        $original = $db->query("SELECT * FROM `$db->SimplePagesPage` WHERE id = " . $id)->fetchAll();
        $original = "<details><summary>Original texts</summary><div><em>Title</em> : " . $original[0]['title'] . "<br /><br /><em>Text</em> : " . $original[0]['text'] . "</div></details>";
        $this->view->form = $original . $form;
    }

    public function translateTermsAction()
    {
        $form = $this->getTranslationsForm();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                if (isset($formData['translations'])) {
                    unset($formData['translations'], $formData['submit']);
                    $translations = base64_encode(serialize($formData));
                    set_option('babel_terms_translations', $translations);
                    $this->view->form = $form;
                    return true;
                }
            }
        }
        $this->view->form = $form;
    }

    public function translateExhibitAction()
    {
        $id = $this->getParam('id');
        $form = $this->getExhibitForm($id);
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $texts = $form->getValues();
                // Sauvegarde form dans DB
                $db = get_db();
                $db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type = 'Exhibit' AND record_id = " . $id);
                foreach ($texts as $fieldName => $translations) {
                    if (is_array($translations)) {
                        foreach ($translations as $lang => $field) {
                            $value = array_values($field);
                            $value = $db->quote($value[0]);
                            if ($value) {
                                $useHtml = 1;
                                $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'Exhibit" . ucfirst($fieldName) . "', 0, 0, 0, '$lang', $value, $useHtml)";
                                $db->query($query);
                            }
                        }
                    }
                    $useHtml = 0;
                }
            }
        }
        $this->view->form = $form;
    }


    public function getSimpleVocabForm()
    {
        $db = get_db();
        // Retrieve translations for this page type from DB
        $translatedTerms = $db->query("SELECT t.element_id id, t.terms terms, e.name name, tr.text trans, tr.lang lang
		                     FROM `$db->SimpleVocabTerms` t
		                      LEFT JOIN `$db->Elements` e ON e.id = t.element_id
		                      LEFT JOIN `$db->TranslationRecords` tr ON tr.element_id = t.element_id")->fetchAll();

        $form = new Zend_Form();
        $form->setName('BabelTranslationSVForm');
        // TODO : Synchro $terms / form
        $terms = [];
        foreach ($translatedTerms as $i => $term) {
            $terms[$term['id']]['name'] = $term['name'];
            $terms[$term['id']]['terms'] = $term['terms'];
            $terms[$term['id']][$term['lang']] = $term['trans'];
        }
// 		Zend_Debug::dump($terms);
        foreach ($terms as $id => $term) {
            // Element
            $original = new Zend_Form_Element_Note('ElementName_' . $id);
            $original->setValue("<h3>" . __($term['name']) . "</h3>");
            $form->addElement($original);
            $default_language = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), Zend_Registry::get('Zend_Locale')));
            foreach ($this->languages as $lang) {
                $language = new Zend_Form_Element_Hidden('lang_' . $id . '_' . $lang);
                $language->setValue($lang);
                $language->setBelongsto($id);
                $form->addElement($language);

                // Original
                $original = new Zend_Form_Element_Note('OriginalTerm_' . $id);
                $original->setValue(nl2br($term['terms']) . '<br /><br />');
                $original->setLabel($default_language);
                $original->setBelongsto($id);
                $form->addElement($original);

                // Corps
                $lines = substr_count($term['terms'], PHP_EOL) + 1;
                $textTerm = new Zend_Form_Element_Textarea('texte');
                $textTerm->setAttrib('rows', $lines);
                $textTerm->setLabel(ucfirst(Locale::getDisplayLanguage($lang, $this->current_language)));
                $textTerm->setName('ElementNameTranslation_' . $id . '_' . $lang);
                if (isset($term[$lang]) && $term[$lang] <> '') {
                    $textTerm->setValue($term[$lang]);
                }
                $textTerm->setBelongsto($id);
                $form->addElement($textTerm);
            }
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $submit->setValue('');
        $form->addElement($submit);

        $form = $this->prettifyForm($form);
        return $form;
    }

    public function getSimplePageForm($id)
    {
        $db = get_db();
        // Retrieve translations for this page type from DB
        $translations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'SimplePage%' AND record_id = " . $id)->fetchAll();
        if ($translations) {
            $values = array();
            foreach ($translations as $index => $texts) {
                $fieldName = substr($texts['record_type'], 10);
                $values[$fieldName][$texts['lang']] = $texts['text'];
                $values[$fieldName]['html'] = $texts['html'];
            }
        }

        $form = new Zend_Form();
        $form->setName('BabelTranslationSSForm');
        foreach ($this->languages as $lang) {
            $titleName = "title[$lang]";
            $textName = "text[$lang]";

            // Titre
            $titleSS = new Zend_Form_Element_Text('title');
            $titleSS->setLabel('Title (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $titleSS->setName($titleName);
            if (isset($values['Title'][$lang])) {
                $titleSS->setValue($values['Title'][$lang]);
            }
            $titleSS->setBelongsTo($titleName);
            $form->addElement($titleSS);

            if (isset($values['Text']['html'])) {
                $checked = $values['Text']['html'];
            } else {
                $checked = false;
            }
            $html = $form->createElement(
                'checkbox', 'use_tiny_mce_' . $lang,
                array(
                    'id' => 'babel-use-tiny-mce-' . $lang,
                    'class' => 'babel-use-tiny-mce',
                    'checked' => $checked,
                    'values' => array(1, 0),
                    'label' => __('Use HTML editor?'),
                )
            );
            $form->addElement($html);

            // Corps
            $textSS = new Zend_Form_Element_Textarea('texte');
            $textSS->setLabel('Text (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $textSS->setName($textName);
            if (isset($values['Text'][$lang])) {
                $textSS->setValue($values['Text'][$lang]);
            }
            $textSS->setBelongsTo($textName);
            $textSS->setAttrib('class', 'babel-use-html');
            $form->addElement($textSS);
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $form->addElement($submit);

        return $form;
    }

    public function getTranslationsForm()
    {
        include_once(PLUGIN_DIR . '/Babel/themeStrings.php');
        $form = new Zend_Form();
        $form->setName('BabelTranslationsForm');

        $t = new Zend_Form_Element_Hidden('translations');
        $t->setValue(1);
        $form->addElement($t);
        $translations = get_option('babel_terms_translations');
        $translations = unserialize(base64_decode($translations));
        $categoryNumber = 0;
        foreach ($strings as $title => $category) {
            $categoryTitle = new Zend_Form_Element_Note('categoryTitle_' . $title);
            $categoryTitle->setValue("<h3>$title</h3>");
            $categoryTitle->setBelongsTo($title);
            $form->addElement($categoryTitle);
            $languages = array_values($this->languages);
            $default_language = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), Zend_Registry::get('Zend_Locale')));
            $languages[] = get_option('locale_lang_code');
            foreach ($category as $j => $string) {
                foreach ($languages as $x => $lang) {
                    $current_language = ucfirst(Locale::getDisplayLanguage($lang, Zend_Registry::get('Zend_Locale')));
                    if ($lang == get_option('locale_lang_code')) {
                        $language = new Zend_Form_Element_Hidden('string_' . $categoryNumber . '_' . $j . '_' . $lang);
                    } else {
                        $language = new Zend_Form_Element_Text('string_' . $categoryNumber . '_' . $j . '_' . $lang);
                        $language->setLabel("Du " . $default_language . ' : "' . $string . '", traduire en ' . $current_language . ' => ');
                    }
                    if (isset ($translations[$title]['string_' . $categoryNumber . '_' . $j . '_' . $lang])) {
                        $language->setValue(trim($translations[$title]['string_' . $categoryNumber . '_' . $j . '_' . $lang]));
                    } else {
                        $language->setValue(trim($string));
                    }
                    $language->setBelongsto($title);
                    $form->addElement($language);
                }
            }
            $categoryNumber++;
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Terms Translations');
        $form->addElement($submit);
        $form = $this->prettifyForm($form);
        $elements = $form->getElements();
        foreach ($elements as $elem) {
            if ($elem instanceof Zend_Form_Element_Hidden) {
                $elem->setDecorators(array('ViewHelper'));
            }
        }
        return $form;
    }

    private function prettifyForm2($form)
    {
        // Prettify form
        $blocks = $form->getElements();

        foreach ($blocks as $elem) {
            if ($elem instanceof Zend_Form_Element_Hidden) {
                $elem->removeDecorator('label')->removeDecorator('HtmlTag');
            }
        }

        // Fieldset pour les blocs
        $displayGroups = [];
        $currentDisplayGroup = '';
        foreach ($form->getElements() as $name => $block) {
            $displayGroup = $block->getBelongsTo();
            if ($displayGroup <> $currentDisplayGroup) {
                $currentDisplayGroup = $displayGroup;
            }
            $displayGroups[$currentDisplayGroup][] = $name;
        }
        foreach ($displayGroups as $block => $displayGroup) {
            if ($block) {
                $form->addDisplayGroup($displayGroup, $block);
                $form->getDisplayGroup($block)->removeDecorator('DtDdWrapper');
            } else {
                $form->addDisplayGroup($displayGroup, 'general');
            }
        }
        $form->setDisplayGroupDecorators(array(
            'FormElements',
            'Fieldset',
            array('Fieldset', array('class' => 'uitemplates-fieldset'))
        ));
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'uitemplates-form')),
            'Form'
        ));
        $form->setElementDecorators(array(
                'ViewHelper',
                'Errors',
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('HtmlTag', array('class' => 'form-div')),
                array('Label', array('class' => 'form-label'))
            )
        );
        $form->setElementDecorators(array(
                'ViewHelper',
                'Label',
                new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'elem-wrapper'))
            )
        );
        return $form;
    }

    private function prettifyForm($form)
    {
        // Prettify form
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form'
        ));
        $form->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'td', 'style' => 'text-align:right;float:right;')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        ));
        $blocks = $form->getElements();
        foreach ($blocks as $elem) {
            if ($elem instanceof Zend_Form_Element_Hidden) {
                $elem->removeDecorator('label')->removeDecorator('HtmlTag');
            }
        }
        return $form;
    }

    public function getExhibitForm($id)
    {
        // Retrieve config for this item type from DB
        /*            $db = get_db();
                    $ss = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type = 'Exhibit' AND record_id = " . $id)->fetchAll();
                    if ($ss) {
                      $ss = unserialize($ss[0]['text']);
                      $title =  $ss['title'];
                      $body =  $ss['description'];
                     } else {
                      $ss = $db->query("SELECT * FROM `$db->Exhibit` WHERE id = $id")->fetchAll();
                  $ss = $ss[0];
                      $title =  $ss['title'];
                      $body =  $ss['description'];
                    }

                    $form = new Zend_Form();
                    $form->setName('BabelTranslationSSForm');

                    // Titre
                    $titleSS = new Zend_Form_Element_Text('title');
                    $titleSS->setLabel('Title');
                    $titleSS->setValue($title);
                    $form->addElement($titleSS);

                    // Corps
                    $textSS = new Zend_Form_Element_TextArea('description');
                    $textSS->setLabel('Body');
                    $textSS->setValue($body);
                    $form->addElement($textSS);

                    $submit = new Zend_Form_Element_Submit('submit');
                    $submit->setLabel('Save Translation');
                    $form->addElement($submit);

                    return $form;*/
        $db = get_db();
        // Retrieve translations for this page type from DB
        $translations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type = 'Exhibit' AND record_id = " . $id)->fetchAll();
        if ($translations) {
            $values = array();
            foreach ($translations as $index => $texts) {
                $fieldName = substr($texts['record_type'], 10);
                $values[$fieldName][$texts['lang']] = $texts['text'];
                $values[$fieldName]['html'] = $texts['html'];
            }
        }

        $form = new Zend_Form();
        $form->setName('BabelTranslationSSForm');
        foreach ($this->languages as $lang) {
            $titleName = "title[$lang]";
            $textName = "text[$lang]";

            // Titre
            $titleSS = new Zend_Form_Element_Text('title');
            $titleSS->setLabel('Title (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $titleSS->setName($titleName);
            if (isset($values['Title'][$lang])) {
                $titleSS->setValue($values['Title'][$lang]);
            }
            $titleSS->setBelongsTo($titleName);
            $form->addElement($titleSS);

            $html = $form->createElement(
                'hidden', 'use_tiny_mce_' . $lang,
                array(
                    'id' => 'babel-use-tiny-mce-' . $lang,
                    'class' => 'babel-use-tiny-mce',
                    'values' => 1,
                )
            );
            $form->addElement($html);

            // Corps
            $textSS = new Zend_Form_Element_Textarea('texte');
            $textSS->setLabel('Text (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
            $textSS->setName($textName);
            if (isset($values['Text'][$lang])) {
                $textSS->setValue($values['Text'][$lang]);
            }
            $textSS->setBelongsTo($textName);
            $textSS->setAttrib('class', 'babel-use-html');
            $form->addElement($textSS);
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save Translation');
        $form->addElement($submit);

        return $form;
    }

}
