<?php if (!defined('ABSPATH')) { exit; } $has_thumb = has_post_thumbnail(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post-card' . ($has_thumb ? ' has-thumbnail' : '')); ?>>
  <?php if (is_sticky()) : ?><span class="sticky-badge"><?php esc_html_e('Pinned', 'voidairo'); ?></span><?php endif; ?>
  <?php if ($has_thumb) : ?>
    <a class="post-card__thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php voidairo_thumbnail('voidairo-card', 'post-card__image'); ?></a>
  <?php endif; ?>
  <div class="post-card__body">
    <?php if (voidairo_option('show_card_meta')) { voidairo_post_meta(); } ?>
    <h2 class="post-card__title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
    <p class="post-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
    <?php if (voidairo_option('show_read_more')) : ?><a class="read-more" href="<?php the_permalink(); ?>"><?php esc_html_e('Read more', 'voidairo'); ?> →</a><?php endif; ?>
  </div>
</article>
