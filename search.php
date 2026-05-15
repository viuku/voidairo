<?php get_header(); ?>
<main id="primary" class="container" role="main">
  <header class="page-header"><h1 class="page-title"><?php printf(esc_html__('Search: %s', 'voidairo'), '<span>' . esc_html(get_search_query()) . '</span>'); ?></h1><?php get_search_form(); ?></header>
  <div class="layout"><div class="content-area">
  <?php if (have_posts()) : ?>
    <div class="search-results-list">
      <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('search-result-card'); ?>>
          <h2 class="search-result-title"><a href="<?php the_permalink(); ?>"><?php echo voidairo_highlight_search_terms(get_the_title()); ?></a></h2>
          <?php voidairo_post_meta(); ?>
          <div class="search-snippets"><?php echo voidairo_search_snippets(get_the_ID(), get_search_query(), 3); ?></div>
        </article>
      <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(array('mid_size' => 2)); ?>
  <?php else : get_template_part('template-parts/content', 'none'); endif; ?>
  </div><?php get_sidebar(); ?></div>
</main>
<?php get_footer(); ?>
