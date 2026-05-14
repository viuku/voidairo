<?php get_header(); ?>
<main id="primary" class="container" role="main">
  <header class="page-header"><h1 class="page-title"><?php printf(esc_html__('Search: %s', 'voidairo'), '<span>' . esc_html(get_search_query()) . '</span>'); ?></h1><?php get_search_form(); ?></header>
  <div class="layout"><div class="content-area">
  <?php if (have_posts()) : ?><div class="post-grid"><?php while (have_posts()) : the_post(); get_template_part('template-parts/content', 'card'); endwhile; ?></div><?php the_posts_pagination(array('mid_size' => 2)); else : get_template_part('template-parts/content', 'none'); endif; ?>
  </div><?php get_sidebar(); ?></div>
</main>
<?php get_footer(); ?>
