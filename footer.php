<?php if (!defined('ABSPATH')) { exit; } ?>
<footer class="site-footer" role="contentinfo">
  <div class="site-footer__inner">
    <div>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('Powered by WordPress.', 'voidairo'); ?></div>
    <div><?php esc_html_e('VOID-like calm, rebuilt light and fast.', 'voidairo'); ?></div>
  </div>
</footer>
<button class="back-to-top icon-button" type="button" aria-label="<?php esc_attr_e('Back to top', 'voidairo'); ?>">↑</button>
<?php wp_footer(); ?>
</body>
</html>
