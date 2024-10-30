<?php
class Admin_Area_Components {


	//add main options widget
	public function dashboard_cms_widget_first() {
		// Display whatever it is you want to show
		?>
	<ul id="short-menu-list">
		<li id="short-menu-link"><a href="nav-menus.php"><?php echo __( 'Menus' ) ?></a></li>
		<li id="short-widget-link"><a href="widgets.php"><?php echo __( 'Widgets' ) ?></a></li>
		<li id="short-theme-link"><a href="themes.php"><?php echo __( 'Themes' ) ?></a></li>
	</ul>
	<div class="clear"></div>
	<?php

	}

 //add statistic options widget
	public function dashboard_cms_widget_statistic() {
		$num_posts = wp_count_posts( 'post' );
		$num_pages = wp_count_posts( 'page' );

		$num_cats = wp_count_terms( 'category' );

		$num_tags = wp_count_terms( 'post_tag' );
		?>
	<ul class="statistic-list">
		<li>

			<h4><?php echo __( 'Pages', 'cms-admin-area' ) ?> :</h4>

			<p><?php echo $num_pages->publish; ?></p></li>
		<li><h4><?php echo __( 'Posts', 'cms-admin-area' ) ?> :</h4>

			<p><?php echo $num_posts->publish; ?></p></li>
	</ul>
	<?php
		/*list posts*/
		$args = array( 'post_type'      => 'page',


									 'posts_per_page' => 5,
		);
		$posts = new WP_Query( $args );

		?>
	<div class="page-list-box">
		<h4><?php echo __( 'Post list', 'cms-admin-area' ) ?></h4>
		<ul class="post-list">
			<?php

			while ( $posts->have_posts() ) : $posts->the_post();
				?>
				<li>
					<h5><a href="<?php echo get_permalink();?>"><?php the_title(); ?></a></h5>
				</li>
				<?php
			endwhile;
			?>
		</ul>
	</div>
	<ul class="menu-buttons">
		<li><a href="post-new.php?post_type=post"
					 class="button-primary"><?php echo __( 'Add new post', 'cms-admin-area' ) ?></a></li>
		<li><a href="post-new.php?post_type=page"
					 class="button-primary"><?php echo __( 'Add new page', 'cms-admin-area' ) ?></a></li>

	</ul>


	<?php

	}

}
