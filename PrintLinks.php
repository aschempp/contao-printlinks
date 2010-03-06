<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2009
 * @author     Andreas Schempp <andreas@schempp.ch
 * @license    LGPL
 */


class PrintLinks extends Frontend
{
	public function outputFrontendTemplate($strBuffer)
	{
		// Abort if insert tag is missing
		if (!preg_match('@\{\{printlinks::([^\}]*)\}\}@', $strBuffer, $arrMatch))
			return $strBuffer;
			
		// Include SimpleHtmlDom
		if (!function_exists('file_get_html'))
			require_once(TL_ROOT . '/system/modules/printlinks/simple_html_dom.php');
		
		// Load DOM
		$html = str_get_html($strBuffer);
		
		$strSelector = html_entity_decode($arrMatch[1]);
		$this->import('Environment');
		$arrLinks = array();
		$intCount = 1;

		foreach( $html->find($strSelector) as $container )
		{
			// Search for links
			foreach( $container->find('a') as $el )
			{
				// Exclude elements without valid/useful link
				if (!strlen($el->href) || substr($el->href, 0, 1) == '#' || $el->href == '/' || strpos($el->href, '#skipNavigation') !== false)
					continue;
					
				// Exclude TYPOlight "print as pdf" link
				if (strpos($el->href, 'pdf=') !== false)
					continue;
				
				$el->outertext = $el->outertext . '<sup class="printlink">' . $intCount . '</sup>';
				$arrLinks[$intCount] = $el->href;
				$intCount++;
			}
		}
		
		if (!count($arrLinks))
		{
			// Remove printlinks module
			foreach( $html->find('.mod_printlinks') as $mod )
			{
				$mod->outertext = '';
			}
			
			$strBuffer = $html->save();
			$html->clear();
			unset($html);
			return $strBuffer;
		}
			
		$strHtml = '<ol class="printlinks">';
		foreach( $arrLinks as $intId => $strLink )
		{
			// Add current website domain
			if (substr($strLink, 0, 4) != 'http')
			{
				$strLink = $this->Environment->base.$strLink;
			}
			
			$strHtml .= '<li class="link_' . $intId . ($intId == 1 ? ' first' : '') . ($intId == count($arrLinks) ? ' last' : '') . '">' . $strLink . '</li>';
		}
		$strHtml .= '</ol>';
		
		// Free resources
		$strBuffer = $html->save();
		$html->clear();
		unset($html);
		
		$strBuffer = str_replace($arrMatch[0], $strHtml, $strBuffer);
		
		return $strBuffer;
	}
}