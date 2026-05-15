<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'voidairo'); ?></a>
<header class="site-header" role="banner">
  <div class="site-header__inner">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
      <?php
      $custom_logo_id = get_theme_mod('custom_logo');
      if ($custom_logo_id) {
          echo '<span class="brand__logo">' . wp_get_attachment_image($custom_logo_id, 'full', false, array('loading' => 'eager', 'decoding' => 'async')) . '</span>';
      }
      ?>
      <span class="brand__text"><span class="brand__title"><?php echo esc_html(get_bloginfo('name')); ?></span></span>
    </a>
    <button class="nav-toggle icon-button" type="button" aria-controls="site-navigation" aria-expanded="false" aria-label="<?php esc_attr_e('Open menu', 'voidairo'); ?>"><svg class="fa-bars" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M0 96C0 78.3 14.3 64 32 64h384c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zm0 160c0-17.7 14.3-32 32-32h384c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zm448 160c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32h384c17.7 0 32 14.3 32 32z"/></svg></button>
    <nav id="site-navigation" class="site-nav" aria-label="<?php esc_attr_e('Primary menu', 'voidairo'); ?>">
      <?php wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'primary-menu', 'container' => false, 'fallback_cb' => 'voidairo_fallback_menu')); ?>
    </nav>
    <div class="header-actions"><button class="theme-toggle icon-button" type="button" aria-label="<?php esc_attr_e('Toggle dark mode', 'voidairo'); ?>">◐</button></div>
  </div>
</header>
