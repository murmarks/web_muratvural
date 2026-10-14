<?php do_action( 'thim_above_footer_area' ); get_template_part('inc/widgets/layout/footer', 'default'); ?>

<footer id="colophon" class="<?php thim_footer_class(); ?>">
	<?php if ( is_active_sidebar( 'footer' ) ) : ?>
        <div class="footer">
            <div class="container">
                <div class="row">
					<?php dynamic_sidebar( 'footer' ); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>

	<?php do_action( 'thim_copyright_area' ); ?>

</footer><!-- #colophon -->
</div><!--end main-content-->

<?php do_action( 'thim_end_content_pusher' ); ?>

</div><!-- end content-pusher-->

<?php do_action( 'thim_end_wrapper_container' ); ?>


</div><!-- end wrapper-container -->

<?php wp_footer(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tüm login ve register linklerini hedefle
    const loginLinks = document.querySelectorAll('a.login, a.register');

    loginLinks.forEach(function(link) {
        // Olası popup sınıfı ve olaylarını temizle
        link.classList.remove('js-show-popup');
        link.removeAttribute('data-popup');
        link.onclick = null;

        // Bu linklere tıklanırsa popup değil doğrudan yönlendirme olsun
        link.addEventListener('click', function (e) {
            e.stopImmediatePropagation(); // Diğer JS event'leri engelle
        });
    });
});
</script>

</body>
</html>
