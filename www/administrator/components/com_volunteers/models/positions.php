<?php
/**
 * @package    Joomla! Volunteers
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Methods supporting a list of positions records.
 */
class VolunteersModelPositions extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'state', 'a.state',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type'));
		$this->setState('filter.acl', $this->getUserStateFromRequest($this->context . '.filter.acl', 'filter_acl'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_volunteers');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.type');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query
			->select($this->getState('list.select', array('a.*')))
			->from($db->quoteName('#__volunteers_positions') . ' AS a');

		// Join over the users for the checked_out user.
		$query
			->select('checked_out.name AS editor')
			->join('LEFT', '#__users AS ' . $db->quoteName('checked_out') . ' ON checked_out.id = a.checked_out');

		// Filter by published state
		$state = $this->getState('filter.state', 1);

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Filter by published state
		$type = $this->getState('filter.type');

		if (is_numeric($type) && ($type > 0))
		{
			$query->where('a.type = ' . (int) $type);
		}

		// Filter by acl state
		$acl = $this->getState('filter.acl');

		switch ($acl)
		{
			case 'edit_department':
				$query->where('a.edit_department = 1');
				break;
			case 'edit':
				$query->where('a.edit = 1');
				break;
			case 'create_report':
				$query->where('a.create_report = 1');
				break;
			case 'create_team':
				$query->where('a.create_team = 1');
				break;
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
