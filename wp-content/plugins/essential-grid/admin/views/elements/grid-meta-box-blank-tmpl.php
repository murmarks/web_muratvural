<?php
$page_bg = get_post_meta( get_the_ID(), 'tp_eg_page_bg_color', true );
$blank = get_page_template_slug( get_the_ID() ) == Essential_Grid_Page_Template::TMPL;
?>
<ul class="esg_blank_tmpl_settings">
	<li class="esg_blank_tmpl_row">
		<label class="esg_wp_ppset" for="esg_blank_template"><?php esc_html_e('Blank Template','essential-grid'); ?></label>
		<div class="bulk-checkbox-wrapper blank-page-checkbox-wrapper esg-display-inline-block esg-margin-0">
			<input type="checkbox" <?php echo esc_attr( $blank ? 'checked' : '' );?> id="blank-page-switch-<?php echo esc_attr( get_the_ID() ); ?>" name="tp_eg_blank_page" value="<?php echo esc_attr( get_the_ID() ); ?>" /><label for="blank-page-switch-<?php echo esc_attr( get_the_ID() ); ?>"></label>
		</div>
	</li>
	<li class="esg_blank_tmpl_row">
		<div id="esg_page_bg_color_column" class="<?php echo esc_attr( $blank ? '' : 'esg-display-none' ); ?>">
			<label for="tp_eg_page_bg_color" class="esg_wp_ppset"><?php esc_html_e('Page Color', 'essential-grid');?></label>
			<input type="text" data-editing="<?php esc_attr_e('Background Color', 'essential-grid');?>" name="tp_eg_page_bg_color"
			       id="tp_eg_page_bg_color" class="my-color-field" value="<?php echo esc_attr( $page_bg ?? '#ffffff' ); ?>">
		</div>
		<div class="clear"></div>
	</li>
</ul>
