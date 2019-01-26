<div class="wrap">
    <h2><?php _e( 'Table', 'ignite' ); ?></h2>

    <form id="outbox-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
		<?php $list_table->search_box( __( 'Search', 'ignite' ), 'search_id' ); ?>
		<?php $list_table->display(); ?>
    </form>
</div>