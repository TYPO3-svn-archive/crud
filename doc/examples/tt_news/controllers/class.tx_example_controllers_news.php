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
 * @subpackage tx_partner__registration
 */

class tx_example_controllers_news extends tx_lib_controller{
	var $defaultAction = 'browseAction';
	
	function browseAction() {
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
		return $translator->translateContent();
	}

	function retrieveAction() {
		echo" action starts";
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
		return $translator->translateContent();
	}
	
	function filterAction(){
		$config = $this->configurations;
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
		$view->set("relations",$model->getData());
	    $view->render($config['view.']['template']);
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		return $translator->translateContent();
	}
	
	function rssAction(){
		$config = $this->configurations;
		$translatorClassName = tx_div::makeInstanceClassName($config["view."]["translatorClassName"]);
		$model = $this->model;
		$view = $this->view;
		$view->setPathToTemplateDirectory($config["view."]["templatePath"]); 
		$view->set("relations",$model->getData());
	    $view->render($config['view.']['template']);
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile($config["view."]["keyOfPathToLanguageFile"]);
		$view->_iterator->array['_content'] = $translator->translateContent();
		$translator = new $translatorClassName($view);
		$translator->setPathToLanguageFile("EXT:crud/locallang.xml");
		//header("Content-Type: application/rss+xml");
		echo $translator->translateContent();
		die();
	}
	
	

}

?>
