<?php
/**
* Podcast Manager for Joomla!
*
* @version		$Id: view.html.php 9 2011-01-05 17:24:41Z mbabker $
* @copyright	Copyright (C) 2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
*/

// Restricted access
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

class PodcastManagerViewFiles extends JView {
	public function display($tpl = null) {
		global $option;
		$app	= JFactory::getApplication();

		$params =& JComponentHelper::getParams($option);

		$filter_published = $app->getUserStateFromRequest($option . 'filter_published', 'filter_published', '*', 'word');
		$filter_metadata = $app->getUserStateFromRequest($option . 'filter_metadata', 'filter_metadata', '*', 'word');

		$filter = array();
		$filter['published'] = self::filter($filter_published, JText::_('Published'), JText::_('Unpublished'), JText::_('Published'), 'filter_published');
		$filter['metadata'] = self::filter($filter_metadata, JText::_('Has Metadata'), JText::_('No Metadata'), JText::_('Metadata'), 'filter_metadata');

		$data =& $this->get('data');
		$folder = $this->get('folder');
		$pagination =& $this->get('pagination');
		$hasSpaces = $this->get('hasSpaces');
		
		$this->assignRef('params', $params);
		$this->assignRef('filter', $filter);
		$this->assignRef('data', $data);
		$this->assignRef('folder', $folder);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('hasSpaces', $hasSpaces);

		parent::display($tpl);
	}

	// based on JHTMLGrid::state
	private static function filter($filter_state = '*', $state1, $state2, $desc, $requestVar = 'filter_state') {
		$state[] = JHTML::_('select.option', '*', '- ' . $desc . ' -');
		$state[] = JHTML::_('select.option', 'on', $state1);
		$state[] = JHTML::_('select.option', 'off', $state2);
		
		return JHTML::_('select.genericlist', $state, $requestVar, 'class="inputbox" size="1" onchange="submitform( );"', 'value', 'text', $filter_state);
	}
}
