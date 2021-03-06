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
?>
<?php if (!$nowrapper) {?>
	<div class="row field-row diagnosis-selection">
		<div class="large-<?php echo $layoutColumns['label'];?> column<?php if (!$label) {?> hide<?php }?>">
			<label for="<?php echo "{$class}_{$field}";?>">Diagnosis:</label>
		</div>
		<div class="large-<?php echo $layoutColumns['field'];?> column end">
<?php } ?>
		<div class="dropdown-row">
			<?php echo !empty($options) ? CHtml::dropDownList("{$class}[$field]", '', $options, array('empty' => 'Select a commonly used diagnosis')) : ""?>
		</div>
		<div class="autocomplete-row">
			<?php
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name' => "{$class}[$field]",
					'id' => "{$class}_{$field}_0",
					'value'=>'',
					'source'=>"js:function(request, response) {
						$.ajax({
							'url': '" . Yii::app()->createUrl('/disorder/autocomplete') . "',
							'type':'GET',
							'data':{'term': request.term, 'code': '".$code."'},
							'success':function(data) {
								data = $.parseJSON(data);

								var result = [];

								for (var i = 0; i < data.length; i++) {
									var ok = true;
									$('#selected_diagnoses').children('input').map(function() {
										if ($(this).val() == data[i]['id']) {
											ok = false;
										}
									});
									if (ok) {
										result.push(data[i]);
									}
								}

								response(result);
							}
						});
					}",
					'options' => array(
							'minLength'=>'3',
							'select' => "js:function(event, ui) {
								".($callback ? $callback."(ui.item.id, ui.item.value);" : '')."
								$('#".$class."_".$field."_0').val('');
								$('#".$class."_".$field."').children('option').map(function() {
									if ($(this).val() == ui.item.id) {
										$(this).remove();
									}
								});
								return false;
							}",
					),
					'htmlOptions' => array(
							'placeholder' => $placeholder,
					),
			));
			?>
		</div>
<?php if (!$nowrapper) {?>
	</div>
</div>
<?php } ?>
<script type="text/javascript">
	<?php if ($callback) {?>
		$('#<?php echo $class?>_<?php echo $field?>').change(function() {
			if ($(this).children('option:selected').val()) {
				<?php echo $callback?>($(this).children('option:selected').val(), $(this).children('option:selected').text());
				$(this).children('option:selected').remove();
				$('#<?php echo $class?>_<?php echo $field?>').val('');
			}
		});
	<?php }?>
</script>
