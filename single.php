<?php get_header(); ?>
<main id="primary" class="container" role="main">
<?php while (have_posts()) : the_post(); ?>
  <?php if (function_exists('voidairo_option') && voidairo_option('toc')) : ?>
    <aside class="toc-panel" aria-label="<?php esc_attr_e('Table of contents', 'voidairo'); ?>"><div class="toc-panel__title"><?php esc_html_e('Contents', 'voidairo'); ?></div><nav class="toc-list"></nav></aside>
  <?php endif; ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class('article'); ?> itemscope itemtype="https://schema.org/BlogPosting">
    <?php if (has_post_thumbnail()) : ?><div class="article__cover"><?php voidairo_thumbnail('voidairo-cover', ''); ?></div><?php endif; ?>
    <header class="article__header">
      <h1 class="article__title" itemprop="headline"><?php the_title(); ?></h1>
      <?php voidairo_post_meta(); ?>
      <div class="article-actions"><?php voidairo_like_button(); ?></div>
    </header>
    <div class="entry-content" itemprop="articleBody">
      <?php the_content(); wp_link_pages(array('before' => '<div class="page-links">' . esc_html__('Pages:', 'voidairo'), 'after' => '</div>')); ?>
      <?php if (has_tag()) : ?><div class="post-tags"><?php the_tags('', ' ', ''); ?></div><?php endif; ?>
      <div class="author-box"><?php echo get_avatar(get_the_author_meta('ID'), 56); ?><div><strong><?php the_author(); ?></strong><br><span><?php echo esc_html(get_the_author_meta('description')); ?></span></div></div>
    </div>
  </article>
  <nav class="post-nav" aria-label="<?php esc_attr_e('Post navigation', 'voidairo'); ?>"><?php previous_post_link('%link', '← %title'); next_post_link('%link', '%title →'); ?></nav>
  <?php if (comments_open() || get_comments_number()) { comments_template(); } ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
