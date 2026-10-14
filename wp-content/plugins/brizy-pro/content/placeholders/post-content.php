<?php

class BrizyPro_Content_Placeholders_PostContent extends BrizyPro_Content_Placeholders_SimplePostAware {

	/**
	 * @return string|callable
	 */
	protected $value;

	/**
	 * BrizyPro_Content_Placeholders_PostContent constructor.
	 *
	 * @param $label
	 * @param $placeholder
	 * @param string $display
	 */
	public function __construct( $label, $placeholder, $group = null, $display = Brizy_Content_Placeholders_Abstract::DISPLAY_INLINE ) {
		parent::__construct( $label, $placeholder, $this->getTheContentCallback(), $group, $display );
	}

	private function getTheContentCallback() {
		return function ( $context, $placeholder, $entity ) {

			$usesEditor = false;
			$entity     = $entity ?: $context->getWpPost();
			try {
				if ( Brizy_Editor_Entity::isBrizyEnabled( $entity ) ) {
					$usesEditor = true;
				}
				$post = Brizy_Editor_Post::get( $entity );
			} catch ( Exception $e ) {
			}

			$content = $usesEditor ? $post->get_compiled_page()->get_body() : $entity->post_content;

			if ( ! has_blocks( $content ) ) {
				$content = wpautop( $content );
			}

			if ( $context->getLevel() < 5 ) {
				$context->setLevel( $context->getLevel() + 1 );
				// try to see if there are still placeholders that were not replaces
				$content = apply_filters( 'brizy_content',
					$content,
					Brizy_Editor_Project::get(),
					$entity
				);
			}

			return wp_doing_ajax() ? apply_filters( 'the_content', $content ) : $content;
		};
	}
}
