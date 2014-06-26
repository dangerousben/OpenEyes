<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace services;

use Guzzle\Http\Client;

/**
 * External FHIR reference
 */
class FhirReference extends ResourceReference
{
	static public function create($url)
	{
		return new self(new Client($url), dirname(dirname($url)));
	}

	private $client;
	private $base_url;

	/**
	 * @param Client $client
	 * @param $base_url
	 */
	public function __construct(Client $client, $base_url)
	{
		$this->client = $client;
		$this->base_url = $base_url;
	}

	/**
	 * @return Resource
	 */
	public function fetch()
	{
		$res = $this->client->get()->send();

		$fhir_object = \Yii::app()->fhirMarshal->parseXml($res->getBody());
		$resource_type = $fhir_object->resourceType;

		// FIXME: need a sane way to map from FHIR resource types to service layer classes
		$class = 'services\\' . $resource_type;

		return $class::fromFhir($fhir_object, FhirContext::create($this->base_url));
	}

	public function getLastModified()
	{
	}

	public function update(Resource $resource)
	{
	}

	public function delete()
	{
	}

	public function toFhir()
	{
	}
}
