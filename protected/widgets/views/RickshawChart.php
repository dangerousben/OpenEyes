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
?>
<script type="text/javascript">
	$(document).ready(function() {
		var series = <?= CJavaScript::encode(array_values($this->series)); ?>;

		var palette = new Rickshaw.Color.Palette();

		var scales = {};
		<?php foreach ($this->y_axes as $axis_name => $y_axis): ?>
		scales[<?= CJavaScript::encode($axis_name); ?>] = d3.scale.linear().domain([<?= $y_axis['min'] ?>, <?= $y_axis['max'] ?>]);
		<?php endforeach; ?>

		for (var i = 0; i < series.length; i++) {
			series[i].color = palette.color();
			if (series[i].y_axis) {
				series[i].scale = scales[series[i].y_axis];
			}
		}

		var graph = new Rickshaw.Graph({
			element: document.querySelector(<?= CJavascript::encode($this->chart_selector); ?>),
			series: series,
			renderer: 'line',
		});

		<?php if (isset($this->time_axis)): ?>
		new Rickshaw.Graph.Axis.Time($.extend({graph: graph}, <?= CJavaScript::encode($this->time_axis); ?>));
		<?php endif; ?>

		<?php foreach ($this->y_axes as $axis_name => $y_axis): ?>
		new Rickshaw.Graph.Axis.Y.Scaled($.extend({graph: graph, scale: scales[<?= CJavaScript::encode($axis_name); ?>]}, <?= CJavaScript::encode($y_axis); ?>));
		<?php endforeach; ?>

		var legend = new Rickshaw.Graph.Legend({
			'graph': graph,
			'element': document.querySelector(<?= CJavaScript::encode($this->legend_selector); ?>),
		});

		graph.render();
	});
</script>
