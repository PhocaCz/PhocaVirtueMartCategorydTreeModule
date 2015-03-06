<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component   Phoca Component
 * @copyright   Copyright (C) Jan Pavelka www.phoca.cz
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later
 */
 
 /*
* Best selling Products module for VirtueMart
* @version $Id: mod_virtuemart_category.php 1160 2008-01-14 20:35:19Z soeren_nb $
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) John Syben (john@webme.co.nz)
* Conversion to Mambo and the rest:
* 	@copyright (C) 2004-2005 Soeren Eberhardt
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); // no direct access

require('helper.php');
JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');


function PhocaVmCategoryDTree ( $treeId, $category_id, $vendorId, $cache, $categoryModel, $p, $o) {
	
	static $level 		= 0;
	//static $parentmenu	= 0;
	
	//$categories		= $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $category_id );
	$categories 	= $categoryModel->getChildCategoryList($vendorId, $category_id, 'c.ordering');
	
	if ($level == 0) {
		$categories = array();
		$categories[0] = new StdClass();
		$categories[0]->virtuemart_category_id = '0';
		$categories[0]->category_name = JText::_('MOD_PHOCA_VM_CATDTREE_ALL_CATEGORIES');
		$categories[0]->category_description = JText::_('MOD_PHOCA_VM_CATDTREE_ALL_CATEGORIES');
		$categories[0]->metadesc = '';
		$categories[0]->metakey = '';
		$categories[0]->slug = '';
		$categories[0]->virtuemart_media_id = array();
		//$parentmenu++;
	}
	
	if (!empty($categories)) {
		
		foreach ($categories as $c) {

			//$childCats 	= $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $c->virtuemart_category_id );
			$childCats 	= $categoryModel->getChildCategoryList($vendorId, $c->virtuemart_category_id, 'c.ordering');
			$url 		= JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$c->virtuemart_category_id);
			
			//$parentCat 	= $cache->call( array( 'VirtueMartModelCategory', 'getParentCategory' ), $c->virtuemart_category_id );
			$parentCat	= $categoryModel->getParentCategory($c->virtuemart_category_id);
			
			if ($level == 0) {
				$o[$c->virtuemart_category_id] = $treeId.'.add(0, -1,\''.addslashes($c->category_name).'\', \''.$url.'\');'."\n";
			} else {
				$o[$c->virtuemart_category_id] = $treeId.'.add('. (int)$c->virtuemart_category_id.','.(int)$parentCat->virtuemart_category_id .',\''.addslashes($c->category_name).'\', \''.$url.'\');'."\n";
			}
			
			
			if (isset($childCats) && !empty($childCats)) {
				$level++;
				
				if ((int)$p['countlevels'] == (int)$level) {
					$level--;
				} else {
					$o = PhocaVmCategoryDTree( $treeId, $c->virtuemart_category_id, $vendorId, $cache, $categoryModel, $p, $o);
					$level--;
				}
			}
		}
	}
	return ($o);
}

// Params
$vendorId 				= '1';
$categoryModel			= new VirtueMartModelCategory();
$cache 					= & JFactory::getCache('com_virtuemart','callback');
$category_id 			= 0;//$params->get('parent_category_id', 0);
$p['countlevels'] 		= $params->get('count_levels', 10);// zero for unlimited


//$document			= &JFactory::getDocument();
JHTML::stylesheet( 'modules/mod_phoca_vm_catdtree/assets/dtree.css' );
JHTML::stylesheet( 'modules/mod_phoca_vm_catdtree/assets/custom.css' );
$document	= &JFactory::getDocument();
$document->addScript( JURI::base(true) . '/modules/mod_phoca_vm_catdtree/assets/dtree.js' );
$treeId 			= "d".uniqid( "tree_" );
$active_category_id = JRequest::getInt('virtuemart_category_id', '0');
$imgPath 			= JURI::base(false) . 'modules/mod_phoca_vm_catdtree/assets/';

$output ='<div style="text-align:left;">';
$output.='<div class="dtree">';
$output.='<script type="text/javascript">'."\n";
$output.='<!--'."\n";
$output.=''."\n";
$output.=''.$treeId.' = new dTree2749(\''.$treeId.'\', \''.$imgPath.'\');'."\n";
$output.=''."\n";

$o = '';
$tree = PhocaVmCategoryDTree ( $treeId, $category_id, $vendorId, $cache, $categoryModel, $p, $o);
if (!empty($tree)) {
	foreach ($tree as $k => $v) {
		$output .= $v;
	}
}

$output.=''."\n";
$output.='document.write('.$treeId.');'."\n";
$output.=''.$treeId.'.openTo('. (int) $active_category_id.',\'true\');'. "\n";
$output.=''."\n";
$output.='//-->'."\n";
$output.='</script>';
$output.='</div></div>';

require(JModuleHelper::getLayoutPath('mod_phoca_vm_catdtree'));