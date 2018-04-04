<?php
class Babel_PageController extends Omeka_Controller_AbstractActionController
{
	public function init() {
      // Get current language from SwitchLanguage
      $lang = getLanguageForOmekaSwitch();      
      $this->current_language = substr($lang, 0, 2);  	
      // Get languages list from SwitchLanguage
      $languages = get_option('languages_options');
      $this->languages = explode('#', $languages);
      // Remove default language from language list
      $locale = get_option('locale_lang_code');
      if(($key = array_search($locale, $this->languages)) !== false) {
          unset($this->languages[$key]);
      }      
	}
	public function translateSimplePageAction() {
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
  				if(is_array($translations)) {
            foreach ($translations as $lang => $field) {
              $value = array_values($field); 
              $value = $db->quote($value[0]);
              if ($value) {
                $query = "INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'SimplePage$fieldName', 0, 0, 0, '$lang', $value, 0)";
//                 Zend_Debug::dump($query);
//                 echo $query . '<br />';
        				$db->query($query);				              
              }
            }    				
  				}
        }  		  
			}
		}
  	// Retrieve orignal texts from DB
  	$db = get_db();
		$original = $db->query("SELECT * FROM `$db->SimplePagesPage` WHERE id = " . $id)->fetchAll();
		$original = "<details><summary>Original texts</summary><div><em>Title</em> : " . $original[0]['title'] . "<br /><br /><em>Text</em> : " . $original[0]['text'] . "</div></details>";
  	$this->view->form = $original . $form;
	}
/*
	public function translateExhibitAction() {
  	$id = $this->getParam('id');
  	$form = $this->getExhibitForm($id);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$texts = $form->getValues();
				// Sauvegarde form dans DB
				$db = get_db();				
				$db->query("DELETE FROM `$db->TranslationRecords` WHERE record_type = 'Exhibit' AND record_id = " . $id);
				$db->query("INSERT INTO `$db->TranslationRecords` VALUES (null, $id, 'Exhibit', 0, 0, 0, 'en', '" . serialize($texts) . "')");				
			}
		}
  	$this->view->form = $form;
	}
*/
	
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
  		}
 		}
		$form = new Zend_Form();
		$form->setName('BabelTranslationSSForm');
    $languages = $this->languages;
    foreach ($languages as $lang) {            
      $titleName = "title[$lang]";
      $textName = "text[$lang]";
      
  		// Titre
  		$titleSS = new Zend_Form_Element_Text('title');
  		$titleSS->setLabel('Title (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
  		$titleSS->setName($titleName);  
  		if (isset($values['title'][$lang])) {
    		$titleSS->setValue($values['title'][$lang]);    		
  		}
  		$titleSS->setBelongsTo($titleName);
  		$form->addElement($titleSS);		
  
//       $html = Zend_Form_Element_Checkbox('use_html');
      $html = $form->createElement(
          'checkbox', 'use_tiny_mce_' . $lang,
          array(
              'id' => 'babel-use-tiny-mce-' . $lang,
              'class' => 'babel-use-tiny-mce',
              'checked' => false,
              'values' => array(1, 0),
              'label' => __('Use HTML editor?'),
           )
      );  
  		$form->addElement($html);
  		      
  		// Corps
  		$textSS = new Zend_Form_Element_Textarea('texte');
  		$textSS->setLabel('Text (' . Locale::getDisplayLanguage($lang, $this->current_language) . ')');
  		$textSS->setName($textName);
  		if (isset($values['text'][$lang])) {
    		$textSS->setValue($values['text'][$lang]);    		
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
/*
	public function getExhibitForm($id)
	{	
		// Retrieve config for this item type from DB
		$db = get_db();
		$ss = $db->query("SELECT * FROM `$db->TranslationRecords` WHERE record_type = 'Exhibit' AND record_id = " . $id)->fetchAll();
		if ($ss) {
  		$ss = unserialize($ss[0]['text']);
  		$title =  $ss['title'];
  		$body =  $ss['text'];
 		} else {
  		$ss = $db->query("SELECT * FROM `$db->Exhibit` WHERE id = $id")->fetchAll();  	
      $ss = $ss[0];
  		$title =  $ss['title'];
  		$body =  $ss['text'];	
		}
		
		$form = new Zend_Form();
		$form->setName('BabelTranslationSSForm');
		
		// Titre
		$titleSS = new Zend_Form_Element_Text('title');
		$titleSS->setLabel('Title');
		$titleSS->setValue($title);
		$form->addElement($titleSS);		

		// Corps
		$textSS = new Zend_Form_Element_TextArea('text');
		$textSS->setLabel('Body');
		$textSS->setValue($body);
		$form->addElement($textSS);		
	
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Save Translation');
		$form->addElement($submit);
					
		return $form;
  }
*/
}