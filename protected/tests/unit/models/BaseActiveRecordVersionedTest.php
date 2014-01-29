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

class BaseActiveRecordVersionedTest extends CDbTestCase
{
	public $fixtures = array(
		'Firm',
		'Patient',
		'User',
	);

	public function updateManyToManyDataProvider()
	{
		return array(
			array(
				array(),
				1,
				array(1, 2, 3, 4),
			),
			array(
				array(
					1 => array(1, 2, 3, 4),
					2 => array(1, 2, 3, 4),
				),
				1,
				array(1, 2, 3, 4),
			),
			array(
				array(
					1 => array(1, 2),
					2 => array(1, 2),
				),
				1,
				array(3, 4),
			),
			array(
				array(
					1 => array(1, 2, 3),
					2 => array(1, 2, 3),
				),
				1,
				array(2, 3, 4),
			),
		);
	}

	/**
	 * @dataProvider updateManyToManyDataProvider
	 */
	public function testUpdateManyToMany(array $initial, $record_id, array $related_ids)
	{
		$this->initManyToMany($initial);
		FirmUserAssignment::model()->updateManyToMany(Firm::model()->findByPk($record_id), $related_ids);
		$this->checkManyToMany($initial, $record_id, $related_ids);
	}

	/**
	 * @dataProvider updateManyToManyDataProvider
	 */
	public function testUpdateManyToMany_WithActiveRecords(array $initial, $record_id, array $related_ids)
	{
		$records = array();
		foreach ($related_ids as $id) {
			$records[] = User::model()->findByPk($id);
		}

		$this->initManyToMany($initial);
		FirmUserAssignment::model()->updateManyToMany(Firm::model()->findByPk($record_id), $records);
		$this->checkManyToMany($initial, $record_id, $related_ids);
	}

	private function initManyToMany($initial)
	{
		Yii::app()->db->createCommand()->delete("firm_user_assignment_version");
		Yii::app()->db->createCommand()->delete("firm_user_assignment");

		foreach ($initial as $firm_id => $user_ids) {
			foreach ($user_ids as $user_id) {
				Yii::app()->db->createCommand()->insert("firm_user_assignment", array('firm_id' => $firm_id, 'user_id' => $user_id));
			}
		}
	}

	private function checkManyToMany($initial, $record_id, array $related_ids)
	{
		foreach ($initial as $firm_id => $user_ids) {
			$undeleted = Yii::app()->db->createCommand()->select('user_id')->from('firm_user_assignment')->where('firm_id = ? and deleted = 0', array($firm_id))->order('user_id')->queryColumn();
			$deleted = Yii::app()->db->createCommand()->select('user_id')->from('firm_user_assignment')->where('firm_id = ? and deleted = 1', array($firm_id))->order('user_id')->queryColumn();
			$all = Yii::app()->db->createCommand()->select('user_id')->from('firm_user_assignment')->where('firm_id = ?', array($firm_id))->order('user_id')->queryColumn();
			$archived = Yii::app()->db->createCommand()->select('user_id')->from('firm_user_assignment_version')->where('firm_id = ?', array($firm_id))->order('user_id')->queryColumn();

			if ($firm_id == $record_id) {
				$this->assertEquals($related_ids, $undeleted);
				$this->assertEquals(array_diff($user_ids, $related_ids), $deleted);
				$this->assertEquals(array_values(array_unique(array_merge($user_ids, $related_ids))), $all);
				$this->assertEquals($deleted, $archived);
			} else {
				$this->assertEquals($user_ids, $undeleted);
				$this->assertEmpty($deleted);
				$this->assertEquals($user_ids, $all);
				$this->assertEmpty($archived);
			}
		}
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage No relationship found
	 */
	public function testUpdateManyToMany_NoRelationshipFound()
	{
		FirmUserAssignment::model()->updateManyToMany(Patient::model()->findByPk(1), array(1, 2, 3));
	}
}
