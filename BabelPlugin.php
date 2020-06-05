<?php

/* 
 * Babel Plugin
 *
 * Translate Omeka content
 *
 */

class BabelPlugin extends Omeka_Plugin_AbstractPlugin 
{
    protected $_filters = array(	
   		'admin_navigation_global',  		
   		'admin_navigation_main',  		
    );
    
    protected $_hooks = array(
      'initialize', 
      'install',
      'after_save_item', 
      'after_delete_item', 
      'after_save_collection', 
      'after_save_file', 
      'public_items_show',     
      'define_routes', 
      'admin_head',
      'define_acl',
      );

    public function hookAdminHead() {
      queue_js_file('babel');      
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
      
    public function hookInitialize() {
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
      if(($key = array_search($locale, $this->languages)) !== false) {
          unset($this->languages[$key]);
      }        
      // Pour index quand multivalue
      $this->elementCounters = array();
      
      // Loop sur tous les champs pour créer un champ de traduction pour chaque
      $db = get_db();
      $elements = $db->query("SELECT e.id id, es.name esName, e.name eName FROM `$db->Element` e LEFT JOIN `$db->ElementSets` es ON e.element_set_id = es.id")->fetchAll();
  
      foreach ($elements as $i => $element) {
        // Pour saisie
  //         add_filter(array('ElementInput', 'Item', $elementSet->name, $element->name), array($this, 'filterElementInput'));        
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
  								'controller'   => 'page',
  								'action'       => 'help',
  						)
  				)
  		);  		
   		$router->addRoute(
  				'babel_translate_menus',
  				new Zend_Controller_Router_Route(
  						'babel/menus', 
  						array(
  								'module' => 'babel',
  								'controller'   => 'page',
  								'action'       => 'translate-menus',
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
  								'controller'   => 'page',
  								'action'       => 'translate-simple-vocab',
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
  								'controller'   => 'page',
  								'action'       => 'translate-simple-page',
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
  								'controller'   => 'page',
  								'action'       => 'translate-terms',
  						)
  				)
  		);
   		$router->addRoute(
  				'babel_list_simple_pages',
  				new Zend_Controller_Router_Route(
  						'babel/list-simple-pages', 
  						array(
  								'module' => 'babel',
  								'controller'   => 'page',
  								'action'       => 'list-simple-pages',
  						)
  				)
  		);  		
   		$router->addRoute(
  				'babel_translate_exhibit_page',
  				new Zend_Controller_Router_Route(
  						'babel/exhibit/:id', 
  						array(
  								'module' => 'babel',
  								'controller'   => 'page',
  								'action'       => 'translate-exhibit',
  								'id' => '',
  						)
  				)
  		);  		
    }      		
             
    public function hookAfterSaveFile($args) {
      $elements = $args['record']->Elements;             
      if (is_object($args['post'])) {
        $post = get_object_vars($args['post']);
        $this::hookAfterSaveItem($args);        
      }
    }
            
    public function hookAfterSaveCollection($args) {    
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
      // Is the created via IHM ? If not, quit immediately
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
            $components['input'] = str_replace('<textarea' , "<span style='font-style:italic;'>$defLangDisplay</span><textarea", $components['input']); 
          }
          $components['input'] .= "<span style='font-style:italic;clear:left;display:block;'>" . $langDisplay . "</span><textarea name='" . $elementId . "' id='" . $elementId . "' rows='3' cols='50' aria-hidden='true'>" . $content . "</textarea>";
        } elseif (substr_count($components['input'], '<select') >= 1) {
          if (strpos($components['input'], $defLangDisplay) == 0) {            
            $components['input'] = str_replace('<select' , "<span style='font-style:italic;width:300px;'>$defLangDisplay</span><select", $components['input']); 
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
//       Zend_Debug::dump($components);      
      return $components;
    }

  public function hookInstall() {
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
  
  public function hookPublicItemsShow($args) {
    $view = $args['view'];  
    $view->addHelperPath(PLUGIN_DIR . '/Babel/views/helpers', 'Babel_View_Helper_' );
  }  
  
  public function translate($string) {
    $strings = unserialize(base64_decode(get_option('babel_terms_translations')));
    $translations = [];
    foreach(array_values($strings) as $group) {
      foreach ($group as $index => $value) {
        $translations[$index] = $value;
      }    
    }      
    $translationKey = array_search(trim($string), $translations);
    $translationKey = str_replace('fr', getLanguageForOmekaSwitch(), $translationKey);
    if (isset($translations[$translationKey])) {
      return $translations[$translationKey];    
    }   
    return $string;
  }      
  
  public function translateMenu($menuString) {
    $current_lang = substr(getLanguageForOmekaSwitch(), 0, 2);
    $default_lang = substr(get_option('locale_lang_code'), 0, 2);
//     echo $default_lang . '/' . $current_lang;
    if ($current_lang == $default_lang) {
      return $menuString;
    }
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $menuString);
    $elements = $dom->getElementsByTagName('li');
    $originals = [];
    foreach ($elements as $i => $li) {
/*
      $uls = $li->getElementsByTagName('ul');
      for ($x = $uls->length; $x--; 0) {
        $y = $uls->item($x);
        $y->parentNode->removeChild($y); 
      }
*/
      $originals[] = trim($li->nodeValue);  
    }
    unset($dom);
    $db = get_db();
    $menuTranslations = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type LIKE 'Menu' AND lang = '" . $current_lang . "'")->fetchAll();  
    $translations = [];
    foreach ($menuTranslations as $i => $menuTranslation) {
      if (trim($menuTranslations[$i]['text']) <> '' && isset($menuTranslations[$i]['text'])) {
        $translations[] = $menuTranslations[$i]['text'];     
//         echo $menuTranslations[$i]['text'] . '<br />';               
      } else {
        $translations[] = $originals[$i];
      }
    }    

//     Zend_Debug::dump($menuTranslations);  
/*    Zend_Debug::dump($originals);    
    Zend_Debug::dump($translations);    
*/
    $menu = str_replace($originals, $translations, $menuString);
    return $menu;
  }
  
} 



