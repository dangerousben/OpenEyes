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

use \Guzzle\Http\Client;

/**
 * External FHIR service
 */
class FhirService implements Service
{
	static public function load(array $params = array())
	{
		if (!isset($params['base_url'])) {
			throw new \Exception("No base_url defined for FHIR service");
		}

		if (!isset($params['resource_type'])) {
			throw new \Exception("No resource_type defined for FHIR service");
		}

		return new self(new Client($params['base_url']), $params['base_url'], $params['resource_type']);
	}

	private $client;
	private $base_url;
	private $resource_type;

	/**
	 * @param string $base_url
	 */
	public function __construct(Client $client, $base_url, $resource_type)
	{
		$this->client = $client;
		$this->base_url = $base_url;
		$this->resource_type = $resource_type;
	}

	/**
	 * @param scalar $id
	 * @return FhirReference
	 */
	public function getReference($id)
	{
		$url = "{$this->base_url}/{$this->resource_type}/" . urlencode($id);

		return FhirReference::create($url);
	}

	/*
	 * @param Resource $resource
	 * @return FhirReference
	 */
	public function create(Resource $resource)
	{
		$url = "{$this->base_url}/{$this->resource_type}";
		$body = \Yii::app()->fhirMarshal()->renderXml($resource->toFhir());

		// post, extract URL from result, return ref
	}

	/**
	 * @param array $params
	 * @return Resource[]
	 */
	public function search(array $params)
	{
		$res = $this->client->get("{$this->resource_type}?" . http_build_query($params))->send();

		$fhir_object = \Yii::app()->fhirMarshal->parseXml($res->getBody());
		$bundle = FhirBundle::fromFhir($fhir_object, FhirContext::create($this->base_url));

		$resources = array();
		foreach ($bundle->entries as $entry) {
			$resources[] = $entry->resource;
		}
		return $resources;
	}
}
