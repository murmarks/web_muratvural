<?php

use BrizyPlaceholders\ContentPlaceholder;


class BrizyPro_Content_Providers_Wp extends Brizy_Content_Providers_AbstractProvider
{
    public function __construct()
    {
        $this->getTextPlaceholders();
        $this->getMediaPlaceholders();
        $this->getLinkPlaceholders();

        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_PostLoop('Post Loop', 'brizy_dc_post_loop'));
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_PostLoopPagination());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_PostLoopTags());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_PostTerms());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_PostTags());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_Breadcrumbs('Breadcrumbs', 'editor_breadcrumbs'));
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_Comments('Comments', 'editor_comments'));
        $this->registerPlaceholder($this->postNavigation(0));
        $this->registerPlaceholder(new Brizy_Content_Placeholders_Simple('', 'editor_is_user_logged_in', function () {
            return is_user_logged_in() ? 'true' : 'false';
        }));
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MenuItemActive('', 'editor_menu_active_item'));

        // this is old version of menu.. we keep it for back compatibility
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_NavItem());

        // ne version of menu
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MenuLoop());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MenuItemProperty());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MegaMenuItem());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MegaMenuValue());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MenuItemIcon());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MenuItemIconValue());
        $this->registerPlaceholder(new BrizyPro_Content_Placeholders_MenuLoopSubmenu());


        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                '',
                'brizy_customer_email',
                function (Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder) {
                    return $this->getUserField($context, $contentPlaceholder, 'email');
                }
            )
        );

        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                '',
                'brizy_customer_fname',
                function (Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder) {
                    return $this->getUserField($context, $contentPlaceholder, 'user_firstname');
                }
            )
        );

        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                '',
                'brizy_customer_lname',
                function (Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder) {
                    return $this->getUserField($context, $contentPlaceholder, 'user_lastname');
                }
            )
        );

        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                '',
                'brizy_customer_username',
                function (Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder) {
                    return $this->getUserField($context, $contentPlaceholder, 'user_login');
                }
            )
        );

        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                '',
                'brizy_customer_roles',
                function (Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder) {

                    if (!$attrContext = $contentPlaceholder->getAttribute('context')) {
                        $attrContext = 'auto';
                    }

                    $userId = 'profile' === $attrContext ? $context->getAuthor() : get_current_user_id();

                    if (!$userId) {
                        return '';
                    }

                    $user = get_userdata($userId);

                    return implode(', ', $user->roles);
                }
            )
        );
    }

    /**
     *
     * @return array
     */
    private function getTextPlaceholders()
    {
        $this->registerPlaceholder(
            new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Post Title', 'brizy-pro'),
                'brizy_dc_post_title',
                function ($context) {
                    if ($context->getWpPost()->post_type == Brizy_Admin_Templates::CP_TEMPLATE) {
                        return $this->getArchiveTitle();
                    }

                    if ($context->getEntity()) {
                        return get_the_title($context->getEntity()->ID);
                    }

                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_PostContent(
                __('Post Content', 'brizy-pro'),
                'brizy_dc_post_content',
                self::CONFIG_KEY_TEXT,
                Brizy_Content_Placeholders_Abstract::DISPLAY_BLOCK
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_Excerpt(
                __('Post Excerpt', 'brizy-pro'),
                'brizy_dc_post_excerpt',
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Post Date', 'brizy-pro'),
                'brizy_dc_post_date',
                function ($context) {
                    return get_the_date('', $context->getEntity());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Post Time', 'brizy-pro'),
                'brizy_dc_post_time',
                function ($context) {
                    return get_the_time('', $context->getEntity());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Post ID', 'brizy-pro'),
                'brizy_dc_post_id',
                function ($context) {
                    return $context->getEntity()->ID;
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Post Comments Count', 'brizy-pro'),
                'brizy_dc_comments_count',
                function ($context) {
                    return get_comments_number($context->getEntity());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->getTextPlaceholderTerms(self::CONFIG_KEY_TEXT);

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Author Name', 'brizy-pro'),
                'brizy_dc_post_author_name',
                function ($context) {

                    if (get_the_author_meta('user_firstname', $context->getAuthor()) || get_the_author_meta(
                            'user_lastname',
                            $context->getAuthor()
                        )) {
                        return trim(
                            get_the_author_meta('user_firstname', $context->getAuthor()).' '.get_the_author_meta(
                                'user_lastname',
                                $context->getAuthor()
                            )
                        );
                    } else {
                        return trim(get_the_author_meta('display_name', $context->getAuthor()));
                    }
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Author Bio', 'brizy-pro'),
                'brizy_dc_post_author_description',
                function ($context) {
                    return get_the_author_meta('description', $context->getAuthor());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Author Email', 'brizy-pro'),
                'brizy_dc_post_author_email',
                function ($context) {
                    return get_the_author_meta('email', $context->getAuthor());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Author Website', 'brizy-pro'),
                'brizy_dc_post_author_url',
                function ($context) {
                    return get_the_author_meta('url', $context->getAuthor());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('Site Title', 'brizy-pro'),
                'brizy_dc_site_title',
                function () {
                    return get_bloginfo();
                },
                self::CONFIG_KEY_TEXT,
                null,
                [],
                []
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('Site Tagline', 'brizy-pro'),
                'brizy_dc_site_tagline',
                function () {
                    return get_bloginfo('description');
                },
                self::CONFIG_KEY_TEXT,
                null,
                [],
                []
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('Archive Title', 'brizy-pro'),
                'brizy_dc_archive_title',
                function () {
                    return $this->getArchiveTitle();
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('Archive Description', 'brizy-pro'),
                'brizy_dc_archive_description',
                function () {
                    return wp_kses_post(get_the_archive_description());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('Username', 'brizy-pro'),
                'editor_user_username',
                function () {

                    if (!is_user_logged_in()) {
                        return '';
                    }

                    $user = wp_get_current_user();

                    return $user->data->user_login;
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(__('User Role', 'brizy-pro'), 'editor_user_role', function () {

                if (!is_user_logged_in()) {
                    return '';
                }

                $user = wp_get_current_user();
                $roles = (array)$user->roles;

                return implode(',', $roles);
            }, self::CONFIG_KEY_TEXT)
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('User First Name', 'brizy-pro'),
                'editor_user_first_name',
                function () {

                    if (!is_user_logged_in()) {
                        return '';
                    }

                    return get_the_author_meta('user_firstname', get_current_user_id());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('User Last Name', 'brizy-pro'),
                'editor_user_last_name',
                function () {

                    if (!is_user_logged_in()) {
                        return '';
                    }

                    return get_the_author_meta('user_lastname', get_current_user_id());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('User Nickname', 'brizy-pro'),
                'editor_user_nickname',
                function () {

                    if (!is_user_logged_in()) {
                        return '';
                    }

                    return get_the_author_meta('nickname', get_current_user_id());
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('User Display Name', 'brizy-pro'),
                'editor_user_display_name',
                function () {

                    if (!is_user_logged_in()) {
                        return '';
                    }

                    $user = wp_get_current_user();

                    return $user->data->display_name;
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(__('User Email', 'brizy-pro'), 'editor_user_email', function () {

                if (!is_user_logged_in()) {
                    return '';
                }

                $user = wp_get_current_user();

                return $user->data->user_email;
            }, self::CONFIG_KEY_TEXT)
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('User Website', 'brizy-pro'),
                'editor_user_website',
                function () {

                    if (!is_user_logged_in()) {
                        return '';
                    }

                    $user = wp_get_current_user();

                    return $user->data->user_url;
                },
                self::CONFIG_KEY_TEXT
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('User Description', 'brizy-pro'),
                'editor_user_description',
                function () {

                    if (!is_user_logged_in()) {
                        return '';
                    }

                    return get_the_author_meta('description', get_current_user_id());
                },
                self::CONFIG_KEY_TEXT
            )
        );

    }

    /**
     * @return array
     */
    private function getMediaPlaceholders()
    {
        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_FeaturedImg(
                __('Featured Image', 'brizy-pro'),
                'brizy_dc_img_featured_image',
                self::CONFIG_KEY_IMAGE
            )
        );
        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_Logo(
                __('Site logo', 'brizy-pro'),
                'brizy_dc_img_site_logo',
                self::CONFIG_KEY_IMAGE
            )
        );
        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('Author Profile Picture', 'brizy-pro'),
                'brizy_dc_img_avatar_url',
                function ($context) {
                    return esc_url(get_avatar_url($context->getAuthor()));
                },
                self::CONFIG_KEY_IMAGE
            )
        );
        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_SimplePostAware(
                __('User Avatar', 'brizy-pro'),
                'editor_user_avatar',
                function () {
                    return esc_url(get_avatar_url(get_current_user_id()));
                },
                self::CONFIG_KEY_IMAGE
            )
        );
    }

    /**
     * @return array
     */
    private function getLinkPlaceholders()
    {
        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_Link(
                __('Post URL', 'brizy-pro'),
                'brizy_dc_url_post',
                function ($context, $placeholder, $entity = null) {
                    if ($entity || ($entity = $context->getEntity())) {
                        return get_permalink($entity);
                    }

                    return '';
                },
                self::CONFIG_KEY_LINK
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_Link(
                __('Author URL', 'brizy-pro'),
                'brizy_dc_url_author',
                function ($context, $placeholder, $entity = null) {
                    if ($entity || ($entity = $context->getAuthor())) {
                        return get_author_posts_url($entity);
                    }

                    return '';
                },
                self::CONFIG_KEY_LINK
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_Link(
                __('Comments URL', 'brizy-pro'),
                'brizy_dc_url_comments',
                function ($context, $placeholder, $entity = null) {
                    if ($entity || ($entity = $context->getEntity())) {
                        return get_comments_link($entity->ID);
                    }

                    return '';
                },
                self::CONFIG_KEY_LINK
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_Link(
                __('Site URL', 'brizy-pro'),
                'brizy_dc_url_site',
                function ($context, $placeholder, $entity = null) {
                    return esc_url(home_url('/'));
                },
                self::CONFIG_KEY_LINK,
                null,
                [],
                []
            )
        );

        $this->registerPlaceholder(
            $p = new Brizy_Content_Placeholders_Simple(
                __('Login Url', 'brizy-pro'),
                'editor_login_url',
                function ($context, $placeholder, $entity = null) {
                    return esc_url(site_url('wp-login.php', 'login_post'));
                },
                self::CONFIG_KEY_LINK,
                null,
                [],
                []
            )
        );

        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                __('Logout Url', 'brizy-pro'),
                'editor_logout_url',
                function (Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder, $entity = null) {
                    $redirect = $contentPlaceholder->getAttribute('redirect');

                    if ($redirect === 'samePage') {
                        $redirect = home_url($_SERVER['REQUEST_URI']);
                    }

                    return add_query_arg('isEditorLogout', '1', wp_logout_url($redirect));

                },
                self::CONFIG_KEY_LINK,
                null,
                [],
                []
            )
        );

        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                __('Lost Password Url', 'brizy-pro'),
                'editor_lostpassword_url',
                function () {
                    if (is_multisite()) {
                        $blog_details = get_blog_details();
                        $wp_login_path = $blog_details->path.'wp-login.php';
                    } else {
                        $wp_login_path = 'wp-login.php';
                    }

                    return add_query_arg(['action' => 'lostpassword'], network_site_url($wp_login_path, 'login'));

                },
                self::CONFIG_KEY_LINK,
                null,
                [],
                []
            )
        );

        $this->registerPlaceholder(
            new Brizy_Content_Placeholders_Simple(
                __('User registration URL', 'brizy-pro'),
                'editor_registration_url',
                function () {
                    return site_url('wp-login.php?action=register', 'login');
                },
                self::CONFIG_KEY_LINK,
                null,
                [],
                []
            )
        );

        $this->registerPlaceholder(
            new BrizyPro_Content_Placeholders_Link(
                __('User Profile', 'brizy-pro'),
                'editor_user_profile_url',
                function ($context, $placeholder, $entity = null) {
                    if ($entity || ($entity = get_current_user_id())) {
                        return get_author_posts_url($entity->ID);
                    }

                },
                self::CONFIG_KEY_LINK
            )
        );

        $this->registerPlaceholder(
            $p = new BrizyPro_Content_Placeholders_Link(
                __('Archive URL', 'brizy-pro'),
                'brizy_dc_url_archive',
                function ($context, $placeholder, $entity = null) {

                    if (is_category() || is_tag() || is_tax()) {
                        $url = get_term_link(get_queried_object());
                    } elseif (is_author()) {
                        $url = get_author_posts_url(get_queried_object_id());
                    } elseif (is_year()) {
                        $url = get_year_link(get_query_var('year'));
                    } elseif (is_month()) {
                        $url = get_month_link(get_query_var('year'), get_query_var('monthnum'));
                    } elseif (is_day()) {
                        $url = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
                    } elseif (is_post_type_archive()) {
                        $url = get_post_type_archive_link(get_post_type());
                    } else {
                        $url = get_post_type_archive_link('post');
                    }

                    return $url;
                },
                self::CONFIG_KEY_LINK
            )
        );
    }

    /**
     * @return array
     */
    private function getTextPlaceholderTerms($group)
    {
        $terms = get_taxonomies(array('public' => true, 'show_ui' => true), 'objects');
        $out = array();

        foreach ($terms as $tax) {

            $this->registerPlaceholder(
                new BrizyPro_Content_Placeholders_SimplePostAware(
                    sprintf('%s links', $tax->label),
                    "brizy_dc_post_tax_{$tax->name}",
                    function ($context) use ($tax) {
                        return self::getTermsPlacehoderValue($tax, $context->getEntity()->ID, true);
                    },
                    $group
                )
            );

            $this->registerPlaceholder(
                new BrizyPro_Content_Placeholders_SimplePostAware(
                    sprintf('%s names', $tax->label),
                    "brizy_dc_post_tax_{$tax->name}_name",
                    function ($context) use ($tax) {
                        return self::getTermsPlacehoderValue($tax, $context->getEntity()->ID, false);
                    },
                    $group
                )
            );
        }

        return $out;
    }

    static public function getTermsPlacehoderValue($tax, $postId, $addLinks)
    {
        $terms = get_terms(['object_ids' => $postId, 'taxonomy' => $tax->name]);

        if (!$terms || is_wp_error($terms)) {
            return '';
        }

        $values = [];

        foreach ($terms as $term) {

            if ($addLinks) {
                if (!($url = get_term_link($term)) || is_wp_error($url)) {
                    continue;
                }

                $values[] = '<a href="'.esc_url($url).'">'.$term->name.'</a>';
            } else {
                $values[] = $term->name;
            }
        }

        return implode(', ', $values);
    }

    /**
     * @return Brizy_Content_Placeholders_Simple
     */
    private function postNavigation()
    {
        return new Brizy_Content_Placeholders_Simple(
            __('Post Navigation', 'brizy-pro'),
            'editor_post_navigation',
            function (Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder) {

                /*
                 * Use array_change_key_case to keep backward compatibility.
                 * When it was as wp shortcode the atts keys come here lowercase
                 */
                $atts = array_change_key_case($contentPlaceholder->getAttributes(), CASE_LOWER);

                $post_type = get_post_type(get_queried_object_id());
                $post_types = explode(',', $atts['post_type']);
                $in_same_term = in_array($post_type, $post_types);
                $taxonomy = empty($atts["{$post_type}_taxonomy"]) ? 'category' : $atts["{$post_type}_taxonomy"];

                if ($atts['showpost'] == 'off') {
                    $prev = get_previous_post_link('%link', $atts['titleprevious'], $in_same_term, '', $taxonomy);
                    $next = get_next_post_link('%link', $atts['titlenext'], $in_same_term, '', $taxonomy);
                } else {
                    $prev = get_previous_post_link('%link', '%title', $in_same_term, '', $taxonomy);
                    $next = get_next_post_link('%link', '%title', $in_same_term, '', $taxonomy);
                }

                $prev = str_replace('href="', 'class="brz-a" href="', $prev);
                $next = str_replace('href="', 'class="brz-a" href="', $next);

                if (empty($prev) && empty($next)) {
                    return '';
                }

                $text_nav = '';

                if ($atts['showtitle'] == 'on') {

                    $prevTitle = '';

                    if ($prev) {
                        $prevTitle =
                            '<span class="brz-span">'.
                            ($atts['showpost'] == 'off' ? $prev : $atts['titleprevious']).
                            '</span>';
                    }

                    $nextTitle = '';

                    if ($next) {
                        $nextTitle =
                            '<span class="brz-span">'.
                            ($atts['showpost'] == 'off' ? $next : $atts['titlenext']).
                            '</span>';
                    }

                    $text_nav = '<div class="brz-navigation-title">'.$prevTitle.$nextTitle.'</div>';
                }

                if ($atts['showpost'] == 'off') {
                    return $text_nav;
                }

                return $text_nav.'<div class="brz-navigation">'.$prev.$next.'</div>';
            }
        );
    }

    private function getArchiveTitle()
    {
        if (is_category()) {
            $title = single_cat_title('', false);
        } elseif (is_tag()) {
            $title = single_tag_title('', false);
        } elseif (is_author()) {
            $title = get_the_author();
        } elseif (is_year()) {
            $title = get_the_date(_x('Y', 'yearly archives date format'));
        } elseif (is_month()) {
            $title = get_the_date(_x('F Y', 'monthly archives date format'));
        } elseif (is_day()) {
            $title = get_the_date(_x('F j, Y', 'daily archives date format'));
        } elseif (is_post_type_archive()) {
            $title = post_type_archive_title('', false);
        } elseif (is_tax()) {
            $tax = get_taxonomy(get_queried_object()->taxonomy);
            $title = sprintf('%1$s: %2$s', $tax->labels->singular_name, single_term_title('', false));
        } else {
            $title = ''; // __( 'Archives' )
        }

        return apply_filters('get_the_archive_title', $title);
    }

    /**
     * @throws Exception
     */
    private function getUserField(Brizy_Content_Context $context, ContentPlaceholder $contentPlaceholder, $field)
    {
        if (!$attrContext = $contentPlaceholder->getAttribute('context')) {
            $attrContext = 'auto';
        }

        $userId = 'profile' === $attrContext ? $context->getAuthor() : get_current_user_id();

        if (!$userId) {
            return '';
        }

        return get_the_author_meta($field, $userId);
    }
}
