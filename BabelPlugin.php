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
    );
    
    protected $_hooks = array(
      'initialize', 
      'install',
      'uninstall',
      'after_save_item', 
      'after_save_collection', 
      'after_save_file', 
      'public_items_show',     
      'define_routes', 
      'admin_head',
      );

    public function hookAdminHead() {
      queue_js_file('babel');      
    }
    
    public function hookInitialize() {
      // Get languages list from SwitchLanguage
      $languages = get_option('languages_options');
      $this->languages = explode('#', $languages);
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
      
      // Loop sur tous les champs pour créer un champ de traduction pour chacun
      $db = get_db();
      $elements = $db->query("SELECT e.id id, es.name esName, e.name eName FROM `$db->Element` e LEFT JOIN `$db->ElementSets` es ON e.element_set_id = es.id")->fetchAll();

      foreach ($elements as $i => $element) {
        // Pour saisie
        add_filter(array('ElementInput', 'Item', $element['esName'], $element['eName']), array($this, 'translateField'), 1);
        add_filter(array('ElementInput', 'Collection', $element['esName'], $element['eName']), array($this, 'translateField'), 1);
        add_filter(array('ElementInput', 'File', $element['esName'], $element['eName']), array($this, 'translateField'), 1);
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
      if (is_array($args['post'])) {
        $post = get_object_vars($args['post']);
        $this::hookAfterSaveItem($args);        
      }
    }
            
    public function hookAfterSaveCollection($args) {
      $elements = $args['record']->Elements;             
      if (is_array($args['post'])) {
        $post = get_object_vars($args['post']);
        $this::hookAfterSaveItem($args);        
      }
    }

    public function hookAfterSaveItem($args) 
    {  
      $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');       
      $record = $args['record'];
      $elements = $args['record']->Elements; 
      $data = get_object_vars($args['post']);
/*
      Zend_Debug::dump($args['post']['Elements'][50]);
      Zend_Debug::dump($args['post']['Elements'][39]);
      $fc = Zend_Controller_Front::getInstance();
      $post = $fc->getRequest()->getPost();
*/

      $insert = $args['insert'];
      $type = get_class($record);
      $db = get_db();
      $db->query("DELETE FROM `$db->TranslationRecord` WHERE record_id = $record->id AND record_type = '$type'");
      foreach ($data['Elements'] as $id => $elementGroup) {
        foreach ($elementGroup as $elementNumber => $element) {
          foreach ($element['translation'] as $lang => $translation) {
            $isHtml = 0;
            if (isset($translation['html'])) {
              Zend_Debug::dump($translation);
              $isHtml = 1;
            }
            if ($translation['text'] <> "") {
              $t = new TranslationRecord;
              $t->record_id = $record->id;
              $t->record_type = "$type";
              $t->element_id = $id;
              $t->element_number = $elementNumber;
              $t->element_set = 1; // TODO : autre que DC
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
          $text = $db->query("SELECT text, lang, element_number, html FROM `$db->TranslationRecord` WHERE 
              element_id = $element->id AND 
              element_set = $element->element_set_id AND 
              record_id = $record->id AND 
              element_number = $num AND 
              lang = '$language' AND
              record_type = '$type'")->fetchAll();      
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
          if (substr_count($components['input'], '<textarea') == 1) {
            $defLangDisplay = ucfirst(Locale::getDisplayLanguage(get_option('locale_lang_code'), $this->current_language));                  
            $components['input'] = str_replace('<textarea' , "<span style='font-style:italic;'>$defLangDisplay</span><textarea", $components['input']);
          }          
          $elementId = $stem . '[translation][' . $language . '][text]';
          $htmlBox = $stem . '[translation][' . $language . '][html]';
          $html = "<label class='tiny'>Use HTML ?<input type='checkbox' class='use-tiny' name='" . $htmlBox . "' id='" . $htmlBox . "' value='1' $checked></label>";
          $components['input'] .= '<span style="font-style:italic;clear:left;display:block;">' . $langDisplay . '</span><textarea name="' . $elementId . '" id="' . $elementId . '" rows="3" cols="50">' . $content . '</textarea>' . $html;          
        }
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
  
  public function hookUnInstall() {
        $db = $this->_db;
        $sql = "DROP TABLE IF EXISTS `$db->TranslationRecord`"; 
        $db->query($sql);    
  }  
  public function hookPublicItemsShow($args) {
    $view = $args['view'];  
    $view->addHelperPath(PLUGIN_DIR . '/Babel/views/helpers', 'Babel_View_Helper_' );
  }      
}

