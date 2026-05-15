<?php
/** VOIDairo theme functions. */
if (!defined('ABSPATH')) { exit; }

function voidairo_defaults() {
    return array(
        'serif' => false,
        'auto_dark' => true,
        'dark_mode' => 'system',
        'pjax' => true,
        'ajax_comments' => true,
        'toc' => true,
        'likes' => true,
        'views' => true,
        'mac_code' => true,
        'show_card_meta' => true,
        'meta_date' => true,
        'meta_author' => true,
        'meta_category' => true,
        'meta_views' => true,
        'meta_likes' => true,
        'meta_comments' => true,
        'meta_order' => 'date,author,category,views,likes,comments',
        'show_read_more' => true,
        'hero_image' => '',
        'hero_image_id' => 0,
        'hero_title' => '',
        'hero_subtitle' => '',
        'font_preset' => 'void',
    );
}

function voidairo_options() {
    $saved = get_option('voidairo_options', array());
    return wp_parse_args(is_array($saved) ? $saved : array(), voidairo_defaults());
}

function voidairo_option_value($key, $default = null) {
    $options = voidairo_options();
    return array_key_exists($key, $options) ? $options[$key] : $default;
}

function voidairo_option($key) {
    return !empty(voidairo_option_value($key));
}

function voidairo_setup() {
    load_theme_textdomain('voidairo', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', array('height' => 96, 'width' => 320, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets'));
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('style.css');
    register_nav_menus(array('primary' => __('Primary Menu', 'voidairo')));
    add_image_size('voidairo-card', 1200, 675, true);
    add_image_size('voidairo-cover', 1600, 900, true);
}
add_action('after_setup_theme', 'voidairo_setup');

function voidairo_content_width() { $GLOBALS['content_width'] = 820; }
add_action('after_setup_theme', 'voidairo_content_width', 0);

function voidairo_widgets_init() {
    register_sidebar(array(
        'name' => __('Sidebar', 'voidairo'),
        'id' => 'sidebar-1',
        'description' => __('Widgets shown beside archive pages.', 'voidairo'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
}
add_action('widgets_init', 'voidairo_widgets_init');

function voidairo_scripts() {
    $ver = wp_get_theme()->get('Version');
    $opts = voidairo_options();
    wp_enqueue_style('voidairo-style', get_stylesheet_uri(), array(), $ver);
    wp_enqueue_script('voidairo-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), $ver, true);
    wp_localize_script('voidairo-theme', 'VOIDAIRO', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'likeNonce' => wp_create_nonce('voidairo_like'),
        'commentNonce' => wp_create_nonce('voidairo_comment'),
        'options' => array(
            'pjax' => (bool) $opts['pjax'],
            'ajaxComments' => (bool) $opts['ajax_comments'],
            'toc' => (bool) $opts['toc'],
            'autoDark' => (bool) $opts['auto_dark'],
            'darkMode' => in_array($opts['dark_mode'] ?? 'system', array('system', 'dark', 'light'), true) ? $opts['dark_mode'] : 'system',
            'likes' => (bool) $opts['likes'],
        ),
        'i18n' => array(
            'loading' => __('加载中…', 'voidairo'),
            'commentError' => __('评论提交失败，请检查表单后重试。', 'voidairo'),
            'commentPending' => __('你的评论正在等待审核。', 'voidairo'),
        ),
    ));
}
add_action('wp_enqueue_scripts', 'voidairo_scripts');

function voidairo_defer_script($tag, $handle) {
    if ('voidairo-theme' === $handle && false === strpos($tag, ' defer')) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'voidairo_defer_script', 10, 2);

function voidairo_excerpt_length() { return 34; }
add_filter('excerpt_length', 'voidairo_excerpt_length');
function voidairo_excerpt_more() { return '…'; }
add_filter('excerpt_more', 'voidairo_excerpt_more');

function voidairo_meta_description() {
    if (is_singular()) {
        $text = has_excerpt() ? get_the_excerpt() : wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', get_queried_object_id())));
    } elseif (is_category() || is_tag() || is_tax()) {
        $text = term_description();
    } else {
        $text = get_bloginfo('description');
    }
    return wp_trim_words(wp_strip_all_tags($text), 32, '');
}

function voidairo_primary_image_url($size = 'voidairo-cover') {
    if (is_singular() && has_post_thumbnail()) {
        return get_the_post_thumbnail_url(get_queried_object_id(), $size);
    }
    $custom_logo_id = get_theme_mod('custom_logo');
    return $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';
}

function voidairo_canonical_url() {
    if (is_404()) { return ''; }
    if (is_singular()) { return get_permalink(get_queried_object_id()); }
    if (is_search()) { return is_paged() ? get_pagenum_link((int) get_query_var('paged')) : get_search_link(); }
    if (is_front_page() || is_home()) { return is_paged() ? get_pagenum_link((int) get_query_var('paged')) : home_url('/'); }
    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        $url = $term ? get_term_link($term) : '';
        return is_paged() ? get_pagenum_link((int) get_query_var('paged')) : (is_wp_error($url) ? '' : $url);
    }
    if (is_author()) { return is_paged() ? get_pagenum_link((int) get_query_var('paged')) : get_author_posts_url(get_queried_object_id()); }
    if (is_post_type_archive()) { return is_paged() ? get_pagenum_link((int) get_query_var('paged')) : get_post_type_archive_link(get_query_var('post_type')); }
    if (is_date()) { return get_pagenum_link(max(1, (int) get_query_var('paged'))); }
    return get_pagenum_link(max(1, (int) get_query_var('paged')));
}

function voidairo_head_meta() {
    $desc = voidairo_meta_description();
    $title = wp_get_document_title();
    $url = voidairo_canonical_url();
    $image = voidairo_primary_image_url();
    echo "\n<meta name=\"description\" content=\"" . esc_attr($desc) . "\">\n";
    if ($url) { echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n"; }
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    if ($url) { echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n"; }
    echo '<meta name="twitter:card" content="' . ($image ? 'summary_large_image' : 'summary') . '">' . "\n";
    if ($image) { echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n"; }
}
add_action('wp_head', 'voidairo_head_meta', 2);

function voidairo_schema() {
    $site = array('@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => get_bloginfo('name'), 'url' => home_url('/'), 'potentialAction' => array('@type' => 'SearchAction', 'target' => home_url('/?s={search_term_string}'), 'query-input' => 'required name=search_term_string'));
    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($site, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    if (is_singular('post')) {
        $post_id = get_queried_object_id();
        $data = array('@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => get_the_title($post_id), 'description' => voidairo_meta_description(), 'datePublished' => get_the_date(DATE_W3C, $post_id), 'dateModified' => get_the_modified_date(DATE_W3C, $post_id), 'mainEntityOfPage' => get_permalink($post_id), 'author' => array('@type' => 'Person', 'name' => get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id))), 'publisher' => array('@type' => 'Organization', 'name' => get_bloginfo('name')));
        $image = get_the_post_thumbnail_url($post_id, 'full');
        if ($image) { $data['image'] = array($image); }
        echo "<script type=\"application/ld+json\">" . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }
}
add_action('wp_head', 'voidairo_schema', 20);

function voidairo_thumbnail($size = 'voidairo-card', $class = '') {
    if (!has_post_thumbnail()) { return; }
    the_post_thumbnail($size, array('class' => $class, 'loading' => is_singular() ? 'eager' : 'lazy', 'decoding' => 'async', 'fetchpriority' => is_singular() ? 'high' : 'auto', 'sizes' => '(max-width: 900px) 100vw, 820px'));
}

function voidairo_get_views($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    return max(0, (int) get_post_meta($post_id, '_voidairo_views', true));
}

function voidairo_get_likes($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    return max(0, (int) get_post_meta($post_id, '_voidairo_likes', true));
}

function voidairo_track_view() {
    if (!voidairo_option('views') || !is_singular('post') || is_admin()) { return; }
    $post_id = get_queried_object_id();
    if (!$post_id) { return; }
    $cookie = 'voidairo_viewed_' . $post_id;
    if (!empty($_COOKIE[$cookie])) { return; }
    update_post_meta($post_id, '_voidairo_views', voidairo_get_views($post_id) + 1);
    setcookie($cookie, '1', time() + HOUR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
}
add_action('template_redirect', 'voidairo_track_view');

function voidairo_meta_keys() {
    return array('date', 'author', 'category', 'views', 'likes', 'comments');
}

function voidairo_meta_labels() {
    return array(
        'date' => __('日期', 'voidairo'),
        'author' => __('作者', 'voidairo'),
        'category' => __('分类', 'voidairo'),
        'views' => __('浏览量', 'voidairo'),
        'likes' => __('点赞', 'voidairo'),
        'comments' => __('评论', 'voidairo'),
    );
}

function voidairo_meta_order() {
    $raw = (string) voidairo_option_value('meta_order', 'date,author,category,views,likes,comments');
    $parts = preg_split('/[\s,，|]+/u', $raw);
    $allowed = voidairo_meta_keys();
    $keys = array();
    foreach ($parts as $part) {
        $key = sanitize_key($part);
        if (in_array($key, $allowed, true) && !in_array($key, $keys, true)) { $keys[] = $key; }
    }
    return $keys;
}

function voidairo_post_meta() {
    $items = array();
    foreach (voidairo_meta_order() as $key) {
        if ('date' === $key) {
            $items[] = '<time datetime="' . esc_attr(get_the_date(DATE_W3C)) . '">' . esc_html(get_the_date()) . '</time>';
        } elseif ('author' === $key) {
            $items[] = '<span>' . esc_html(get_the_author()) . '</span>';
        } elseif ('category' === $key) {
            $cats = get_the_category_list(', ');
            if ($cats) { $items[] = '<span>' . wp_kses_post($cats) . '</span>'; }
        } elseif ('views' === $key && voidairo_option('views')) {
            $items[] = '<span>' . sprintf(esc_html__('%s views', 'voidairo'), esc_html(number_format_i18n(voidairo_get_views()))) . '</span>';
        } elseif ('likes' === $key && voidairo_option('likes')) {
            $items[] = '<span>' . sprintf(esc_html__('%s likes', 'voidairo'), esc_html(number_format_i18n(voidairo_get_likes()))) . '</span>';
        } elseif ('comments' === $key && !post_password_required() && comments_open()) {
            ob_start();
            comments_popup_link(__('No comments', 'voidairo'), __('1 comment', 'voidairo'), __('% comments', 'voidairo'));
            $items[] = '<span>' . ob_get_clean() . '</span>';
        }
    }
    if (!$items) { return; }
    echo '<div class="post-meta">' . implode('<span aria-hidden="true">•</span>', $items) . '</div>';
}

function voidairo_like_button($post_id = null) {
    if (!voidairo_option('likes')) { return; }
    $post_id = $post_id ?: get_the_ID();
    echo '<button class="voidairo-like" type="button" data-post-id="' . esc_attr($post_id) . '" aria-label="' . esc_attr__('Like this post', 'voidairo') . '"><span aria-hidden="true">♡</span><strong>' . esc_html(number_format_i18n(voidairo_get_likes($post_id))) . '</strong></button>';
}

function voidairo_like_fingerprint($post_id) {
    $user = get_current_user_id();
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    return substr(wp_hash($post_id . '|' . ($user ? 'u:' . $user : 'ip:' . $ip . '|ua:' . $ua)), 0, 20);
}

function voidairo_ajax_like() {
    check_ajax_referer('voidairo_like', 'nonce');
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    if (!$post_id || 'publish' !== get_post_status($post_id)) { wp_send_json_error(array('message' => __('Invalid post.', 'voidairo'), 'likes' => voidairo_get_likes($post_id)), 400); }

    $fingerprint = voidairo_like_fingerprint($post_id);
    $lock_key = 'voidairo_like_lock_' . $post_id . '_' . $fingerprint;
    $rate_key = 'voidairo_like_rate_' . substr($fingerprint, 0, 12);
    $current_likes = voidairo_get_likes($post_id);

    if (get_transient($lock_key) || !empty($_COOKIE['voidairo_liked_' . $post_id])) {
        wp_send_json_error(array('message' => __('你已经点过赞了。', 'voidairo'), 'likes' => $current_likes), 429);
    }

    $rate = (int) get_transient($rate_key);
    if ($rate >= 10) {
        wp_send_json_error(array('message' => __('点赞太频繁，请稍后再试。', 'voidairo'), 'likes' => $current_likes), 429);
    }
    set_transient($rate_key, $rate + 1, MINUTE_IN_SECONDS);
    set_transient($lock_key, 1, DAY_IN_SECONDS);

    $likes = $current_likes + 1;
    update_post_meta($post_id, '_voidairo_likes', $likes);
    setcookie('voidairo_liked_' . $post_id, '1', time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
    wp_send_json_success(array('likes' => $likes));
}
add_action('wp_ajax_voidairo_like', 'voidairo_ajax_like');
add_action('wp_ajax_nopriv_voidairo_like', 'voidairo_ajax_like');

function voidairo_body_classes($classes) {
    $preset = sanitize_key((string) voidairo_option_value('font_preset', 'void'));
    if (!in_array($preset, array('void', 'system', 'serif', 'chinese'), true)) { $preset = 'void'; }
    $classes[] = 'font-preset-' . $preset;
    if (voidairo_option('serif')) { $classes[] = 'use-serif'; }
    if (voidairo_option('mac_code')) { $classes[] = 'mac-code'; }
    return $classes;
}
add_filter('body_class', 'voidairo_body_classes');

function voidairo_customizer($wp_customize) {
    $wp_customize->add_setting('voidairo_serif', array('default' => false, 'sanitize_callback' => 'wp_validate_boolean'));
    $wp_customize->add_control('voidairo_serif', array('type' => 'checkbox', 'section' => 'title_tagline', 'label' => __('Use serif reading font', 'voidairo')));
}
add_action('customize_register', 'voidairo_customizer');

function voidairo_admin_menu() {
    add_theme_page('VOIDairo 主题设置', 'VOIDairo 设置', 'edit_theme_options', 'voidairo-settings', 'voidairo_settings_page');
}
add_action('admin_menu', 'voidairo_admin_menu');


function voidairo_admin_assets($hook) {
    if ('appearance_page_voidairo-settings' !== $hook) { return; }
    wp_enqueue_media();
    $ver = wp_get_theme()->get('Version');
    wp_enqueue_script('voidairo-admin-settings', get_template_directory_uri() . '/assets/js/admin-settings.js', array('jquery'), $ver, true);
    wp_register_style('voidairo-admin-settings', false, array(), $ver);
    wp_enqueue_style('voidairo-admin-settings');
    wp_add_inline_style('voidairo-admin-settings', '.voidairo-meta-sorter{display:flex;flex-wrap:wrap;gap:10px;max-width:760px}.voidairo-meta-item{display:inline-flex;align-items:center;gap:8px;padding:9px 10px;border:1px solid #ccd0d4;border-radius:999px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);cursor:grab}.voidairo-meta-item.is-disabled{opacity:.48;background:#f0f0f1}.voidairo-meta-item.is-dragging{opacity:.7;cursor:grabbing}.voidairo-meta-item button{min-width:28px;height:28px;padding:0;border-radius:999px}.voidairo-meta-label{font-weight:600}.voidairo-meta-position{min-width:1.4em;color:#646970;text-align:center}');
}
add_action('admin_enqueue_scripts', 'voidairo_admin_assets');

function voidairo_sanitize_options($input) {
    $input = is_array($input) ? $input : array();
    $defaults = voidairo_defaults();
    $output = array();
    $boolean_keys = array('serif', 'auto_dark', 'pjax', 'ajax_comments', 'toc', 'likes', 'views', 'mac_code', 'show_read_more');
    foreach ($boolean_keys as $key) { $output[$key] = !empty($input[$key]); }
    $output['show_card_meta'] = true; // Legacy option: meta_order now controls whether meta is shown.
    $output['hero_image'] = isset($input['hero_image']) ? esc_url_raw(trim(wp_unslash($input['hero_image']))) : '';
    $output['hero_image_id'] = isset($input['hero_image_id']) ? absint($input['hero_image_id']) : 0;
    $meta_clean = array();
    if (isset($input['meta_order']) && is_array($input['meta_order'])) {
        $meta_parts = array_map('sanitize_key', wp_unslash($input['meta_order']));
    } else {
        $meta_order = isset($input['meta_order']) ? sanitize_text_field(wp_unslash($input['meta_order'])) : $defaults['meta_order'];
        $meta_parts = preg_split('/[\s,，|]+/u', $meta_order);
    }
    foreach ((array) $meta_parts as $part) {
        $key = sanitize_key($part);
        if (in_array($key, voidairo_meta_keys(), true) && !in_array($key, $meta_clean, true)) { $meta_clean[] = $key; }
    }
    $output['meta_order'] = implode(',', $meta_clean);
    $dark = isset($input['dark_mode']) ? sanitize_key(wp_unslash($input['dark_mode'])) : $defaults['dark_mode'];
    $output['dark_mode'] = in_array($dark, array('system', 'dark', 'light'), true) ? $dark : $defaults['dark_mode'];
    $output['hero_title'] = isset($input['hero_title']) ? sanitize_text_field(wp_unslash($input['hero_title'])) : '';
    $output['hero_subtitle'] = isset($input['hero_subtitle']) ? sanitize_text_field(wp_unslash($input['hero_subtitle'])) : '';
    $font = isset($input['font_preset']) ? sanitize_key(wp_unslash($input['font_preset'])) : $defaults['font_preset'];
    $output['font_preset'] = in_array($font, array('void', 'system', 'serif', 'chinese'), true) ? $font : $defaults['font_preset'];
    return wp_parse_args($output, $defaults);
}

function voidairo_register_settings() {
    register_setting('voidairo_settings', 'voidairo_options', array('sanitize_callback' => 'voidairo_sanitize_options'));
}
add_action('admin_init', 'voidairo_register_settings');


function voidairo_render_manual_update_box() {
    if (!current_user_can('update_themes')) {
        echo '<p>当前账号没有更新主题权限。</p>';
        return;
    }
    if (isset($_POST['voidairo_check_update']) && check_admin_referer('voidairo_check_update_action', 'voidairo_check_update_nonce')) {
        delete_site_transient('voidairo_github_release');
        delete_site_transient('update_themes');
        wp_update_themes();
        echo '<div class="notice notice-success inline"><p>已重新检查 GitHub Release 更新。</p></div>';
    }
    $updates = get_site_transient('update_themes');
    $theme = get_stylesheet();
    $current = wp_get_theme()->get('Version');
    $update = isset($updates->response[$theme]) ? $updates->response[$theme] : null;
    echo '<p>当前版本：<strong>' . esc_html($current) . '</strong></p>';
    if ($update && !empty($update['new_version'])) {
        $url = wp_nonce_url(admin_url('update.php?action=upgrade-theme&theme=' . rawurlencode($theme)), 'upgrade-theme_' . $theme);
        echo '<p>发现新版本：<strong>' . esc_html($update['new_version']) . '</strong> <a class="button button-primary" href="' . esc_url($url) . '">手动更新主题</a></p>';
    } else {
        echo '<p>暂未发现可用更新。</p>';
    }
    echo '<form method="post" style="margin-top:8px">';
    wp_nonce_field('voidairo_check_update_action', 'voidairo_check_update_nonce');
    submit_button('手动检查更新', 'secondary', 'voidairo_check_update', false);
    echo '</form>';
}

function voidairo_settings_page() {
    if (!current_user_can('edit_theme_options')) { return; }
    $opts = voidairo_options();
    $checks = array(
        'show_read_more' => '首页/列表文章显示 Read more 按钮',
        'pjax' => '启用 PJAX 页面无刷新切换（如果站点闪烁或异常可关闭）',
        'ajax_comments' => '启用 AJAX 评论',
        'toc' => '文章页自动生成目录',
        'likes' => '启用文章点赞',
        'views' => '启用轻量浏览量统计',
        'mac_code' => '启用 VS Code 风格代码块外观',
        'serif' => '文章阅读区域使用衬线体',
    );
    $font_presets = array(
        'void' => 'VOID 默认：系统无衬线 + 中文优化',
        'system' => '纯系统字体：最快加载',
        'serif' => '衬线阅读：思源宋体/Noto Serif SC 优先',
        'chinese' => '中文屏显：苹方/微软雅黑优先',
    );
    $dark_modes = array('system' => '跟随系统', 'dark' => '始终深色', 'light' => '始终浅色');
    echo '<div class="wrap"><h1>VOIDairo 主题设置</h1><p>参考 VOID 的核心设置重新整理：顶部大图、颜色/字体、PJAX、目录、评论、代码块和首页显示项。</p><form method="post" action="options.php">';
    settings_fields('voidairo_settings');
    echo '<h2>首页顶部大图</h2><table class="form-table" role="presentation"><tbody>';
    echo '<tr><th scope="row"><label for="voidairo_hero_image">首页顶部大图</label></th><td><input id="voidairo_hero_image" class="regular-text" type="url" name="voidairo_options[hero_image]" value="' . esc_attr($opts['hero_image']) . '" placeholder="https://example.com/banner.jpg"> <input id="voidairo_hero_image_id" type="hidden" name="voidairo_options[hero_image_id]" value="' . esc_attr((int) $opts['hero_image_id']) . '"> <button type="button" class="button" id="voidairo-pick-hero">从媒体库选择/上传</button> <button type="button" class="button" id="voidairo-clear-hero">清除</button><p class="description">可手动填写图片 URL，也可直接从 WordPress 媒体库选择或上传。</p><div id="voidairo-hero-preview" style="margin-top:10px;max-width:360px">' . (!empty($opts['hero_image']) ? '<img src="' . esc_url($opts['hero_image']) . '" style="max-width:100%;height:auto;border-radius:8px">' : '') . '</div></td></tr>';
    echo '<tr><th scope="row"><label for="voidairo_hero_title">首页顶部大标题</label></th><td><input id="voidairo_hero_title" class="regular-text" type="text" name="voidairo_options[hero_title]" value="' . esc_attr($opts['hero_title']) . '" placeholder="' . esc_attr(get_bloginfo('name')) . '"></td></tr>';
    echo '<tr><th scope="row"><label for="voidairo_hero_subtitle">首页顶部小标题</label></th><td><input id="voidairo_hero_subtitle" class="regular-text" type="text" name="voidairo_options[hero_subtitle]" value="' . esc_attr($opts['hero_subtitle']) . '" placeholder="' . esc_attr(get_bloginfo('description')) . '"></td></tr>';
    echo '</tbody></table>';
    echo '<h2>颜色与字体</h2><table class="form-table" role="presentation"><tbody><tr><th scope="row">深色模式</th><td><select name="voidairo_options[dark_mode]">';
    foreach ($dark_modes as $key => $label) { echo '<option value="' . esc_attr($key) . '" ' . selected($opts['dark_mode'], $key, false) . '>' . esc_html($label) . '</option>'; }
    echo '</select></td></tr><tr><th scope="row">预设字体</th><td><select name="voidairo_options[font_preset]">';
    foreach ($font_presets as $key => $label) { echo '<option value="' . esc_attr($key) . '" ' . selected($opts['font_preset'], $key, false) . '>' . esc_html($label) . '</option>'; }
    echo '</select></td></tr></tbody></table>';
    echo '<h2>显示与功能开关</h2><table class="form-table" role="presentation"><tbody>';
    $meta_labels = voidairo_meta_labels();
    $ordered_meta = voidairo_meta_order();
    $meta_items = array_merge($ordered_meta, array_values(array_diff(array_keys($meta_labels), $ordered_meta)));
    echo '<tr><th scope="row">元信息显示与排序</th><td><div id="voidairo-meta-sorter" class="voidairo-meta-sorter" aria-label="元信息显示与排序">';
    foreach ($meta_items as $meta_key) {
        $enabled = in_array($meta_key, $ordered_meta, true);
        echo '<div class="voidairo-meta-item' . (!$enabled ? ' is-disabled' : '') . '" draggable="true" data-key="' . esc_attr($meta_key) . '"><span class="voidairo-meta-position"></span><label><input class="voidairo-meta-enabled" type="checkbox" ' . checked($enabled, true, false) . '> <span class="voidairo-meta-label">' . esc_html($meta_labels[$meta_key]) . '</span></label><input type="hidden" name="voidairo_options[meta_order][]" value="' . esc_attr($meta_key) . '" ' . disabled(!$enabled, true, false) . '><button type="button" class="button voidairo-meta-move-up" aria-label="上移">↑</button><button type="button" class="button voidairo-meta-move-down" aria-label="下移">↓</button></div>';
    }
    echo '</div><p class="description">点亮表示启用，点灰表示不显示；可拖动排序，也可用 ↑/↓ 调整。全部点灰则前台不显示元信息。</p></td></tr>';
    foreach ($checks as $key => $label) {
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td><label><input type="checkbox" name="voidairo_options[' . esc_attr($key) . ']" value="1" ' . checked(!empty($opts[$key]), true, false) . '> 启用</label></td></tr>';
    }
    echo '</tbody></table>';
    submit_button('保存设置');
    echo '</form><hr><h2>主题更新</h2>';
    voidairo_render_manual_update_box();
    echo '</div>';
}

function voidairo_fallback_menu() {
    $class = (is_front_page() || is_home()) ? ' class="current-menu-item"' : '';
    echo '<ul class="primary-menu"><li' . $class . '><a href="' . esc_url(home_url('/')) . '">' . esc_html__('首页', 'voidairo') . '</a></li></ul>';
}

function voidairo_notice_shortcode($atts, $content = '') {
    return '<aside class="va-notice">' . wp_kses_post(do_shortcode($content)) . '</aside>';
}
add_shortcode('notice', 'voidairo_notice_shortcode');

function voidairo_photos_shortcode($atts, $content = '') {
    return '<div class="va-photos">' . do_shortcode($content) . '</div>';
}
add_shortcode('photos', 'voidairo_photos_shortcode');

function voidairo_ruby_shortcode($atts, $content = '') {
    $atts = shortcode_atts(array('text' => '', 'rt' => ''), $atts, 'ruby');
    if (!$atts['text'] && $content && strpos($content, ':') !== false) {
        list($atts['text'], $atts['rt']) = array_map('trim', explode(':', $content, 2));
    }
    if (!$atts['text'] || !$atts['rt']) { return esc_html($content); }
    return '<ruby>' . esc_html($atts['text']) . '<rp>(</rp><rt>' . esc_html($atts['rt']) . '</rt><rp>)</rp></ruby>';
}
add_shortcode('ruby', 'voidairo_ruby_shortcode');

function voidairo_links_shortcode($atts, $content = '') {
    $items = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $content)));
    if (!$items) { return ''; }
    $out = '<div class="va-links">';
    foreach ($items as $line) {
        if (!preg_match('/^\[(.+?)\]\((https?:\/\/.+?)\)(?:\+\((https?:\/\/.+?)\))?$/', $line, $m)) { continue; }
        $title = $m[1]; $url = $m[2]; $avatar = $m[3] ?? '';
        $out .= '<a class="va-link-card" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">';
        if ($avatar) { $out .= '<img src="' . esc_url($avatar) . '" alt="" loading="lazy" decoding="async">'; }
        $out .= '<span>' . esc_html($title) . '</span></a>';
    }
    return $out . '</div>';
}
add_shortcode('links', 'voidairo_links_shortcode');

function voidairo_ruby_inline_syntax($content) {
    return preg_replace_callback('/\{\{([^:{}]{1,80}):([^{}]{1,120})\}\}/u', function ($m) {
        return '<ruby>' . esc_html($m[1]) . '<rp>(</rp><rt>' . esc_html($m[2]) . '</rt><rp>)</rp></ruby>';
    }, $content);
}
add_filter('the_content', 'voidairo_ruby_inline_syntax', 8);
add_filter('comment_text', 'voidairo_ruby_inline_syntax', 8);


function voidairo_search_terms($query = null) {
    $query = null === $query ? get_search_query() : $query;
    $raw_terms = preg_split('/\s+/u', trim((string) $query));
    $terms = array();
    foreach ($raw_terms as $term) {
        $term = trim($term);
        if ('' !== $term && !in_array($term, $terms, true)) { $terms[] = $term; }
    }
    usort($terms, function ($a, $b) { return strlen($b) <=> strlen($a); });
    return $terms;
}

function voidairo_highlight_search_terms($text, $query = null) {
    $text = wp_strip_all_tags((string) $text);
    $terms = voidairo_search_terms($query);
    if (!$terms) { return esc_html($text); }
    $pattern = '/(' . implode('|', array_map(function ($term) { return preg_quote($term, '/'); }, $terms)) . ')/iu';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts)) { return esc_html($text); }
    $out = '';
    foreach ($parts as $part) {
        if ('' === $part) { continue; }
        $is_match = preg_match($pattern, $part);
        $out .= $is_match ? '<mark class="search-mark">' . esc_html($part) . '</mark>' : esc_html($part);
    }
    return $out;
}

function voidairo_text_pos($haystack, $needle) {
    if (function_exists('mb_stripos')) { return mb_stripos($haystack, $needle, 0, get_bloginfo('charset') ?: 'UTF-8'); }
    return stripos($haystack, $needle);
}

function voidairo_text_substr($text, $start, $length = null) {
    if (function_exists('mb_substr')) { return mb_substr($text, $start, $length, get_bloginfo('charset') ?: 'UTF-8'); }
    return null === $length ? substr($text, $start) : substr($text, $start, $length);
}

function voidairo_text_strlen($text) {
    if (function_exists('mb_strlen')) { return mb_strlen($text, get_bloginfo('charset') ?: 'UTF-8'); }
    return strlen($text);
}

function voidairo_search_snippets($post_id, $query, $limit = 3) {
    $raw = get_post_field('post_content', $post_id);
    $text = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags(strip_shortcodes($raw))));
    $terms = voidairo_search_terms($query);
    if (!$text || !$terms) { return '<p>' . esc_html(get_the_excerpt($post_id)) . '</p>'; }
    $snippets = array();
    $length = voidairo_text_strlen($text);
    foreach ($terms as $term) {
        if (count($snippets) >= $limit) { break; }
        $offset = 0;
        while (count($snippets) < $limit) {
            $rest = voidairo_text_substr($text, $offset);
            $found = voidairo_text_pos($rest, $term);
            if (false === $found) { break; }
            $pos = $offset + (int) $found;
            $start = max(0, $pos - 42);
            $chunk = voidairo_text_substr($text, $start, 120);
            $snippets[] = ($start > 0 ? '…' : '') . $chunk . ($length > $start + 120 ? '…' : '');
            $offset = $pos + max(1, voidairo_text_strlen($term));
        }
    }
    if (!$snippets) { $snippets[] = wp_trim_words($text, 28, '…'); }
    $out = '';
    foreach ($snippets as $snippet) { $out .= '<p>' . voidairo_highlight_search_terms($snippet, $query) . '</p>'; }
    if (count($snippets) >= $limit) { $out .= '<p class="search-more">…</p>'; }
    return $out;
}

function voidairo_comment_nonce_field($fields) {
    $fields .= '<input type="hidden" name="voidairo_comment_nonce" value="' . esc_attr(wp_create_nonce('voidairo_comment')) . '">';
    return $fields;
}
add_filter('comment_form_submit_field', 'voidairo_comment_nonce_field');

function voidairo_ajax_comment() {
    if (!voidairo_option('ajax_comments')) { wp_send_json_error(array('message' => __('AJAX comments are disabled.', 'voidairo')), 400); }
    $nonce = isset($_POST['voidairo_comment_nonce']) ? sanitize_text_field(wp_unslash($_POST['voidairo_comment_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'voidairo_comment')) { wp_send_json_error(array('message' => __('Security check failed.', 'voidairo')), 403); }
    $comment = wp_handle_comment_submission(wp_unslash($_POST));
    if (is_wp_error($comment)) { wp_send_json_error(array('message' => $comment->get_error_message()), 400); }
    $GLOBALS['comment'] = $comment;
    ob_start();
    ?>
    <li <?php comment_class('', $comment); ?> id="comment-<?php comment_ID(); ?>">
        <article class="comment-body">
            <footer class="comment-meta"><strong><?php comment_author(); ?></strong> <a href="#comment-<?php comment_ID(); ?>"><?php comment_date(); ?></a></footer>
            <div class="comment-content"><?php comment_text(); ?></div>
        </article>
    </li>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(array('html' => $html, 'approved' => (string) $comment->comment_approved));
}
add_action('wp_ajax_voidairo_comment', 'voidairo_ajax_comment');
add_action('wp_ajax_nopriv_voidairo_comment', 'voidairo_ajax_comment');


function voidairo_comment_form_defaults($defaults) {
    $defaults['label_submit'] = __('发布评论', 'voidairo');
    $defaults['title_reply'] = __('发表评论', 'voidairo');
    return $defaults;
}
add_filter('comment_form_defaults', 'voidairo_comment_form_defaults');

function voidairo_github_release() {
    $cached = get_site_transient('voidairo_github_release');
    if (false !== $cached) { return $cached; }
    $version = wp_get_theme()->get('Version');
    $res = wp_remote_get('https://api.github.com/repos/viuku/voidairo/releases/latest', array(
        'timeout' => 6,
        'headers' => array(
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'VOIDairo/' . $version . '; WordPress',
        ),
    ));
    if (is_wp_error($res) || 200 !== wp_remote_retrieve_response_code($res)) {
        set_site_transient('voidairo_github_release', null, HOUR_IN_SECONDS * 6);
        return null;
    }
    $data = json_decode(wp_remote_retrieve_body($res), true);
    set_site_transient('voidairo_github_release', is_array($data) ? $data : null, HOUR_IN_SECONDS * 6);
    return is_array($data) ? $data : null;
}

function voidairo_find_release_package($release, $latest = '') {
    $fallback = '';
    $exact = '';
    $versioned = '';
    if (!empty($release['assets']) && is_array($release['assets'])) {
        foreach ($release['assets'] as $asset) {
            $name = isset($asset['name']) ? (string) $asset['name'] : '';
            $url = $asset['browser_download_url'] ?? '';
            if (!$name || !$url || !preg_match('/^voidairo(?:[-_]?v?\d+(?:\.\d+){0,3}(?:[-_.a-z0-9]*)?)?\.zip$/i', $name)) { continue; }
            if (!$fallback) { $fallback = $url; }
            if ('voidairo.zip' === strtolower($name)) { $exact = $url; }
            if ($latest && false !== stripos($name, $latest)) { $versioned = $url; }
        }
    }
    if ($versioned) { return $versioned; }
    if ($exact) { return $exact; }
    if ($fallback) { return $fallback; }
    return !empty($release['zipball_url']) ? $release['zipball_url'] : '';
}

function voidairo_normalize_release_notes($body) {
    $body = (string) $body;
    $body = str_replace(array('\\r\\n', '\\n', '\\r'), "\n", $body);
    $body = preg_replace('/(?<=[\x{4e00}-\x{9fff}])\.\s*\n/u', "。\n", $body);
    $lines = array_filter(array_map('trim', preg_split('/\R/u', wp_strip_all_tags($body))));
    if (!$lines) { return '<p>查看 GitHub Release 获取更新说明。</p>'; }
    $out = '';
    foreach ($lines as $line) { $out .= '<p>' . esc_html($line) . '</p>'; }
    return $out;
}

function voidairo_themes_api($result, $action, $args) {
    if ('theme_information' !== $action || empty($args->slug) || get_stylesheet() !== $args->slug) { return $result; }
    $release = voidairo_github_release();
    if (!$release || empty($release['tag_name'])) { return $result; }
    $latest = ltrim((string) $release['tag_name'], 'v');
    $info = new stdClass();
    $info->name = wp_get_theme()->get('Name');
    $info->slug = get_stylesheet();
    $info->version = $latest;
    $info->author = wp_get_theme()->get('Author');
    $info->homepage = $release['html_url'] ?? 'https://github.com/viuku/voidairo';
    $info->download_link = voidairo_find_release_package($release, $latest);
    $info->sections = array(
        'description' => '<p>VOIDairo 通过 GitHub Release 提供主题更新。</p>',
        'changelog' => voidairo_normalize_release_notes($release['body'] ?? ''),
    );
    return $info;
}
add_filter('themes_api', 'voidairo_themes_api', 10, 3);

function voidairo_update_themes($transient) {
    if (empty($transient->checked) || !isset($transient->checked[get_stylesheet()])) { return $transient; }
    $release = voidairo_github_release();
    if (!$release || empty($release['tag_name'])) { return $transient; }
    $latest = ltrim((string) $release['tag_name'], 'v');
    $current = wp_get_theme()->get('Version');
    if (!version_compare($latest, $current, '>')) { return $transient; }
    $package = voidairo_find_release_package($release, $latest);
    $transient->response[get_stylesheet()] = array(
        'theme' => get_stylesheet(),
        'new_version' => $latest,
        'url' => $release['html_url'] ?? 'https://github.com/viuku/voidairo',
        'package' => $package,
    );
    return $transient;
}
add_filter('pre_set_site_transient_update_themes', 'voidairo_update_themes');

function voidairo_flush_archives_cache() {
    delete_transient('voidairo_archives_html');
}
add_action('save_post_post', 'voidairo_flush_archives_cache');
add_action('deleted_post', 'voidairo_flush_archives_cache');
add_action('transition_post_status', 'voidairo_flush_archives_cache');
