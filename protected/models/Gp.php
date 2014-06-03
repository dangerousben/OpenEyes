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

/**
 * This is the model class for table "Gp".
 *
 * The followings are the available columns in table 'Gp':
 * @property integer $id
 *
 * The followings are the available model relations:
 * @property Contact $contact
 */
class Gp extends BaseActiveRecordVersioned
{
	const UNKNOWN_SALUTATION = 'Doctor';
	const UNKNOWN_NAME = 'The General Practitioner';

	public $use_pas = TRUE;

	/**
	 * Suppress PAS integration
	 * @return Gp
	 */
	public function noPas()
	{
		// Clone to avoid singleton problems with use_pas flag
		$model = clone $this;
		$model->use_pas = FALSE;
		return $model;
	}

	public function behaviors()
	{
		return array(
			'ContactBehavior' => array(
				'class' => 'application.behaviors.ContactBehavior',
			),
		);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'gp';
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'contact' => array(self::BELONGS_TO, 'Contact', 'contact_id'),
		);
	}

	/**
	* Pass through use_pas flag to allow pas supression
	* @see CActiveRecord::instantiate()
	*/
	protected function instantiate($attributes)
	{
		$model = parent::instantiate($attributes);
		$model->use_pas = $this->use_pas;
		return $model;
	}

	/**
	 * Raise event to allow external data sources to update gp
	 * @see CActiveRecord::afterFind()
	 */
	protected function afterFind()
	{
		parent::afterFind();
		Yii::app()->event->dispatch('gp_after_find', array('gp' => $this));
	}

	public function getLetterAddress($params=array())
	{
		if (!isset($params['patient'])) {
			throw new Exception("Patient must be passed for GP contacts.");
		}

		$contact = $address = null;

		if ($params['patient']->practice) {
			if (@$params['contact']) {
				$contactRelation = $params['contact'];
				$contact = $params['patient']->practice->$contactRelation;
			} else {
				$contact = $params['patient']->practice->contact;
			}

			$address = $contact->address;
		}

		return $this->formatLetterAddress($contact, $address, $params);
	}

	public function getPrefix()
	{
		return 'GP';
	}

	public function getCorrespondenceName()
	{
		return $this->contact->fullName;
	}
}
