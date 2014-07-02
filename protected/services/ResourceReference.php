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

abstract class ResourceReference implements FhirCompatible
{
	/**
	 * @param \StdClass $fhir_object
	 * @param FhirContext $context
	 * @return ResourceReference
	 */
	static public function fromFhir($fhir_object, FhirContext $context)
	{
		$url = $context->canonicaliseUrl($fhir_object->reference);

		if (($local_base = \Yii::app()->service->getLocalFhirBaseUrl()) &&
			(preg_match('|^' . preg_quote($local_base) . '/(.*)/(.*)$|', $url, $m))) {

			if (!($ref = \Yii::app()->service->fhirIdToReference($m[1], $m[2]))) {
				throw new InvalidValue("Invalid local resource reference: {$url}");
			}

			return $ref;
		} else {
			return FhirReference::create($url);
		}
	}

	/**
	 * Fetch the resource this reference refers to
	 *
	 * @return Resource
	 */
	abstract public function fetch();

	/**
	 * Get the last modified date for this resource without fetching it
	 *
	 * @param scalar $id
	 * @return int
	 */
	abstract public function getLastModified();

	/**
	 * Update this resource
	 *
	 * @param scalar $id
	 * @param Resource $resource
	 */
	abstract public function update(Resource $resource);

	/**
	 * Delete this resource
	 *
	 * @param scalar $id
	 */
	abstract public function delete();
}
