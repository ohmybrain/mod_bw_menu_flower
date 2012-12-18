<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_bw_menu_flower
 *
 * @copyright   Copyright (C) 2012 Brian Williford, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Last Modified: BW 121210
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

// menus
$list		= modBwMenuFlowerHelper::getList($params);
$active		= modBwMenuFlowerHelper::getActive($params);
$active_id 	= $active->id;
$path		= $active->tree;

$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));


//images
$link	= $params->get('link');

$folder	= modBwMenuFlowerHelper::getFolder($params);
$images	= modBwMenuFlowerHelper::getImages($params, $folder);

if (!count($images)) {
	//echo JText::_('MOD_BW_MENU_FLOWER_NO_IMAGES');
	//return;
}

$image = modBwMenuFlowerHelper::getRandomImage($params, $images);

// tmpl
if(count($list)) {
	require JModuleHelper::getLayoutPath('mod_bw_menu_flower', $params->get('layout', 'default'));
}
