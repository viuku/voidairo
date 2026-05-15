<?php get_header(); ?>
<?php
$hero_image = function_exists('voidairo_option_value') ? voidairo_option_value('hero_image', '') : '';
$hero_title = function_exists('voidairo_option_value') && voidairo_option_value('hero_title', '') ? voidairo_option_value('hero_title', '') : (is_home() && !is_front_page() ? single_post_title('', false) : get_bloginfo('name'));
$hero_subtitle = function_exists('voidairo_option_value') && voidairo_option_value('hero_subtitle', '') ? voidairo_option_value('hero_subtitle', '') : (get_bloginfo('description') ?: __('A quiet place for writing, reading and thinking.', 'voidairo'));
?>
<section class="hero" aria-labelledby="hero-title">
  <div class="hero-card<?php echo $hero_image ? ' has-hero-image' : ''; ?>"<?php echo $hero_image ? ' style="--hero-image:url(' . esc_url($hero_image) . ')"' : ''; ?>>
    <div class="hero-card__content">
      <h1 id="hero-title"><?php echo esc_html($hero_title); ?></h1>
      <p><?php echo esc_html($hero_subtitle); ?></p>
    </div>
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
