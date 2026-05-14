<?php get_header(); ?>
<main id="primary" class="container" role="main">
<?php while (have_posts()) : the_post(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class('article'); ?>>
    <?php if (has_post_thumbnail()) : ?><div class="article__cover"><?php voidairo_thumbnail('voidairo-cover', ''); ?></div><?php endif; ?>
    <header class="article__header"><h1 class="article__title"><?php the_title(); ?></h1></header>
    <div class="entry-content"><?php the_content(); wp_link_pages(array('before' => '<div class="page-links">' . esc_html__('Pages:', 'voidairo'), 'after' => '</div>')); ?></div>
  </article>
  <?php if (comments_open() || get_comments_number()) { comments_template(); } ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
