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

class FhirClient
{
	static public function create($base_url)
	{
		$http_client = new \Zend_Http_Client;
		$http_client->setHeader('Accept', 'application/xml');

		return new self($base_url, $http_client);
	}

	private $base_url;
	private $http_client;

	/**
	 * @param string $base_url
	 * @param \Zend_Http_Client $http_client
	 */
	public function __construct($base_url, \Zend_Http_Client $http_client)
	{
		$this->base_url = $base_url;
		$this->http_client = $http_client;
	}

	/**
	 * @param string $relative_url
	 * @param string $method
	 * @param string $body
	 * @return DataObject|null
	 */
	public function request($relative_url, $method, $body = null)
	{
		$url = "{$this->base_url}/{$relative_url}";

		$this->http_client->setUri($url);
		$this->http_client->setMethod($method);
		if ($body) {
			$this->http_client->setRawData($body, 'application/xml+fhir; charset=utf-8');
		}
		$response = $this->http_client->request();
		$this->http_client->resetParameters();

		if (($body = $response->getBody())) {
			$use_errors = libxml_use_internal_errors(true);

			$fhir_obj = Yii::app()->fhirMarshal->parseXml($body);

			$errors = lib_xml_get_errors();
			lib_xml_use_internal_errors($use_errors);

			if ($errors) {
				throw new Exception("Error parsing XML response from {$method} to {$url}: " . print_r($errors, true));
			}

			switch ($fhir_obj->resourceType) {
				case 'Bundle':
					$class = 'services\FhirBundle';
					break;
				case 'OperationOutcome':
					$class = 'services\FhirOutcome';
					break;
				default:
					$class = \Yii::app()->fhirMap->fhirResTypeToOeResType($fhir_obj->resourceType);
			}

			$obj = $class::fromFhir($fhir_obj);
		} else {
			$obj = null;
		}

		if ($response->isError()) {
			throw new \Exception("TODO");
		}

		return $obj;
	}
}
