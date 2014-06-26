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

class FhirBundleTest extends \PHPUnit_Framework_TestCase
{
	public function testFromFhir()
	{
		$fhir_object = (object)array(
			"resourceType" => "Bundle",
			"link" => array(
				(object)array(
					"rel" => "self",
					"href" => "http://example.com/self",
				),
				(object)array(
					"rel" => "fhir-base",
					"href" => "http://example.com/base",
				),
			),
			"entry" => array(
				(object)array(
					"content" => (object)array(
						"resourceType" => "Patient",
					),
				)
			),
		);

		$bundle = FhirBundle::fromFhir($fhir_object, FhirContext::create("http://example.com"));

		$this->assertEquals("http://example.com/self", $bundle->self_url);
		$this->assertEquals("http://example.com/base", $bundle->base_url);
		$this->assertCount(1, $bundle->entries);
		$this->assertInstanceOf("services\Patient", $bundle->entries[0]->resource);
	}
}
