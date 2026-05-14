<?php get_header(); ?>
<main id="primary" class="container" role="main">
  <header class="page-header">
    <h1 class="page-title"><?php the_archive_title(); ?></h1>
    <?php the_archive_description('<div class="archive-description">', '</div>'); ?>
  </header>
  <div class="layout">
    <div class="content-area">
      <?php if (have_posts()) : ?><div class="post-grid"><?php while (have_posts()) : the_post(); get_template_part('template-parts/content', 'card'); endwhile; ?></div><?php the_posts_pagination(array('mid_size' => 2)); else : get_template_part('template-parts/content', 'none'); endif; ?>
    </div>
    <?php get_sidebar(); ?>
  </div>
</main>
<?php get_footer(); ?>
