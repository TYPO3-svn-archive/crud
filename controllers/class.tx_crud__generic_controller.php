<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Frank Thelemann
 *  Contact: f.thelemann@yellowmed.com
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Depends on: lib/div
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */

class tx_crud__generic_controller extends tx_lib_controller{
	
	var $defaultAction = 'retrieve';
	var $headerData = false;
	var $footerData = false;
	
	/**
	 * generic create action
	 * 
	 * @return	string	the content of the controller
	 */
	function createAction() {
		$config = $this->configurations;
		$modelClassName = $config['storage.']['className'];
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
	    $view->render($config['view.']['template']);
		///$this->headerData = $view->headerData;
		//$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		return $translator->translateContent();
	}
	
	/**
	 * generic retrieve action
	 * 
	 * @return	string	the content of the controller
	 */
	function retrieveAction() {
		$config = $this->configurations;
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
		$view->render($config['view.']['template']);
		//$this->headerData = $view->headerData;
		//$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		return $translator->translateContent();
		
	}
	
	/**
	 * generic update action
	 * 
	 * @return	void
	 */
	function updateAction() {
		$config = $this->configurations;
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
	    $view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		return $translator->translateContent();
	}
	
		
	/**
	 * generic delete action
	 * 
	 * @return	string	the content of the controller
	 */
	function deleteAction() {
		$config = $this->configurations;
		$modelClassName = $config['storage.']['className'];
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
	    $view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		return $translator->translateContent();
	}
	
	/**
	 * generic browse action
	 * 
	 * @return	string	the content of the controller
	 */
	function browseAction() {
		$start=microtime(true);
		$config = $this->configurations;
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
		$view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		return $translator->translateContent();
	}
	
	/**
	 * generic filter action
	 * 
	 * @return	string	the content of the controller
	 */
	function pluginAction() {
		$config = $this->configurations->getArrayCopy();
		//t3lib_div::Debug($config);
		if(isset($config['view.']['className'])) {
			$templateEngineClassName = $config['view.']['className'];
			$view = new $templateEngineClassName($this); 
			$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
			return $view->render($config['view.']['template']);
		}
	}	
	
	/**
	 * generic autocomplete action for the searchbox
	 * 
	 * @return	string	the content of the controller
	 */
	function autocompleteAction() {		

		$config = $this->configurations;
		$templateEngineClassName = $config['view.']['className'];
		$view = new $templateEngineClassName($this); 
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
		$model=$this->model;
		$view->set("data",$model->data);
		return $view->render($config['view.']['template']);
		//die();
		//$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		//$translator = new $translatorClassName($view,$this);
		//$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		//$view->_iterator->array['_content'] = $translator->translateContent();
		//$translator = new $translatorClassName($view,$this);
		//$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		//echo $translator->translateContent();
        //die();
	}
	
	function rssAction(){
		$config = $this->configurations;
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
	    $view->render($config['view.']['template']);
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		echo $translator->translateContent();
		die();
	}
}

?>