<?php
/**
* Podcast Manager for Joomla!
*
* @package     PodcastManager
* @subpackage  com_podcastmanager
*
* @copyright   Copyright (C) 2011 Michael Babker. All rights reserved.
* @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*
* Podcast Manager is based upon the ideas found in Podcast Suite created by Joe LeBlanc
* Original copyright (c) 2005 - 2008 Joseph L. LeBlanc and released under the GPLv2 license
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Podcast management view class.
 *
 * @package     PodcastManager
 * @subpackage  com_podcastmanager
 * @since       1.6
 */
class PodcastManagerViewPodcasts extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Initialise variables
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// We don't need the toolbar or external media in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			// Add the component media
			JHtml::stylesheet('administrator/components/com_podcastmanager/media/css/template.css', false, false, false);
			JHtml::script('administrator/components/com_podcastmanager/media/js/podcasts.js', false, false);

			$this->addToolbar();
		}

		include_once JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/feedfilter.php';
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo	= PodcastManagerHelper::getActions();

		JToolBarHelper::title(JText::_('COM_PODCASTMANAGER_VIEW_PODCASTS_TITLE'), 'podcastmanager.png');

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('podcast.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('podcast.edit');
		}
		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publish('podcasts.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('podcasts.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::checkin('podcasts.checkin');
			JToolBarHelper::divider();
		}
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'podcasts.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('podcasts.trash');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_podcastmanager');
		}
	}
}
