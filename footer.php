<?php if (!defined('ABSPATH')) { exit; } ?>
<footer class="site-footer" role="contentinfo">
  <div class="site-footer__inner">
    <div>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>. <?php esc_html_e('Powered by WordPress.', 'voidairo'); ?></div>
    <div class="site-footer__theme">Theme: <strong>VOIDairo</strong> · <a href="https://github.com/viuku/voidairo" target="_blank" rel="noopener noreferrer">GitHub</a></div>
  </div>
</footer>
<button class="back-to-top icon-button" type="button" aria-label="<?php esc_attr_e('Back to top', 'voidairo'); ?>">↑</button>
<?php wp_footer(); ?>
</body>
</html>
