<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_bw_menu_flower
 *
 * @copyright   Copyright (C) 2012 Brian Williford, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @ Inquiries	info@ohmybrain.com - http://www.brianwilliford.com 
 * Last Modified: BW 121209
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_bw_menu_flower
 *
 * @package     Joomla.Site
 * @subpackage  mod_bw_menu_flower
 * @since       1.5
 */
class modBwMenuFlowerHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param	JRegistry	$params	The module options.
	 *
	 * @return	array
	 * @since	1.5
	 */
	public static function getList(&$params)
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// Get active menu item
		$active = self::getActive($params);
		$user = JFactory::getUser();
		$levels = $user->getAuthorisedViewLevels();
		asort($levels);
		$key = 'menu_items' . $params . implode(',', $levels) . '.' . $active->id;
		$cache = JFactory::getCache('mod_bw_menu_flower', '');
		if (!($items = $cache->get($key)))
		{
			$path    = $active->tree;
			$start   = (int) $params->get('startLevel');
			$end     = (int) $params->get('endLevel');
			$showAll = $params->get('showAllChildren');
			$items   = $menu->getItems('menutype', $params->get('menutype'));

			$lastitem = 0;

			if ($items)
			{
				foreach($items as $i => $item)
				{
					if (($start && $start > $item->level)
						|| ($end && $item->level > $end)
						|| (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
						|| ($start > 1 && !in_array($item->tree[$start - 2], $path)))
					{
						unset($items[$i]);
						continue;
					}

					$item->deeper     = false;
					$item->shallower  = false;
					$item->level_diff = 0;

					if (isset($items[$lastitem]))
					{
						$items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
						$items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
						$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
					}

					$item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);

					$lastitem     = $i;
					$item->active = false;
					$item->flink  = $item->link;

					// Reverted back for CMS version 2.5.6
					switch ($item->type)
					{
						case 'separator':
						case 'heading':
							// No further action needed.
							continue;

						case 'url':
							if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
							{
								// If this is an internal Joomla link, ensure the Itemid is set.
								$item->flink = $item->link . '&Itemid=' . $item->id;
							}
							break;

						case 'alias':
							// If this is an alias use the item id stored in the parameters to make the link.
							$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
							break;

						default:
							$router = JSite::getRouter();
							if ($router->getMode() == JROUTER_MODE_SEF)
							{
								$item->flink = 'index.php?Itemid=' . $item->id;
							}
							else
							{
								$item->flink .= '&Itemid=' . $item->id;
							}
							break;
					}

					if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
					{
						$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
					}
					else
					{
						$item->flink = JRoute::_($item->flink);
					}

					// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
					// when the cause of that is found the argument should be removed
					$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
					$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
					$item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
				}

				if (isset($items[$lastitem]))
				{
					$items[$lastitem]->deeper     = (($start?$start:1) > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = (($start?$start:1) < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start?$start:1));
				}
			}

			$cache->store($items, $key);
		}
		return $items;
	}

	/**
	 * Get active menu item.
	 *
	 * @param	JRegistry	$params	The module options.
	 *
	 * @return	object
	 * @since	3.0
	 */
	public static function getActive(&$params)
	{
		$menu = JFactory::getApplication()->getMenu();

		// Get active menu item from parameters
		if ($params->get('active')) {
			$active = $menu->getItem($params->get('active'));
		} else {
			$active = false;
		}

		// If no active menu, use current or default
		if (!$active) {
			$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
		}

		return $active;
	}
	
	/* aaded random image */
		public static function getRandomImage(&$params, $images)
	{
		$width	= $params->get('width');
		$height	= $params->get('height');

		$i      = count($images);
		$random = mt_rand(0, $i - 1);
		$image  = $images[$random];
		$size   = getimagesize(JPATH_BASE . '/' . $image->folder . '/' . $image->name);

		if ($width == '') {
			$width = 100;
		}

		if ($size[0] < $width) {
			$width = $size[0];
		}

		$coeff = $size[0] / $size[1];
		if ($height == '') {
			$height = (int) ($width / $coeff);
		} else {
			$newheight = min($height, (int) ($width / $coeff));
			if ($newheight < $height) {
				$height = $newheight;
			} else {
				$width = $height * $coeff;
			}
		}

		$image->width	= $width;
		$image->height	= $height;
		$image->folder	= str_replace('\\', '/', $image->folder);

		return $image;
	}

	public static function getImages(&$params, $folder)
	{
		$type		= $params->get('type', 'jpg');

		$files	= array();
		$images	= array();

		$dir = JPATH_BASE . '/' . $folder;

		// check if directory exists
		if (is_dir($dir))
		{
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..' && $file != 'CVS' && $file != 'index.html') {
						$files[] = $file;
					}
				}
			}
			closedir($handle);

			$i = 0;
			foreach ($files as $img)
			{
				if (!is_dir($dir . '/' . $img))
				{
					if (preg_match('/'.$type.'/', $img)) {
						$images[$i] = new stdClass;

						$images[$i]->name	= $img;
						$images[$i]->folder	= $folder;
						$i++;
					}
				}
			}
		}

		return $images;
	}

	public static function getFolder(&$params)
	{
		$folder	= $params->get('folder');

		$LiveSite	= JURI::base();

		// if folder includes livesite info, remove
		if (JString::strpos($folder, $LiveSite) === 0) {
			$folder = str_replace($LiveSite, '', $folder);
		}
		// if folder includes absolute path, remove
		if (JString::strpos($folder, JPATH_SITE) === 0) {
			$folder = str_replace(JPATH_BASE, '', $folder);
		}
		$folder = str_replace('\\', DIRECTORY_SEPARATOR, $folder);
		$folder = str_replace('/', DIRECTORY_SEPARATOR, $folder);

		return $folder;
	}
}
