<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class BaseAPI
{
	private $event_type;

	public function __construct(EventType $event_type)
	{
		$this->event_type = $event_type;
	}

	/*
	 * gets the event type for the api instance
	 *
	 * @return EventType $event_type
	 */
	protected function getEventType()
	{
		return $this->event_type;
	}

	/**
	 * gets all the events in the episode for the event type this API is for, most recent first.
	 *
	 * @param Episode $episode - the episode
	 * @return array - list of events of the type for this API instance
	 */
	protected function getEventsInEpisode(Episode $episode)
	{
		return $episode->getAllEventsByType($this->getEventType()->id);
	}

	/**
	 * @param Episode $episode
	 * @return Event|null
	 */
	protected function getMostRecentEventInEpisode(Episode $episode)
	{
		return $episode->getMostRecentEventByType($this->getEventType()->id);
	}

	/**
	 * gets the element of type $element_class in the latest event from the given episode
	 *
	 * @param Episode $episode - the episode
	 * @param string $element_class - the element class
	 *
	 * @return BaseElement|null - the element type requested, or null
	 */
	protected function getElementForLatestEventInEpisode(Episode $episode, $element_class)
	{
		if (($event = $this->getMostRecentEventInEpisode($episode))) {
			return $event->getElementByClass($element_class);
		}

		return null;
	}

	/*
	 * gets the most recent instance of a specific element in the current episode
	 *
	 * @param Episode $episode
	 * @param string $element_class
	 * @return BaseElement|null
	 */
	protected function getMostRecentElementInEpisode(Episode $episode, $element_class)
	{
		foreach ($this->getEventsInEpisode($episode) as $event) {
			if (($element = $event->getElementByClass($element_class))) {
				return $element;
			}
		}

		return null;
	}

	/*
	 * Stub method to prevent crashes when getEpisodeHTML() is called for every installed module
	 *
	 */
	public function getEpisodeHTML($episode_id)
	{
	}
}
