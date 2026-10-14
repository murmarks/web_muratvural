<?php

namespace WPFormsUserRegistration\Templates;

use WPForms_Template;

/**
 * User reset form template.
 *
 * @since 2.0.0
 */
class Reset extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'User Password Reset Form', 'wpforms-user-registration' );
		$this->slug        = 'user_reset';
		$this->description = esc_html__( 'Allow your users to easily reset their password.', 'wpforms-user-registration' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = [
			'field_id' => '3',
			'fields'   => [
				'1' => [
					'id'       => '1',
					'type'     => 'text',
					'label'    => esc_html__( 'Username or Email', 'wpforms-user-registration' ),
					'required' => '1',
					'size'     => 'medium',
					'meta'     => [
						'nickname' => 'login',
						'delete'   => false,
					],
				],
				'2' => [
					'id'           => '2',
					'type'         => 'password',
					'label'        => esc_html__( 'Password', 'wpforms-user-registration' ),
					'required'     => '1',
					'confirmation' => '1',
					'size'         => 'medium',
					'meta'         => [
						'nickname' => 'password',
						'delete'   => false,
					],
				],
			],
			'settings' => [
				'notification_enable' => '0',
				'disable_entries'     => '1',
				'antispam_v3'         => '1',
				'ajax_submit'         => '1',
				'user_reset_hide'     => '1',
			],
			'meta'     => [
				'template' => $this->slug,
			],
		];
	}
}
