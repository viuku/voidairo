<?php
/** VOIDairo theme functions. */
if (!defined('ABSPATH')) { exit; }

function voidairo_defaults() {
    return array(
        'serif' => false,
        'auto_dark' => true,
        'pjax' => true,
        'ajax_comments' => true,
        'toc' => true,
        'likes' => true,
        'views' => true,
        'mac_code' => true,
        'show_card_meta' => true,
        'show_read_more' => true,
        'hero_image' => '',
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

function voidairo_head_meta() {
    $desc = voidairo_meta_description();
    $title = wp_get_document_title();
    $url = is_singular() ? get_permalink() : home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''));
    $image = voidairo_primary_image_url();
    echo "\n<meta name=\"description\" content=\"" . esc_attr($desc) . "\">\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
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

function voidairo_post_meta() {
    echo '<div class="post-meta">';
    echo '<time datetime="' . esc_attr(get_the_date(DATE_W3C)) . '">' . esc_html(get_the_date()) . '</time>';
    echo '<span aria-hidden="true">•</span><span>' . esc_html(get_the_author()) . '</span>';
    $cats = get_the_category_list(', ');
    if ($cats) { echo '<span aria-hidden="true">•</span><span>' . wp_kses_post($cats) . '</span>'; }
    if (voidairo_option('views')) { echo '<span aria-hidden="true">•</span><span>' . sprintf(esc_html__('%s views', 'voidairo'), esc_html(number_format_i18n(voidairo_get_views()))) . '</span>'; }
    if (voidairo_option('likes')) { echo '<span aria-hidden="true">•</span><span>' . sprintf(esc_html__('%s likes', 'voidairo'), esc_html(number_format_i18n(voidairo_get_likes()))) . '</span>'; }
    if (!post_password_required() && comments_open()) { echo '<span aria-hidden="true">•</span><span>'; comments_popup_link(__('No comments', 'voidairo'), __('1 comment', 'voidairo'), __('% comments', 'voidairo')); echo '</span>'; }
    echo '</div>';
}

function voidairo_like_button($post_id = null) {
    if (!voidairo_option('likes')) { return; }
    $post_id = $post_id ?: get_the_ID();
    echo '<button class="voidairo-like" type="button" data-post-id="' . esc_attr($post_id) . '" aria-label="' . esc_attr__('Like this post', 'voidairo') . '"><span aria-hidden="true">♡</span><strong>' . esc_html(number_format_i18n(voidairo_get_likes($post_id))) . '</strong></button>';
}

function voidairo_ajax_like() {
    check_ajax_referer('voidairo_like', 'nonce');
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    if (!$post_id || 'publish' !== get_post_status($post_id)) { wp_send_json_error(array('message' => __('Invalid post.', 'voidairo')), 400); }
    $likes = voidairo_get_likes($post_id) + 1;
    update_post_meta($post_id, '_voidairo_likes', $likes);
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

function voidairo_sanitize_options($input) {
    $input = is_array($input) ? $input : array();
    $defaults = voidairo_defaults();
    $output = array();
    $boolean_keys = array('serif', 'auto_dark', 'pjax', 'ajax_comments', 'toc', 'likes', 'views', 'mac_code', 'show_card_meta', 'show_read_more');
    foreach ($boolean_keys as $key) { $output[$key] = !empty($input[$key]); }
    $output['hero_image'] = isset($input['hero_image']) ? esc_url_raw(trim(wp_unslash($input['hero_image']))) : '';
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

function voidairo_settings_page() {
    if (!current_user_can('edit_theme_options')) { return; }
    $opts = voidairo_options();
    $checks = array(
        'show_card_meta' => '首页/列表文章显示元信息（日期、作者、分类、浏览、点赞、评论）',
        'show_read_more' => '首页/列表文章显示 Read more 按钮',
        'auto_dark' => '默认跟随系统深色模式',
        'pjax' => '启用 PJAX 页面无刷新切换（如果导航异常可关闭）',
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
    echo '<div class="wrap"><h1>VOIDairo 主题设置</h1><p>参考 VOID 的核心设置重新整理：顶部大图、颜色/字体、PJAX、目录、评论、代码块和首页显示项。</p><form method="post" action="options.php">';
    settings_fields('voidairo_settings');
    echo '<h2>首页顶部大图</h2><table class="form-table" role="presentation"><tbody>';
    echo '<tr><th scope="row"><label for="voidairo_hero_image">首页顶部大图 URL</label></th><td><input id="voidairo_hero_image" class="regular-text" type="url" name="voidairo_options[hero_image]" value="' . esc_attr($opts['hero_image']) . '" placeholder="https://example.com/banner.jpg"><p class="description">留空时使用渐变背景；可以填写图片或随机图 API。</p></td></tr>';
    echo '<tr><th scope="row"><label for="voidairo_hero_title">首页顶部大标题</label></th><td><input id="voidairo_hero_title" class="regular-text" type="text" name="voidairo_options[hero_title]" value="' . esc_attr($opts['hero_title']) . '" placeholder="' . esc_attr(get_bloginfo('name')) . '"></td></tr>';
    echo '<tr><th scope="row"><label for="voidairo_hero_subtitle">首页顶部小标题</label></th><td><input id="voidairo_hero_subtitle" class="regular-text" type="text" name="voidairo_options[hero_subtitle]" value="' . esc_attr($opts['hero_subtitle']) . '" placeholder="' . esc_attr(get_bloginfo('description')) . '"></td></tr>';
    echo '</tbody></table>';
    echo '<h2>字体预设</h2><table class="form-table" role="presentation"><tbody><tr><th scope="row">预设字体</th><td><select name="voidairo_options[font_preset]">';
    foreach ($font_presets as $key => $label) { echo '<option value="' . esc_attr($key) . '" ' . selected($opts['font_preset'], $key, false) . '>' . esc_html($label) . '</option>'; }
    echo '</select></td></tr></tbody></table>';
    echo '<h2>显示与功能开关</h2><table class="form-table" role="presentation"><tbody>';
    foreach ($checks as $key => $label) {
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td><label><input type="checkbox" name="voidairo_options[' . esc_attr($key) . ']" value="1" ' . checked(!empty($opts[$key]), true, false) . '> 启用</label></td></tr>';
    }
    echo '</tbody></table>';
    submit_button('保存设置');
    echo '</form></div>';
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
