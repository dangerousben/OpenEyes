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
class DrugSetItemTest extends CDbTestCase
{

	/**
	 * @var DrugSetItem
	 */
	protected $model;
	public $fixtures = array(
		'drugsetitems' => 'DrugSetItem',
	);

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->model = new DrugSetItem;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers DrugForm::model
	 * @todo   Implement testModel().
	 */
	public function testModel()
	{

		$this->assertEquals('DrugSetItem', get_class(DrugSetItem::model()), 'Class name should match model.');
	}

	/**
	 * @covers DrugForm::tableName
	 * @todo   Implement testTableName().
	 */
	public function testTableName()
	{

		$this->assertEquals('drug_set_item', $this->model->tableName());
	}

	/**
	 * @covers DrugForm::rules
	 * @todo   Implement testRules().
	 */
	public function testRules()
	{

		$this->assertTrue($this->drugsetitems('drugsetitem1')->validate());
		$this->assertEmpty($this->drugsetitems('drugsetitem2')->errors);
	}

	/**
	 * @covers DrugSetItem::relations
	 * @todo   Implement testRelations().
	 */
	public function testRelations()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers DrugSetItem::attributeLabels
	 * @todo   Implement testAttributeLabels().
	 */
	public function testAttributeLabels()
	{

		$expected = array();

		$this->assertEquals($expected, $this->model->attributeLabels());
	}

	/**
	 * @covers DrugSetItem::search
	 * @todo   Implement testSearch().
	 */
	public function testSearch()
	{
		$this->markTestIncomplete(' needs TLC ');
		$this->model->setAttributes($this->drugsetitems('drugsetitem1')->getAttributes());
		$results = $this->model->search();
		$data = $results->getData();

		$expectedKeys = array('drugsetitem1');
		$expectedResults = array();
		if (!empty($expectedKeys)) {
			foreach ($expectedKeys as $key) {
				$expectedResults[] = $this->drugsetitems($key);
			}
		}
		$this->assertEquals(1, $results->getItemCount());
		$this->assertEquals($expectedResults, $data);
	}

}
