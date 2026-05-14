<?php get_header(); ?>
<section class="hero" aria-labelledby="hero-title">
  <div class="hero-card">
    <h1 id="hero-title"><?php echo is_home() && !is_front_page() ? esc_html(single_post_title('', false)) : esc_html(get_bloginfo('name')); ?></h1>
    <p><?php echo esc_html(get_bloginfo('description') ?: __('A quiet place for writing, reading and thinking.', 'voidairo')); ?></p>
    <div class="hero-meta"><span class="pill"><?php esc_html_e('Responsive', 'voidairo'); ?></span><span class="pill"><?php esc_html_e('Dark mode', 'voidairo'); ?></span><span class="pill"><?php esc_html_e('SEO ready', 'voidairo'); ?></span></div>
  </div>
</section>
<main id="primary" class="container" role="main">
  <div class="layout">
    <div class="content-area">
      <?php if (have_posts()) : ?>
        <div class="post-grid">
          <?php while (have_posts()) : the_post(); get_template_part('template-parts/content', 'card'); endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 2, 'prev_text' => __('Previous', 'voidairo'), 'next_text' => __('Next', 'voidairo'))); ?>
      <?php else : get_template_part('template-parts/content', 'none'); endif; ?>
    </div>
    <?php get_sidebar(); ?>
  </div>
</main>
<?php get_footer(); ?>
