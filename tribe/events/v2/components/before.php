<?php
/**
 * Component: Before Events (theme override)
 *
 * Always output the before-html wrapper so theme-injected hero and intro (via tribe_events_before_html filter) display.
 *
 * @var string $before_events HTML to print before the Events (includes hero + intro from Theme Settings > Events).
 */
?>
<div class="tribe-events-before-html">
	<?php if ( ! empty( $before_events ) ) : ?>
		<?php echo $before_events; ?>
	<?php endif; ?>
</div>
