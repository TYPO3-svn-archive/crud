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

tx_div::load('tx_lib_controller');
class tx_crud__marker_controller extends tx_lib_controller{
	
	var $defaultAction = 'update';
	var $headerData = false;
	var $footerData = false;

	function updateAction() {
		$config = $this->configurations;
		$modelClassName = $config['storage.']['className'];
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		require_once($config['storage.']['classPath']);
		require_once($config['view.']['classPath']);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
	    $view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		if ($_GET['ajax'] || $_POST['ajax']) {
			echo $translator->translateContent();
			die();
		} else {
			return $translator->translateContent();
		}
	}
	
	function deleteAction() {
		$config = $this->configurations;
		$modelClassName = $config['storage.']['className'];
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		require_once($config['storage.']['classPath']);
		require_once($config['view.']['classPath']);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
	    $view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		if ($_GET['ajax'] || $_POST['ajax']) {
			echo $translator->translateContent();
			die();
		} else {
			return $translator->translateContent();
		}
	}
	
	function createAction() {
		$config = $this->configurations;
		$modelClassName = $config['storage.']['className'];
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		require_once($config['storage.']['classPath']);
		require_once($config['view.']['classPath']);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
	    $view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		if ($_REQUEST['ajax']) {
			echo $translator->translateContent();
			die();
		} else {
			return $translator->translateContent();
		}
	}
	
	function retrieveAction() {		
		$config = $this->configurations;
		$start = microtime(true);
		$modelClassName = $config['storage.']['className'];
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		require_once($config['storage.']['classPath']);
		require_once($config['view.']['classPath']);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
		//$view->set("data",$model->getData());
		$view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		$stop = microtime(true);
		if ($_GET['ajax'] || $_POST['ajax']) {
			echo $translator->translateContent();
			die();
		} else {
			return $translator->translateContent();
		}
	}

	function browseAction() {
		$start = microtime(true);
		$config = $this->configurations;
		$modelClassName = $config['storage.']['className'];
		$templateEngineClassName = $config['view.']['className'];
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$stop = microtime(true);
		//echo "<br> instanzen:".round($stop-$start,3);
		$start = microtime(true);
		include($config['storage.']['classPath']);
		include($config['view.']['classPath']);
		$stop = microtime(true);
		//echo "<br> file include:".round($stop-$start,3);
		$start = microtime(true);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$stop = microtime(true);
		//echo "<br> model and view:".round($stop-$start,3);
		$start = microtime(true);
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
		$view->render($config['view.']['template']);
		$this->headerData = $view->headerData;
		$this->footerData = $view->footerData;
		$stop = microtime(true);
		// "<br> view rendern:".round($stop-$start,3);
		$start = microtime(true);
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		$stop = microtime(true);
		//echo "<br> translate:".round($stop-$start,3);
		if ($_GET['ajax'] || $_POST['ajax']) {
			echo $translator->translateContent();
			die();
		} else {
			return $translator->translateContent();
		}
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/controllers/class.tx_crud_controllers_common.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/controllers/class.tx_crud_controllers_common.php']);
}

?>