<?php
/**
 * (C) OpenEyes Foundation, 2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class RickshawChart extends CWidget
{
	public $chart_selector;
	public $legend_selector;

	protected $series = array();
	protected $time_axis;
	protected $y_axes = array();

	/**
	 * @param string $series_name
	 * @param float $x
	 * @param float $y
	 * @param array $config
	 * @return RickshawChart
	 */
	public function addPoint($series_name, $x, $y, $label = null)
	{
		$this->initSeries($series_name);

		$this->series[$series_name]['data'][] = array("x" => (float)$x, "y" => (float)$y);

		return $this;
	}

	/**
	 * @param array $config
	 * @return RickshawChart
	 */
	public function addTimeAxis(array $config = array())
	{
		$this->time_axis = $config;
		return $this;
	}

	/**
	 * @param array $config
	 * @return RickshawChart
	 */
	public function addYAxis($axis_name, array $config = array())
	{
		if (isset($config['ticks']) && !isset($config['tickValues'])) {
			$ticks = $config['ticks'];
			$config['ticks'] = array_values($ticks);
			$config['tickValues'] = array_keys($ticks);
		}

		$this->y_axes[$axis_name] = $config;
		return $this;
	}

	public function configureSeries($series_name, array $config)
	{
		$this->initSeries($series_name);
		$this->series[$series_name] += $config;

		return $this;
	}

	public function run()
	{
		foreach ($this->series as &$series) {
			usort($series['data'], function ($a, $b) { return ($a['x'] < $b['x']) ? -1 : 1; });
		}

		foreach ($this->y_axes as $axis_name => &$y_axis) {
			if (!isset($y_axis['min']) || !isset($y_axis['max'])) {
				if (isset($y_axis['tickValues'])) {
					$min = min($y_axis['tickValues']);
					$max = max($y_axis['tickValues']);
				} else {
					$min = null;
					$max = null;
					foreach ($this->series as $series) {
						if ($series['y_axis'] != $axis_name) continue;
						foreach ($series['data'] as $point) {
							if (is_null($min) || $point['y'] < $min) $min = $point['y'];
							if (is_null($max) || $point['y'] > $max) $max = $point['y'];
						}
					}
				}
				if (!isset($y_axis['min'])) $y_axis['min'] = $min;
				if (!isset($y_axis['max'])) $y_axis['max'] = $max;
			}
		}

		Yii::app()->clientScript->registerPackage('rickshaw');
		$this->render('RickshawChart');
	}

	protected function initSeries($series_name)
	{
		if (!isset($this->series[$series_name])) {
			$this->series[$series_name] = array(
				'name' => $series_name,
				'data' => array(),
			);
		}
	}
}
