<?php if (!defined('ABSPATH')) { exit; } ?>
<aside class="widget-area" role="complementary" aria-label="<?php esc_attr_e('Sidebar', 'voidairo'); ?>">
<?php if (is_active_sidebar('sidebar-1')) { dynamic_sidebar('sidebar-1'); } else { ?>
  <?php if (!is_search()) : ?><section class="widget"><h2 class="widget-title"><?php esc_html_e('Search', 'voidairo'); ?></h2><?php get_search_form(); ?></section><?php endif; ?>
  <section class="widget"><h2 class="widget-title"><?php esc_html_e('Recent Posts', 'voidairo'); ?></h2><ul><?php wp_get_archives(array('type' => 'postbypost', 'limit' => 5)); ?></ul></section>
  <section class="widget"><h2 class="widget-title"><?php esc_html_e('Archives', 'voidairo'); ?></h2><ul><?php wp_get_archives(array('type' => 'monthly', 'limit' => 8)); ?></ul></section>
<?php } ?>
</aside>
