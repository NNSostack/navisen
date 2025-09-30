<!-- Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		  <h4 class="modal-title"><?php _e('Search in Site','gabfire'); ?></h4>
		</div>
		<div class="modal-body">
			<form action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<div class="input-prepend">
					<label><?php _e('To search in site, type your keyword and hit enter', 'gabfire'); ?></label>
					<input type="text" name="s" class="form-control" placeholder="<?php _e('Type keyword and hit enter', 'gabfire'); ?>">
				</div>
			</form>	
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close','gabfire'); ?></button>
		</div>
	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->