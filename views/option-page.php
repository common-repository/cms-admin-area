<div class="wrap admin-area-wrpper">
	<div class="icon32" id="icon-tools"></div>

	<h2><?php _e( 'Admin Area Settings', 'cms-admin-area' ); ?></h2>


	<h2 class="nav-tab-wrapper">
		<a href="<?php echo '?page=' . $this->urlSlug . '&tab=' . $this->display_settings_key ?>" class="nav-tab <?php Admin_Area_Helper::currentTabClass( $this->activeTab, $this->display_settings_key ) ?>"><?php _e( 'Visual Options', 'cms-admin-area' )?></a>
		<a href="<?php echo '?page=' . $this->urlSlug . '&tab=' . $this->site_settings_key ?>" class="nav-tab <?php Admin_Area_Helper::currentTabClass( $this->activeTab, $this->site_settings_key ) ?>"><?php _e( 'Site Settings', 'cms-admin-area' )?></a>
		<a href="<?php echo '?page=' . $this->urlSlug . '&tab=' . $this->extend_configuration_key ?>" class="nav-tab <?php Admin_Area_Helper::currentTabClass( $this->activeTab, $this->extend_configuration_key ) ?>"><?php _e( 'Extend Configuration', 'cms-admin-area' )?></a>

	</h2>

	<form method="post" action="options.php">
		<?php
		if ( $this->activeTab == $this->display_settings_key ) {
			?>
			<?php settings_fields( $this->display_settings_key ); ?>
			<?php do_settings_sections( $this->display_settings_key ); ?>
			<?php
		}
		?>

		<?php
		if ( $this->activeTab == $this->site_settings_key ) {
			?>
			<?php settings_fields( $this->site_settings_key ); ?>
			<?php do_settings_sections( $this->site_settings_key ); ?>
			<?php
		}
		?>
		<?php
		if ( $this->activeTab == $this->extend_configuration_key ) {
			?>
			<?php settings_fields( $this->extend_configuration_key ); ?>
			<?php do_settings_sections( $this->extend_configuration_key ); ?>
			<?php
		}
		?>

		<?php submit_button(); ?>

	</form>
</div>
