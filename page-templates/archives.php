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
      $posts = get_posts(array('posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids'));
      $year = '';
      foreach ($posts as $post_id) {
          $post_year = get_the_date('Y', $post_id);
          if ($post_year !== $year) {
              if ($year !== '') { echo '</ul>'; }
              $year = $post_year;
              echo '<h2>' . esc_html($year) . '</h2><ul class="archive-list">';
          }
          echo '<li><time datetime="' . esc_attr(get_the_date('Y-m-d', $post_id)) . '">' . esc_html(get_the_date('m-d', $post_id)) . '</time><a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></li>';
      }
      if ($year !== '') { echo '</ul>'; }
      ?>
    </div>
  </article>
</main>
<?php get_footer(); ?>
