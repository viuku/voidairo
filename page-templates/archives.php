<?php
/**
 * Template Name: Archives
 * Description: A clean yearly archive page inspired by VOID's archive view.
 */
get_header(); ?>
<main id="primary" class="container" role="main">
  <article class="article">
    <header class="article__header"><h1 class="article__title"><?php the_title(); ?></h1></header>
    <div class="entry-content archive-timeline">
      <?php
      $archive_html = get_transient('voidairo_archives_html');
      if (false === $archive_html) {
          $posts = get_posts(array(
              'posts_per_page' => -1,
              'post_status' => 'publish',
              'orderby' => 'date',
              'order' => 'DESC',
              'fields' => 'ids',
              'no_found_rows' => true,
              'update_post_meta_cache' => false,
              'update_post_term_cache' => false,
              'ignore_sticky_posts' => true,
          ));
          $year = '';
          ob_start();
          foreach ($posts as $post_id) {
              $post_year = get_the_date('Y', $post_id);
              if ($post_year !== $year) {
                  if ($year !== '') { echo '</ul>'; }
                  $year = $post_year;
                  echo '<h2>' . esc_html($year) . '</h2><ul class="archive-list">';
              }
              echo '<li><time datetime="' . esc_attr(get_the_date('Y-m-d', $post_id)) . '">' . esc_html(get_the_date('m-d', $post_id)) . '</time><a class="archive-list__link no-pjax" data-no-pjax href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></li>';
          }
          if ($year !== '') { echo '</ul>'; }
          $archive_html = ob_get_clean();
          set_transient('voidairo_archives_html', $archive_html, HOUR_IN_SECONDS * 12);
      }
      echo $archive_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Cached HTML is generated above with escaped URLs/text.
      ?>
    </div>
  </article>
</main>
<?php get_footer(); ?>
