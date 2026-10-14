<?php

class WPCode_Pixel_Admin_Page_Pixel extends WPCode_Admin_Page_Pixel {

	/**
	 * Register hook on admin init just for this page.
	 *
	 * @return void
	 */
	public function page_hooks() {
		$this->process_message();
		add_action( 'admin_init', array( $this, 'submit_listener' ) );
		add_filter( 'admin_body_class', array( $this, 'maybe_add_body_classes' ) );
	}

	/**
	 * If you need to page-specific scripts override this function.
	 * Hooked to 'admin_enqueue_scripts'.
	 *
	 * @return void
	 */
	public function page_scripts() {
		// Let's load the admin custom tracking scripts.
		$asset_file = WPCODE_PIXEL_PLUGIN_PATH . 'build/admin-custom-tracking.asset.php';

		// If the asset file doesn't exist, we can't enqueue the script.
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include_once $asset_file;

		wp_enqueue_script( 'wpcode-pixel-admin-js', plugins_url( 'build/admin-custom-tracking.js', WPCODE_PIXEL_FILE ), $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( 'wpcode-pixel-admin-css', plugins_url( 'build/admin-custom-tracking.css', WPCODE_PIXEL_FILE ), null, $asset['version'] );

		wp_localize_script(
			'wpcode-pixel-admin-js',
			'wpcodePixelAdmin',
			array(
				'remove_row' => __( 'Are you sure you want to remove this item?', 'wpcode-pixel' ),
			)
		);
	}

	/**
	 * Add view specific to the Pixel page.
	 * Remove this after adding this view in the main plugin.
	 *
	 * @return void
	 */
	public function setup_views() {
		parent::setup_views();
		$this->views['snapchat']       = __( 'Snapchat', 'wpcode-pixel' );
		$this->views['click_tracking'] = __( 'Click Tracking', 'wpcode-pixel' );
	}

	/**
	 * Process messages specific to this page.
	 *
	 * @return void
	 */
	public function process_message() {
		if ( empty( $_GET['message'] ) ) {
			return;
		}
		$messages = array(
			1 => __( 'Settings updated.', 'wpcode-pixel' ),
			2 => __( 'We encountered an error saving your settings.', 'wpcode-pixel' ),
		);
		$message  = absint( $_GET['message'] );
		// phpcs:enable WordPress.Security.NonceVerification

		if ( ! isset( $messages[ $message ] ) ) {
			return;
		}

		if ( $message > 1 ) {
			$this->set_error_message( $messages[ $message ] );
		} else {
			$this->set_success_message( $messages[ $message ] );
		}

	}

	/**
	 * The Conversion Pixels page output.
	 * In the actual addon page wrap elements in a form as the lite plugin
	 * never needs an actual form.
	 *
	 * @return void
	 */
	public function output_content() {
		if ( method_exists( $this, 'output_view_' . $this->view ) ) {
			$this->adblocker_notice();
			?>
			<form action="<?php echo esc_url( $this->get_page_action_url() ); ?>" method="post">
				<?php
				call_user_func( array( $this, 'output_view_' . $this->view ) );
				?>
			</form>
			<?php
			$this->adblocker_notice_js();
		}
	}

	/**
	 * Listen for POSTed data and process it.
	 *
	 * @return void
	 */
	public function submit_listener() {

		// Don't allow users who can't change snippet output to save settings.
		if ( ! current_user_can( 'wpcode_manage_conversion_pixels' ) || ! isset( $_POST['wpcode-pixel-nonce'] ) ) {
			return;
		}

		if ( wp_verify_nonce( sanitize_key( $_POST['wpcode-pixel-nonce'] ), 'wpcode-save-facebook-pixel-data' ) ) {
			$facebook_pixel_id        = isset( $_POST['facebook_pixel_id'] ) ? sanitize_text_field( $_POST['facebook_pixel_id'] ) : '';
			$facebook_pixel_api_token = isset( $_POST['facebook_pixel_api_token'] ) ? sanitize_text_field( $_POST['facebook_pixel_api_token'] ) : '';
			$facebook_pixel_events    = isset( $_POST['facebook_pixel_events'] ) ? array_map( 'boolval', $_POST['facebook_pixel_events'] ) : array();

			wpcode()->settings->update_option( 'facebook_pixel_id', $facebook_pixel_id );
			wpcode()->settings->update_option( 'facebook_pixel_api_token', $facebook_pixel_api_token );
			wpcode()->settings->update_option( 'facebook_pixel_events', $facebook_pixel_events );
		}

		if ( wp_verify_nonce( sanitize_key( $_POST['wpcode-pixel-nonce'] ), 'wpcode-save-google-pixel-data' ) ) {
			$google_analytics_id = isset( $_POST['google_analytics_id'] ) ? sanitize_text_field( $_POST['google_analytics_id'] ) : '';
			$google_ads_id       = isset( $_POST['google_ads_id'] ) ? sanitize_text_field( $_POST['google_ads_id'] ) : '';
			$google_ads_label    = isset( $_POST['google_ads_label'] ) ? sanitize_text_field( $_POST['google_ads_label'] ) : '';
			$google_pixel_events = isset( $_POST['google_pixel_events'] ) ? array_map( 'boolval', $_POST['google_pixel_events'] ) : array();

			wpcode()->settings->update_option( 'google_analytics_id', $google_analytics_id );
			wpcode()->settings->update_option( 'google_ads_id', $google_ads_id );
			wpcode()->settings->update_option( 'google_ads_label', $google_ads_label );
			wpcode()->settings->update_option( 'google_pixel_events', $google_pixel_events );
		}

		if ( wp_verify_nonce( sanitize_key( $_POST['wpcode-pixel-nonce'] ), 'wpcode-save-pinterest-pixel-data' ) ) {
			$pinterest_id               = isset( $_POST['pinterest_id'] ) ? sanitize_text_field( $_POST['pinterest_id'] ) : '';
			$pinterest_ad_account_id    = isset( $_POST['pinterest_ad_account_id'] ) ? sanitize_text_field( $_POST['pinterest_ad_account_id'] ) : array();
			$pinterest_conversion_token = isset( $_POST['pinterest_conversion_token'] ) ? sanitize_text_field( $_POST['pinterest_conversion_token'] ) : array();
			$pinterest_pixel_events     = isset( $_POST['pinterest_pixel_events'] ) ? array_map( 'boolval', $_POST['pinterest_pixel_events'] ) : array();

			wpcode()->settings->update_option( 'pinterest_id', $pinterest_id );
			wpcode()->settings->update_option( 'pinterest_ad_account_id', $pinterest_ad_account_id );
			wpcode()->settings->update_option( 'pinterest_conversion_token', $pinterest_conversion_token );
			wpcode()->settings->update_option( 'pinterest_pixel_events', $pinterest_pixel_events );
		}

		if ( wp_verify_nonce( sanitize_key( $_POST['wpcode-pixel-nonce'] ), 'wpcode-save-tiktok-pixel-data' ) ) {
			$tiktok_pixel_id     = isset( $_POST['tiktok_pixel_id'] ) ? sanitize_text_field( $_POST['tiktok_pixel_id'] ) : '';
			$tiktok_access_token = isset( $_POST['tiktok_access_token'] ) ? sanitize_text_field( $_POST['tiktok_access_token'] ) : '';
			$tiktok_pixel_events = isset( $_POST['tiktok_pixel_events'] ) ? array_map( 'boolval', $_POST['tiktok_pixel_events'] ) : array();

			wpcode()->settings->update_option( 'tiktok_pixel_id', $tiktok_pixel_id );
			wpcode()->settings->update_option( 'tiktok_access_token', $tiktok_access_token );
			wpcode()->settings->update_option( 'tiktok_pixel_events', $tiktok_pixel_events );
		}

		if ( wp_verify_nonce( sanitize_key( $_POST['wpcode-pixel-nonce'] ), 'wpcode-save-snapchat-pixel-data' ) ) {
			$tiktok_pixel_id     = isset( $_POST['snapchat_pixel_id'] ) ? sanitize_text_field( $_POST['snapchat_pixel_id'] ) : '';
			$tiktok_access_token = isset( $_POST['snapchat_pixel_api_token'] ) ? sanitize_text_field( $_POST['snapchat_pixel_api_token'] ) : '';
			$tiktok_pixel_events = isset( $_POST['snapchat_pixel_events'] ) ? array_map( 'boolval', $_POST['snapchat_pixel_events'] ) : array();

			wpcode()->settings->update_option( 'snapchat_pixel_id', $tiktok_pixel_id );
			wpcode()->settings->update_option( 'snapchat_pixel_api_token', $tiktok_access_token );
			wpcode()->settings->update_option( 'snapchat_pixel_events', $tiktok_pixel_events );
		}

		if ( wp_verify_nonce( sanitize_key( $_POST['wpcode-pixel-nonce'] ), 'wpcode-save-click-pixel-data' ) ) {
			if ( ! isset( $_POST['selector'] ) || ! is_array( $_POST['selector'] ) ) {
				return;
			}
			$possible_pixels = array();
			foreach ( wpcode_pixel()->auto_insert->types as $type ) {
				$possible_pixels[] = $type->type;
			}

			$css_selectors = array_map( 'sanitize_text_field', wp_unslash( $_POST['selector'] ) );
			$event_names   = isset( $_POST['label'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['label'] ) ) : array();
			$event_values  = isset( $_POST['value'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['value'] ) ) : array();
			$pixels_data   = isset( $_POST['pixels'] ) ? wp_unslash( $_POST['pixels'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$clicks_data   = array();
			foreach ( $css_selectors as $index => $css_selector ) {
				if ( empty( $css_selector ) ) {
					continue;
				}
				$event_name   = isset( $event_names[ $index ] ) ? $event_names[ $index ] : '';
				$event_value  = isset( $event_values[ $index ] ) ? floatval( $event_values[ $index ] ) : 0;
				$pixel_data   = isset( $pixels_data[ $index ] ) ? $pixels_data[ $index ] : array();
				$pixel_data   = array_map( 'sanitize_text_field', $pixel_data ); // We are sanitizing the pixels data here.
				$pixels       = array();
				$event_labels = array();
				$pixel_values = array();
				foreach ( $possible_pixels as $pixel ) {
					if ( ! empty( $pixel_data[ $pixel ] ) ) {
						$pixels[] = $pixel;
					}
					if ( ! empty( $pixel_data[ $pixel . '_event_name' ] ) ) {
						$event_labels[ $pixel ] = $pixel_data[ $pixel . '_event_name' ];
					}
				}

				$click_data    = array(
					'selector'  => $css_selector,
					'label'     => $event_name,
					'value'     => $event_value,
					'platforms' => $pixels,
					'labels'    => $event_labels,
					'values'    => $pixel_values,
				);
				$clicks_data[] = $click_data;
			}

			wpcode()->settings->update_option( 'clicks_data', $clicks_data );
		}

		if ( function_exists( 'wpcode_clear_all_plugins_page_cache' ) ) {
			wpcode_clear_all_plugins_page_cache( 'pixels' );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'message' => 1,
				),
				$this->get_page_action_url()
			)
		);
		exit;
	}

	/**
	 * Override the main class get option method to actually load data in the addon.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_option( $key, $default = false ) {
		return wpcode()->settings->get_option( $key, $default );
	}

	/**
	 * Add a simple js check to see if the user is using an adblocker and if so
	 * display a notice that they might have trouble confirming the pixels are
	 * loaded correctly and should whitelist their site.
	 *
	 * @return void
	 */
	public function adblocker_notice_js() {
		$adex_src = WPCODE_PIXEL_PLUGIN_URL . 'assets/adex.js';
		?>
		<script src="<?php echo esc_url( $adex_src ); ?>" type="text/javascript"></script>
		<script type="text/javascript">
			const adblocker_notice = document.getElementById( 'wpcode-adblocker-notice' );
			if ( document.getElementById( 'MepWv8eL' ) ) {

			} else {
				adblocker_notice.style = 'display:block';
			}
		</script>
		<?php
	}

	/**
	 * Display a simple notice if an adblocker is detected.
	 *
	 * @return void
	 */
	public function adblocker_notice() {
		?>
		<div class="wpcode-alert wpcode-alert-warning" id="wpcode-adblocker-notice" style="display:none">
			<p><?php esc_html_e( 'It looks like you are using an ad blocker. If you are having trouble confirming your pixels are loaded correctly, please whitelist this site or disable the adblocker.', 'wpcode-pixel' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Whether we have a supported eCommerce plugin installed.
	 *
	 * @return array
	 */
	public function ecommerce_available() {
		$available_providers = array();

		foreach ( wpcode_pixel()->auto_insert->providers as $provider ) {
			$available_providers[] = $provider->get_name();
		}

		return $available_providers;
	}

	/**
	 * Let's see if the enabled providers have any unsupported events.
	 *
	 * @param string $class The admin body class.
	 *
	 * @return string
	 */
	public function maybe_add_body_classes( $class ) {

		$unsupported_events = array();
		$first              = true;
		foreach ( wpcode_pixel()->auto_insert->providers as $provider ) {
			if ( empty( $unsupported_events ) && true === $first ) {
				$unsupported_events = array_merge( $unsupported_events, $provider->unsupported_events );
			} else {
				// Keep just the overlap of the unsupported events.
				$unsupported_events = array_intersect( $unsupported_events, $provider->unsupported_events );
			}
			$first = false;
		}
		foreach ( $unsupported_events as $event ) {
			$class .= ' wpcode-pixel-unsupported-' . $event;
		}

		return $class;
	}

	/**
	 * Page for Snapchat pixel settings.
	 *
	 * @return void
	 */
	public function output_view_snapchat() {
		?>
		<h2><?php esc_html_e( 'Snapchat Pixel', 'wpcode-pixel' ); ?></h2>
		<?php
		$this->metabox_row(
			__( 'Snap Pixel ID', 'wpcode-pixel' ),
			$this->get_input_text(
				'snapchat_pixel_id',
				$this->get_option( 'snapchat_pixel_id', '' ),
				sprintf(
				// translators: %1$s and %2$s are the opening and closing anchor tags.
					__( 'You can find your Snapchat Pixel ID in the Snapchat Ads Manager. %1$sRead our step by step directions%2$s. ', 'wpcode-pixel' ),
					'<a target="_blank" href="' . wpcode_utm_url( 'https://wpcode.com/docs/how-to-find-your-snapchat-pixel-id-and-conversions-api-token/', 'conversion-pixels', 'snapchat', 'pixel' ) . '">',
					'</a>'
				),
				true
			),
			'snapchat_pixel_id'
		);
		$this->metabox_row(
			__( 'Conversions API Token', 'wpcode-pixel' ),
			$this->get_input_text(
				'snapchat_pixel_api_token',
				$this->get_option( 'snapchat_pixel_api_token', '' ),
				__( 'The Conversions API token allows you to send API events that are more reliable and can improve audience targeting.', 'wpcode-pixel' ),
				true
			),
			'snapchat_pixel_api_token'
		);
		$this->metabox_row(
			__( 'Snapchat Pixel Events', 'wpcode-pixel' ),
			$this->get_checkbox_inputs(
				array(
					array(
						'label'       => __( 'Pave View Event', 'wpcode-pixel' ),
						'name'        => 'page_view',
						'description' => __( 'Enable the PAGE_VIEW event to track and record page visits on all the pages of your website using the Snapchat Pixel.', 'wpcode-pixel' ),
						'ecommerce'   => false,
					),
				),
				'snapchat_pixel_events'
			)
		);
		$this->metabox_row(
			__( 'eCommerce Events Tracking', 'wpcode-pixel' ),
			$this->get_ecommerce_events_input() . $this->get_checkbox_inputs( $this->get_snap_pixel_events_inputs(), 'snapchat_pixel_events' )
		);
		wp_nonce_field( 'wpcode-save-snapchat-pixel-data', 'wpcode-pixel-nonce' );
		?>
		<button type="submit" class="wpcode-button">
			<?php esc_html_e( 'Save Changes', 'wpcode-pixel' ); ?>
		</button>
		<?php
	}

	/**
	 * Event options checkboxes for the Snapchat Pixel.
	 *
	 * @return array[]
	 */
	public function get_snap_pixel_events_inputs() {
		return array(
			array(
				'label'       => __( 'View Content Event', 'wpcode-pixel' ),
				'name'        => 'view_content',
				'description' => __( 'Turn on the "VIEW_CONTENT" event to track views of product pages on your website.', 'wpcode-pixel' ),
				'ecommerce'   => true,
				'css_class'   => 'view-content',
			),
			array(
				'label'       => __( 'Add to Cart Event', 'wpcode-pixel' ),
				'name'        => 'add_to_cart',
				'description' => __( 'Turn on the "ADD_CART" event to track when items are added to a shopping cart on your website.', 'wpcode-pixel' ),
				'ecommerce'   => true,
				'css_class'   => 'add-to-cart',
			),
			array(
				'label'       => __( 'Start Checkout Event', 'wpcode-pixel' ),
				'name'        => 'begin_checkout',
				'description' => __( 'Turn on the "START_CHECKOUT" event to track when a user reaches the checkout page on your website.', 'wpcode-pixel' ),
				'ecommerce'   => true,
				'css_class'   => 'begin-checkout',
			),
			array(
				'label'       => __( 'Purchase Event', 'wpcode-pixel' ),
				'name'        => 'purchase',
				'description' => __( 'Turn on the "PURCHASE" event to track successful purchases on your website.', 'wpcode-pixel' ),
				'ecommerce'   => true,
				'css_class'   => 'purchase',
			),
		);
	}

	/**
	 * This is the page content for the Custom Events page.
	 *
	 * @return void
	 */
	public function output_view_click_tracking() {
		$clicks_data = $this->get_option(
			'clicks_data',
			array(
				array(
					'selector'  => '',
					'label'     => '',
					'value'     => '',
					'platforms' => array(),
					'labels'    => array(),
				),
			)
		);
		?>
		<h2><?php esc_html_e( 'Custom Click Tracking', 'wpcode-pixel' ); ?></h2>
		<p>
			<?php
			printf(
			/* Translators: %1$s is the opening link tag, %2$s is the closing link tag */
				esc_html__( 'Use this section to add custom click events to your site. You can add as many as you like. Each event can have multiple pixels and each pixel can have a custom event name and value. Read more about how to configure these settings in %1$sthis article%2$s', 'wpcode-pixel' ),
				'<a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/docs/conversion-pixels-custom-click-tracking/' ) ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			?>
		</p>
		<div id="wpcode-settings-repeater" class="wpcode-settings-repeater-group">
			<?php
			foreach ( $clicks_data as $index => $click_data ) {
				$this->click_event_row( $index, $click_data['selector'], $click_data['label'], $click_data['value'], $click_data['platforms'], $click_data['labels'] );
			}
			?>
		</div>
		<button type="button" class="wpcode-button wpcode-button-secondary" id="wpcode-add-click-event">
			<?php esc_html_e( 'Add New Click Event', 'wpcode-pixel' ); ?>
		</button>
		<?php
		wp_nonce_field( 'wpcode-save-click-pixel-data', 'wpcode-pixel-nonce' );
		?>
		<button type="submit" class="wpcode-button">
			<?php esc_html_e( 'Save Changes', 'wpcode-pixel' ); ?>
		</button>
		<script id="wpcode-click-event-row-template" type="text/template">
			<?php $this->click_event_row( '{index}' ); ?>
		</script>
		<?php
	}

	/**
	 * Output the HTML for a single click event row.
	 *
	 * @param string $index
	 * @param string $selector
	 * @param string $label
	 * @param string $value
	 * @param array  $platforms
	 *
	 * @return void
	 */
	public function click_event_row( $index, $selector = '', $label = '', $value = '', $platforms = array(), $labels = array() ) {
		?>
		<div class="wpcode-settings-repeater-item" data-index="<?php echo esc_attr( $index ); ?>">
			<?php
			$this->metabox_row(
				__( 'CSS Selector', 'wpcode-pixel' ),
				$this->get_input_text(
					'selector[' . $index . ']',
					$selector,
					sprintf(
					// Translators: %1$s is an opening anchor tag, %2$s is a closing anchor tag.
						esc_html__( 'Define the HTML element that triggers the event upon clicking (button, link, etc). Input the appropriate CSS selector here. %1$sLearn more%2$s', 'wpcode-pixel' ),
						'<a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/docs/finding-css-selector/' ) ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					true
				)
			);
			$this->metabox_row(
				__( 'Event Name', 'wpcode-pixel' ),
				$this->get_input_text(
					'label[' . $index . ']',
					$label,
					__( 'Assign a unique identifier to your event for easy recognition and categorization. ', 'wpcode-pixel' )
				)
			);
			$this->metabox_row(
				__( 'Event Value', 'wpcode-pixel' ),
				$this->get_input_text(
					'value[' . $index . ']',
					$value,
					__( 'Input a numerical value for your event. This helps in quantifying user interactions for your tracking needs. ', 'wpcode-pixel' )
				)
			);
			?>
			<div class="wpcode-pixels-chooser-row">
				<?php
				$this->metabox_row(
					__( 'Pixels', 'wpcode-pixel' ),
					$this->get_pixels_inputs( $platforms, $index, $labels )
				);
				?>
			</div>
			<div class="wpcode-settings-repeater-item-actions">
				<button type="button" class="wpcode-button wpcode-button-secondary wpcode-remove-repeater-item">
					<?php esc_html_e( 'Remove Click Event', 'wpcode-pixel' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Built the inputs for the pixel options.
	 *
	 * @param array $values The values.
	 * @param int   $index The index of the current repeater being output.
	 *
	 * @return string
	 */
	public function get_pixels_inputs( $values, $index, $labels = array() ) {
		$available_pixels = wpcode_pixel()->auto_insert->types;

		$markup = '<p>';
		$markup .= __( 'Choose which pixels to enable for this event. ', 'wpcode-pixel' );
		$markup .= '</p>';
		$markup .= '<p>';
		$markup .= __( 'You can choose a standard event for each pixel to override the custom event label set above.', 'wpcode-pixel' );
		$markup .= '</p>';
		/**
		 *  The pixel type.
		 *
		 * @var WPCode_Pixel_Auto_Insert_Type $pixel
		 */
		foreach ( $available_pixels as $pixel ) {
			$checked = in_array( $pixel->type, $values, true );

			$markup .= '<div class="wpcode-checkbox-row">';
			$markup .= $this->get_checkbox_toggle(
				$checked,
				'pixels[' . $index . '][' . $pixel->type . ']',
				'',
				1,
				$pixel->name
			);

			$standard_events      = $pixel->get_standard_events();
			$standard_event_value = isset( $labels[ $pixel->type ] ) ? $labels[ $pixel->type ] : '';
			if ( ! empty( $standard_events ) ) {
				// Let's add a select for the standard events.
				$markup .= '<div class="wpcode-pixel-event-select">';

				$standard_events = array_keys( $standard_events );
				// Use the standard events array keys as both keys and values.
				$standard_events = array_combine( $standard_events, $standard_events );
				// Prepend an empty option.
				$standard_events = array_merge( array( '' => __( 'Choose Standard Event', 'wpcode-pixel' ) ), $standard_events );

				$markup .= $this->get_select(
					'pixels[' . $index . '][' . $pixel->type . '_event_name]',
					$standard_events,
					array( $standard_event_value ),
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					''
				);
			} else {
				// No standard event let's provide a text input.
				$markup .= '<div class="wpcode-pixel-event-text">';
				$markup .= $this->get_input_text(
					'pixels[' . $index . '][' . $pixel->type . '_event_name]',
					$standard_event_value,
					'',
					'',
					__( 'Conversion Label', 'wpcode-pixel' )
				);
			}
			$markup .= '</div>';
			$markup .= '</div>';
		}

		return $markup;
	}

	/**
	 * @param $name
	 * @param $options
	 * @param $values
	 * @param $class
	 * @param $id
	 * @param $multiple
	 * @param $size
	 * @param $disabled
	 * @param $required
	 * @param $readonly
	 * @param $placeholder
	 * @param $label
	 *
	 * @return string
	 */
	public function get_select( $name, $options, $values, $class = '', $id = '', $multiple = false, $size = '', $disabled = false, $required = false, $readonly = false, $placeholder = '', $label = '' ) {
		$markup = '';
		if ( ! empty( $label ) ) {
			$markup .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		}
		$markup .= '<select name="' . esc_attr( $name ) . '" class="' . esc_attr( $class ) . '" id="' . esc_attr( $id ) . '" ' . ( $multiple ? 'multiple' : '' ) . ' ' . ( ! empty( $size ) ? 'size="' . esc_attr( $size ) . '"' : '' ) . ' ' . ( $disabled ? 'disabled' : '' ) . ' ' . ( $required ? 'required' : '' ) . ' ' . ( $readonly ? 'readonly' : '' ) . '>';
		if ( ! empty( $placeholder ) ) {
			$markup .= '<option value="">' . esc_html( $placeholder ) . '</option>';
		}
		foreach ( $options as $key => $option ) {
			$markup .= '<option value="' . esc_attr( $key ) . '" ' . ( in_array( $key, $values, true ) ? 'selected' : '' ) . '>' . esc_html( $option ) . '</option>';
		}
		$markup .= '</select>';

		return $markup;
	}

	/**
	 * Get a text field markup.
	 *
	 * @param string $id The id of the text field.
	 * @param string $value The value of the text field.
	 * @param string $description The description of the text field.
	 * @param bool   $wide Whether the text field should be wide.
	 * @param string $placeholder The placeholder of the text field.
	 *
	 * @return string
	 */
	public function get_input_text( $id, $value = '', $description = '', $wide = false, $placeholder = '' ) {
		$class = 'wpcode-regular-text';
		if ( $wide ) {
			$class .= ' wpcode-wide-text';
		}
		$markup = '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" class="' . esc_attr( $class ) . '" autocomplete="off" ' . ( ! empty( $placeholder ) ? 'placeholder="' . esc_attr( $placeholder ) . '"' : '' ) . '>';
		if ( ! empty( $description ) ) {
			$markup .= '<p>' . wp_kses_post( $description ) . '</p>';
		}

		return $markup;
	}
}
