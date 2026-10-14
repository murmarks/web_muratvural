<div class="license-form">
    <form action="<?php echo $action; ?>" method="post">
	    <?php echo $nonce; ?>
        <table class="form-table">
            <tr>
                <th>
                    <label for="licensekey"><?php _e( 'Your License Key', 'brizy-pro' ); ?>:</label>
                </th>
                <td>
	                <?php if ( $license ): ?>
                        <input type="text" value="<?php echo $license; ?>" class="regular-text" id="licensekey" readonly/>
	                <?php else: ?>
                        <input name="key" type="text" value="" class="regular-text" id="licensekey"/>
	                <?php endif; ?>
                    <input type="hidden" value="<?php echo $license_form_action; ?>" name="license_form_action"/>
                    <input type="submit" name="brizy-license-submit" id="submit" class="button button-primary" value="<?php echo $submit_label; ?>" style="vertical-align:baseline;">
                </td>
            </tr>
        </table>
        <?php if ( ! $license ) : ?>
            <p style="font-style: italic">
                <?php echo sprintf("If you don't have a PRO license, get one from %sour website%s", "<a href='" . Brizy_Config::UPGRADE_TO_PRO_URL . "' target='_blank' style='text-decoration: none;'><strong>", '</strong></a>'); ?>
            </p>
        <?php endif; ?>
    </form>
</div>