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

abstract class DataObject implements FhirCompatible
{
	/**
	 * The FHIR type that this class corresponds to, if left unset the unqualified name of the class is assumed
	 */
	static protected $fhir_type;

	/**
	 * Get the FHIR type that this class corresponds to
	 *
	 * @return string
	 */
	static public function getFhirType()
	{
		if (isset(static::$fhir_type)) {
			return static::$fhir_type;
		} else {
			$class = new \ReflectionClass(get_called_class());
			return $class->getShortName();
		}
	}

	/**
	 * @param array $values
	 * @return DataObject
	 */
	static public function fromFhirValues(array $values)
	{
		return new static($values);
	}

	/**
	 * Convert a FHIR object into a service layer object
	 *
	 * @param StdClass $fhirObject
	 * @param FhirContext $context
	 * @return DataObject
	 */
	static public function fromFhir($fhir_object, FhirContext $context)
	{
		$fhir_object = clone($fhir_object);

		static::subObjectsFromFhir($fhir_object, $context);

		$values = static::getFhirTemplate()->match($fhir_object, $warnings);
		if (is_null($values)) {
			throw new InvalidStructure("Failed to match object of type '{$fhir_type}': " . implode("; ", $warnings));
		}

		return static::fromFhirValues($values);
	}

	static protected function subObjectsFromFhir($fhir_object, FhirContext $context)
	{
		$fhir_type = static::getFhirType();
		$schema = \Yii::app()->fhirMarshal->getSchema($fhir_type);

		foreach ($fhir_object as $name => &$value) {
			if ($name == 'resourceType' || $name == 'id' || $name[0] == '_') continue;

			$valueType = $schema[$name]['type'];
			$class = static::getServiceClass($valueType);
			if (!$class) continue;

			if (is_array($value)) {
				foreach ($value as &$v) {
					$v = $class::fromFhir($v, $context);
				}
			} else {
				$value = $class::fromFhir($value, $context);
			}
		}
	}

	static protected function getServiceClass($fhir_type)
	{
		switch ($fhir_type) {
			case 'date':
			case 'dateTime':
				return 'services\\' . ucfirst($fhir_type);
				break;
			default:
				$class = "services\\{$fhir_type}";
				return @class_exists($class) ? $class : null;
		}
	}

	static protected function getFhirTemplate()
	{
		$class = new \ReflectionClass(get_called_class());
		$path = dirname($class->getFileName()) . '/fhir_templates/' . $class->getShortName() . '.json';

		return \DataTemplate::fromJsonFile($path);
	}

	/**
	 * @param array $values
	 */
	public function __construct($values=array())
	{
		foreach ($values as $name => $value) {
			$this->$name = $value;
		}
	}

	/**
	 * Compare two DataObjects in terms of their public properties
	 *
	 * @param DataObject $object
	 * @return boolean
	 */
	public function isEqual(DataObject $object)
	{
		if (get_class($this) != get_class($object)) {
			return false;
		}

		$rf_obj = new \ReflectionObject($this);
		$rf_props = $rf_obj->getProperties(\ReflectionProperty::IS_PUBLIC);

		foreach ($rf_props as $rf_prop) {
			if ($rf_prop->getValue($this) != $rf_prop->getValue($object)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function toFhirValues()
	{
		$values = get_object_vars($this);
		foreach ($values as &$value) {
			if ($value === '') $value = null;
		}
		return $values;
	}

	/**
	 * Convert this object to it's FHIR representation
	 *
	 * @return StdClass
	 */
	public function toFhir()
	{
		$values = $this->toFhirValues();
		$this->subObjectsToFhir($values);

		return static::getFhirTemplate()->generate($values);
	}

	private function subObjectsToFhir(&$values)
	{
		foreach ($values as &$value) {
			if ($value instanceof FhirCompatible) {
				$value = $value->toFhir();
			} elseif (is_array($value)) {
				$this->subObjectsToFhir($value);
			}
		}
	}

	/**
	 * Convert this object to a serialised blob
	 *
	 * @return string
	 */
	public function serialise()
	{
		$values = get_object_vars($this);
		return json_encode($this->serialiseSubObjects($values));
	}

	private function serialiseSubObjects($values)
	{
		foreach ($values as $key => $value) {
			if ($value instanceof DataObject) {
				$values[$key] = array();
				foreach ($value as $_key => $_value) {
					if ($_value instanceof ModelReference) {
						$values[$key][$_key] = array(
							'service' => $_value->getServiceName(),
							'id' => $_value->getId(),
						);
					} else {
						$values[$key][$_key] = $_value;
					}
				}
			} else if ($value instanceof ModelReference) {
				$values[$key] = array(
					'service' => $value->getServiceName(),
					'id' => $value->getId(),
				);
			} else if (is_array($value)) {
				$values[$key] = $this->serialiseSubObjects($value);
			}
		}

		return $values;
	}

	static public function fromObject($object)
	{
		$data_class = get_called_class();
		return new $data_class((array)$object);
	}
}
