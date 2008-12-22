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
require_once(t3lib_extMgm::extPath('crud__controller') . 'models/class.tx_crud__controller_models_generic.php');
require_once(t3lib_extMgm::extPath('crud__controller') . 'views/class.tx_crud__controller_views_generic.php');
class tx_crud__controller_generic extends tx_lib_controller{

	function createAction() {
		$typoscript = $this->configurations->getArrayCopy();
		$config = $typoscript[$this->action.'.'];
		$config['storage.']['nameSpace'] = $this->configurations->get("namespace");
		$config['storage.']['fields'] = $this->configurations->get("fields");
		$nodes = explode("_",$this->configurations->get("nodesPid"));
		$config['storage.']['nodes'] = $this->configurations->get("nodesPid");
		$enable = explode(",",$typoscript['enable']);
		foreach ($enable as $features) {
			$config['enable.'][$features] = 1;
		}
		
		$this->configurations->set($this->action.'.',$config);
		$modelClassName = tx_div::makeInstanceClassName($config['setup.']['model']);
		$templateEngineClassName = tx_div::makeInstanceClassName($config['setup.']['view']);
		$translatorClassName = tx_div::makeInstanceClassName($config["setup."]["translatorClassName"]);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["setup."]["templatePath"]); 
		$view->render($config['setup.']['template']);
		$translator = new $translatorClassName($view->view, $this);
		$translator->setPathToLanguageFile($config["setup."]["keyOfPathToLanguageFile"]);
        return $translator->translateContent();
	}
	
	function updateAction() {
		$typoscript = $this->configurations->getArrayCopy();
		$config = $typoscript[$this->action.'.'];
		$config['storage.']['nameSpace'] = $this->configurations->get("namespace");
		$config['storage.']['fields'] = $this->configurations->get("fields");
		$nodes = explode("_",$this->configurations->get("nodes"));
		$config['storage.']['nodes'] = $nodes[count($nodes)-1];
		$enable = explode(",",$typoscript['enable']);
		foreach ($enable as $features) {
			$config['enable.'][$features] = 1;
		}
		
		$this->configurations->set($this->action.'.',$config);
		$modelClassName = tx_div::makeInstanceClassName($config['setup.']['model']);
		$templateEngineClassName = tx_div::makeInstanceClassName($config['setup.']['view']);
		$translatorClassName = tx_div::makeInstanceClassName($config["setup."]["translatorClassName"]);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["setup."]["templatePath"]); 
		$view->render($config['setup.']['template']);
		$translator = new $translatorClassName($view->view, $this);
		$translator->setPathToLanguageFile($config["setup."]["keyOfPathToLanguageFile"]);
        return $translator->translateContent();
	}
	
	function deleteAction() {
		$typoscript = $this->configurations->getArrayCopy();
		$config = $typoscript[$this->action.'.'];
		$config['storage.']['fields'] = "deleted";
		$nodes = explode("_",$this->configurations->get("nodesUid"));
		$config['storage.']['nodes'] = $nodes[count($nodes)-1];
		unset($nodes[count($nodes)-1]);
		$config['storage.']['nameSpace'] = implode("_",$nodes);
		$enable = explode(",",$typoscript['enable']);
		foreach ($enable as $features) {
			$config['enable.'][$features] = 1;
		}
		$this->configurations->set($this->action.'.',$config);
		$modelClassName = tx_div::makeInstanceClassName($config['setup.']['model']);
		$templateEngineClassName = tx_div::makeInstanceClassName($config['setup.']['view']);
		$translatorClassName = tx_div::makeInstanceClassName($config["setup."]["translatorClassName"]);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["setup."]["templatePath"]); 
		$view->render($config['setup.']['template']);
		$translator = new $translatorClassName($view->view, $this);
		$translator->setPathToLanguageFile($config["setup."]["keyOfPathToLanguageFile"]);
        return $translator->translateContent();
	}
	
	function uploadAction() {
		$typoscript = $this->configurations->getArrayCopy();
		//debug($typoscript);
		$config = $typoscript[$this->action.'.'];
		$config['storage.']['nameSpace'] = "tx_testupload";
		$config['storage.']['fields'] = "files";
		$config['storage.']['nodes'] = $this->configurations->get("nodesUpload");
		$config['storage.']['virtual.']['tx_testupload.']['files.']['config.']['uploadfolder'] = $this->configurations->get("nodesUpload");
		$config['storage.']['virtual.']['tx_testupload.']['files.']['config.']['size'] = $this->configurations->get("uploadDefaultSize");
		$config['storage.']['virtual.']['tx_testupload.']['files.']['config.']['minitems'] = $this->configurations->get("uploadMinSize");
		$config['storage.']['virtual.']['tx_testupload.']['files.']['config.']['maxitems'] = $this->configurations->get("uploadMaxSize");
		$config['storage.']['virtual.']['tx_testupload.']['files.']['config.']['allowed'] = $config['filetypes.'][$this->configurations->get("uploadAllowed")."."]["value"];
		$config['storage.']['virtual.']['tx_testupload.']['files.']['config.']['max_size'] = $this->configurations->get("uploadMaxKilobytes");
		$config['storage.']['virtual.']['tx_testupload.']['files.']['label'] = $this->configurations->get("uploadLabel");
		$config['storage.']['virtual.']['tx_testupload.']['files.']['help'] = $this->configurations->get("uploadHelp");
		$enable = explode(",",$typoscript['enable']);
		foreach ($enable as $features) {
			$config['enable.'][$features] = 1;
		}
		$this->configurations->set($this->action.'.',$config);
		//debug($config);
		$modelClassName = tx_div::makeInstanceClassName($config['setup.']['model']);
		$templateEngineClassName = tx_div::makeInstanceClassName($config['setup.']['view']);
		$translatorClassName = tx_div::makeInstanceClassName($config["setup."]["translatorClassName"]);
		$model = new $modelClassName($this);
		$view = new $templateEngineClassName($model->controller); 
		$view->setPathToTemplateDirectory($config["setup."]["templatePath"]); 
		$view->render($config['setup.']['template']);
		$translator = new $translatorClassName($view->view, $this);
		$translator->setPathToLanguageFile($config["setup."]["keyOfPathToLanguageFile"]);
        return $translator->translateContent();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/controllers/class.tx_crud_controllers_common.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/controllers/class.tx_crud_controllers_common.php']);
}

?>