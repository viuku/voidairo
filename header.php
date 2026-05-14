<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
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
      <span class="brand__text"><span class="brand__title"><?php bloginfo('name'); ?></span><span class="brand__desc"><?php bloginfo('description'); ?></span></span>
    </a>
    <button class="nav-toggle icon-button" type="button" aria-controls="site-navigation" aria-expanded="false">☰</button>
    <nav id="site-navigation" class="site-nav" aria-label="<?php esc_attr_e('Primary menu', 'voidairo'); ?>">
      <?php wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'primary-menu', 'container' => false, 'fallback_cb' => 'voidairo_fallback_menu')); ?>
    </nav>
    <div class="header-actions"><button class="theme-toggle icon-button" type="button" aria-label="<?php esc_attr_e('Toggle dark mode', 'voidairo'); ?>">◐</button></div>
  </div>
</header>
