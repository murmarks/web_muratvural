<?php $class_sub_info = isset($instance['sub_info']) && $instance['sub_info'] ? ' has_sub_info' : '';?>
<div class="thim-link-login thim-login-popup<?php echo esc_attr($class_sub_info);?>">
	<?php
	$layout               = isset( $instance['layout'] ) ? $instance['layout'] : 'base';
	$profile_text         = $logout_text = $login_text = $register_text = '';
	$registration_enabled = get_option( 'users_can_register' );

	if ( 'base' == $layout ) {
		$profile_text  = isset( $instance['text_profile'] ) ? $instance['text_profile'] : '';
		$logout_text   = isset( $instance['text_logout'] ) ? $instance['text_logout'] : '';
		$login_text    = isset( $instance['text_login'] ) ? $instance['text_login'] : '';
		$register_text = isset( $instance['text_register'] ) ? $instance['text_register'] : '';
	} else {
		$profile_text = '<i class="tk tk-users"></i>';
		$logout_text  = '<i class="tk tk-file-export"></i>';
		$login_text   = '<i class="tk tk-users"></i>';
	}

	// Login popup link output
	if ( is_user_logged_in() ) {
		if(isset($instance['sub_info']) && $instance['sub_info']){
			echo thim_sub_info_login_popup($profile_text, $logout_text);
		}else{
			if ( class_exists( 'LearnPress' ) && $profile_text ) {
				if ( thim_is_new_learnpress( '1.0' ) ) {
					echo '<a class="profile" href="' . esc_url( learn_press_user_profile_link() ) . '">' . ( $profile_text ) . '</a>';
				} else {
					echo '<a class="profile" href="' . esc_url( apply_filters( 'learn_press_instructor_profile_link', '#', get_current_user_id(), '' ) ) . '">' . ( $profile_text ) . '</a>';
				}
			}

			if ( $logout_text ) {
				?>
				<a class="logout"
				   href="<?php echo esc_url( wp_logout_url( apply_filters( 'thim_default_logout_redirect', thim_eduma_get_current_url() ) ) ); ?>"><?php echo( $logout_text ); ?></a>
				<?php
			}
		}
	} else {
		if ( $registration_enabled && 'base' == $layout ) {
			if ( $register_text ) {
				echo '<a class="register" href="' . esc_url( home_url( '/kayit' ) ) . '">' . ( $register_text ) . '</a>';
			}
		}

		if ( $login_text ) {
			echo '<a class="login" href="' . esc_url( home_url( '/giris' ) ) . '">' . ( $login_text ) . '</a>';
		}
	}
	?>
</div>
