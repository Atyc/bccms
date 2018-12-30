<div class="cms_input_container <?= !empty($params['groups']) ? ' cms_input_container_groups ' : '' ?>" <?= !empty($params['groups']) ? ' data-groups="'.implode(',', $params['groups']).'" ' : '' ?>>

	<div class="cms_input cms_input_textarea <?php print(!empty($extra_class) ? $extra_class : ''); ?> <?= !empty($mandatory_class) ? $mandatory_class : '' ?>">
		
		<label><?php print($label); ?></label>
		
		<?php _panel('cms_help', ['help' => !empty($help) ? $help : '', ]); ?>
		
		<?php if (!empty($translate) && !empty($GLOBALS['language'])): ?>
			<div class="cms_translate_icon" style="background-image: url('<?= $GLOBALS['config']['base_url'] ?>modules/cms/img/cms_translate.png'); "></div>
		<?php endif ?>

		<textarea name="<?php print($name); ?>" class="<?= $name ?> 
				<?php print(!empty($tinymce) ? ' admin_tinymce ' : ''); ?>
				<?php print(!empty($max_chars_class) ? $max_chars_class : ''); ?>
				<?php print(!empty($meta_class) ? $meta_class : ''); ?>
				" <?php print(!empty($extra_data) ? $extra_data : ''); ?>><?php print(!empty($value) ? $value : ''); ?></textarea>
	
	</div>

</div>