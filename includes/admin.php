<?php
class postcs_Admin extends postcsCls {

	public function __construct() {
		/*Menu pages*/
		add_action('admin_menu', array( $this, 'postcs_menu' ));

		/*Form options*/
		add_action( 'admin_init', array( $this, 'postcs_register_option_settings' ));

		/*Exclude JS Form options*/
		add_action( 'admin_init', array( $this, 'postcs_register_js_settings' ));

		/*Setting link*/
		add_filter( 'plugin_action_links_' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/post-cs.php', array( $this, 'postcs_setting_link' ) );
	}

	/*Add setting link*/
	function postcs_setting_link( $links ) {
		return array_merge( array(
			'<a href="' . admin_url( 'admin.php?page=postcs' ) . '">' . __( 'Setting', 'post-carousel-and-slider' ) . '</a>',
		), $links );
	}

	/*Add menu*/
	public function postcs_menu() {
		add_menu_page( __( 'Post carousel & slider', 'post-carousel-and-slider' ), __( 'Post carousel & slider', 'post-carousel-and-slider' ), 'manage_options', 'postcs', array( $this, 'postcs_page' ) );
		add_submenu_page( 'postcs', __( 'Common setting', 'post-carousel-and-slider' ), __( 'Common setting', 'post-carousel-and-slider' ), 'manage_options', 'postcssetting', array( $this, 'postcs_page_setting' ) );
	}

	/*Common option for all slider*/
	public function postcs_register_option_settings() {
		register_setting( 'postcs-settings-group', 'ps_setting' );
	}

	/*Exclude JS option*/
	public function postcs_register_js_settings() {
		register_setting( 'postcs-js-settings', 'ps_setting_mousewheel' );
		register_setting( 'postcs-js-settings', 'ps_setting_swipe' );
	}

	/*Admin listing & slider setting*/
	public function postcs_page() {
		?>
		<div id="<?php echo esc_attr( 'psbox' ); ?>" class="<?php echo esc_attr( 'wrap' ); ?>">
			<a style="margin-top:15px;" class="<?php echo esc_attr( 'button button-primary alain-right' ); ?>" href="<?php echo admin_url('/admin.php?page=postcs'); ?>"><?php echo esc_html('All slider and carousel'); ?></a>	
			<h1><?php echo esc_html( 'Post carousel & slider' ); ?></h1>
			<hr>
			<?php
			/*Slider setting form*/
			if(isset($_GET['slider_no'])) {
				$slider_no = sanitize_text_field($_GET['slider_no']);
				/*Get form data (common option for all slider)*/
				$get_ps_set = get_option("ps_setting");
				if($get_ps_set && key($get_ps_set) && key($get_ps_set) == $slider_no) {
					if(get_option( "ps_setting{$slider_no}" )) {
						/*Update slider setting*/
						update_option( "ps_setting{$slider_no}", $get_ps_set[key($get_ps_set)] );
					} else {
						/*Add slider setting*/
						delete_option("ps_setting{$slider_no}");
						add_option("ps_setting{$slider_no}", $get_ps_set[key($get_ps_set)], "", "yes" );									
					}
				}
				
				/*Get slider setting*/
				$get_ps_setting = get_option( "ps_setting{$slider_no}" );

				/*Delete common option*/
				delete_option("ps_setting");

				/*Design template*/
				$des_opt = "";
				if($get_ps_setting['design_option']) {
					$get_do = strtolower($get_ps_setting['design_option']);
					$get_do = str_replace(" ", "", $get_do);
					$des_opt = ' design_option="'.$get_do.'"';
				}
				
				/*Display shortcodes*/
				$sortcodeforcms = "[post-cs id=".$slider_no."]";
				$sortcodeforphp = "&lt;?php echo do_shortcode('[post-cs id=".$slider_no."]'); ?&gt;";
				
				/*Preview*/
				if(isset($_GET['preview'])) {
					$preview = sanitize_text_field($_GET['preview']);
					?>
					<div class="content-box top">
						<a class="<?php echo esc_attr( 'button' ); ?>" href="<?php echo admin_url('/admin.php?page=postcs&slider_no='.$slider_no.''); ?>">
							<?php echo esc_html('Back'); ?>
						</a>
						<br><br>
						<?php echo do_shortcode('[post-cs id='.$preview.' '.$des_opt.']'); ?>
					</div>
					<?php					
				} else {
					?>
					<div class="content-box top">
						<a class="<?php echo esc_attr( 'button alain-right' ); ?>" href="<?php echo admin_url('/admin.php?page=postcs&slider_no='.$slider_no.'&preview='.$slider_no.''); ?>"><?php echo esc_html('Preview'); ?></a>
						<pre>
							<strong><?php echo esc_html( 'Shortcode for CMS' ) ?> : </strong> <?php echo $sortcodeforcms; ?>
						</pre>
						<pre>
							<strong><?php echo esc_html( 'Shortcode for PHP' ) ?> : </strong> <?php echo $sortcodeforphp; ?>
						</pre>
					</div>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'postcs-settings-group' );
						do_settings_sections( 'postcs-settings-group' );
						?>
						<table class="<?php echo esc_attr( 'form-table psform' ); ?>">
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Slider or carousel name' ); ?></th>
								<td>
									<?php
									$get_enter_name = '';
									if(isset($get_ps_setting['enter_name'])) {
										$get_enter_name = $get_ps_setting['enter_name'];	
									}
									?>
									<input type="text" name="ps_setting[<?php echo $slider_no; ?>][enter_name]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_enter_name); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Enter a unique name for your slider or carousel' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Select type' ); ?></th>
								<td>
									<?php
									$get_post_type = 'any';
									if(isset($get_ps_setting['post_type'])) {
										$get_post_type = $get_ps_setting['post_type'];	
									}
									?>
									<select name="ps_setting[<?php echo $slider_no; ?>][post_type]" class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option <?php if($get_post_type == 'any') echo 'selected'; ?> value="any"><?php echo esc_html( 'Any' ); ?></option>
										<?php
										$args = array(
											'public'=> true
										);
										foreach ( get_post_types( $args, 'names' ) as $post_type ) {
											if($post_type == 'attachment' || $post_type == 'revision' || $post_type == 'nav_menu_item' || $post_type == 'custom_css' || $post_type == 'customize_changeset' || $post_type == 'acf' || $post_type == 'product_variation' || $post_type == 'shop_order' || $post_type == 'shop_order_refund' || $post_type == 'shop_webhook') {

											} else { ?>
												<option <?php if($get_post_type == $post_type) echo 'selected'; ?> value="<?php echo esc_attr($post_type); ?>"><?php echo $post_type; ?></option>
											<?php }
										} ?>								
									</select>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'The type you select here will be displayed in the slider or carousel' ); ?>
									</p>				
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Exclude some posts' ); ?></th>
								<td>
									<?php
									$get_exclude_post = '';
									if(isset($get_ps_setting['exclude_post'])) {
										$get_exclude_post = $get_ps_setting['exclude_post'];	
									}
									?>
									<input type="text" name="ps_setting[<?php echo $slider_no; ?>][exclude_post]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_exclude_post); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Add comma-separated post ids like 1,2,3' ); ?>
										<br>
										<?php echo esc_html( 'You can add multiple post Ids here, those posts will not be displayed in the slider or carousel.' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Number of posts' ); ?></th>
								<td>
									<?php
									$get_posts_per_page = 1;
									if(isset($get_ps_setting['posts_per_page'])) {
										$get_posts_per_page = $get_ps_setting['posts_per_page'];	
									}
									?>
									<input type="number" name="ps_setting[<?php echo $slider_no; ?>][posts_per_page]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_posts_per_page); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'The number of posts you want to display.' ); ?>
										<br>
										<?php echo esc_html( 'For example: for the slider, you can set 1 and for the carousel, you can set 2 or more.' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Order' ); ?></th>
								<td>
									<?php
									$get_order = 'DESC';
									if(isset($get_ps_setting['order'])) {
										$get_order = $get_ps_setting['order'];	
									}
									?>
									<select name="ps_setting[<?php echo $slider_no; ?>][order]" class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option <?php if($get_order == 'DESC') echo 'selected'; ?> value="<?php echo esc_attr( 'DESC' ); ?>"><?php echo esc_html( 'DESC' ); ?></option>
										<option <?php if($get_order == 'ASC') echo 'selected'; ?> value="<?php echo esc_attr( 'ASC' ); ?>"><?php echo esc_html( 'ASC' ); ?></option>
									</select>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Choose ascending or descending order' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Order by' ); ?></th>
								<td>
									<?php
									$get_orderby = 'date';
									if(isset($get_ps_setting['orderby'])) {
										$get_orderby = $get_ps_setting['orderby'];
									}
									?>
									<select name="ps_setting[<?php echo $slider_no; ?>][orderby]" class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option <?php if($get_orderby == 'date') echo 'selected'; ?> value="<?php echo esc_attr( 'date' ); ?>"><?php echo esc_html( 'date' ); ?></option>
										<option <?php if($get_orderby == 'ID') echo 'selected'; ?> value="<?php echo esc_attr( 'ID' ); ?>"><?php echo esc_html( 'ID' ); ?></option>
										<option <?php if($get_orderby == 'title') echo 'selected'; ?> value="<?php echo esc_attr( 'title' ); ?>"><?php echo esc_html( 'title' ); ?></option>
										<option <?php if($get_orderby == 'name') echo 'selected'; ?> value="<?php echo esc_attr( 'name' ); ?>"><?php echo esc_html( 'name' ); ?></option>
									</select>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Sort all posts by ID or title or name or date' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Posts by category' ); ?></th>
								<td>
									<?php
									$categories = get_categories( array(
									    'orderby' => 'name',
									    'order'   => 'ASC'
									) );
									$get_cat = false;
									if(isset($get_ps_setting['cat'])) {
										$get_cat = $get_ps_setting['cat'];	
									}
									?>
									<select name="ps_setting[<?php echo $slider_no; ?>][cat][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
									<option value="">Select category</option>
									<?php foreach($categories as $key => $val) { ?>
										<option <?php if ($get_cat && in_array($val->term_id, $get_cat)) { echo "selected"; } ?> value="<?php echo $val->term_id; ?>"><?php echo $val->name; ?></option>
									<?php } ?>
									</select>
									<p class="description">
										<?php echo esc_html( 'Only selected category posts will be displayed in slider or carousel' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Posts exclude by category' ); ?></th>
								<td>
									<?php
									$get_excat = false;
									if(isset($get_ps_setting['excat'])) {
										$get_excat = $get_ps_setting['excat'];	
									}
									?>
									<select name="ps_setting[<?php echo $slider_no; ?>][excat][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
									<option value="">Select category</option>
									<?php foreach($categories as $key => $val) { ?>
										<option <?php if ($get_excat && in_array($val->term_id, $get_excat)) { echo "selected"; } ?> value="<?php echo $val->term_id; ?>"><?php echo $val->name; ?></option>
									<?php } ?>
									</select>
									<p class="description">
										<?php echo esc_html( 'Selected category posts will not be displayed in the slider or carousel' ); ?>
									</p>
								</td>
							</tr>
							<?php
							$args = array(
								'public' => true,
								'_builtin' => false
							);
							$taxonomies = get_taxonomies($args);
							if (count($taxonomies)) {
							?>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Posts by taxonomy' ); ?></th>
								<td>
									<?php
									foreach($taxonomies as $key => $val) {
										if($key == 'category' || $key == 'post_tag' || $key == 'nav_menu' || $key == 'link_category' || $key == 'post_format') {
										} else {
											$get_tax = false;
											if(isset($get_ps_setting['tax'])) {
												$get_tax = $get_ps_setting['tax'];	
											}
											$getterms = get_terms( array(
											    'taxonomy' => $key,
											    'hide_empty' => false,
											) );
											if(count($getterms)) {
											?>
											<select rows="10" cols="50" name="ps_setting[<?php echo $slider_no; ?>][tax][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
											<option value=""><?php echo esc_html( 'Select Taxonomy' ); ?></option>
											<?php foreach($getterms as $ke => $vl) { ?>
												<option <?php if ($get_tax && in_array($key . "?" . $vl->slug, $get_tax)) { echo "selected"; } ?> value="<?php echo $key . "?" . $vl->slug; ?>"><?php echo $vl->taxonomy . " : " . $vl->name; ?></option>
											<?php }
											} ?>
											</select>					
										<?php }
									} ?>
									<p class="description">
										<?php echo esc_html( 'Only selected taxonomy posts will be displayed in the slider or carousel' ); ?>
									</p>
								</td>
							</tr>
							<?php
							}
							$tags = get_tags();
							if (count($tags)) {
							?>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Posts by tags' ); ?></th>
								<td>
									<?php
									$get_tags = false;
									if(isset($get_ps_setting['tags'])) {
										$get_tags = $get_ps_setting['tags'];	
									}
									?>
									<select name="ps_setting[<?php echo $slider_no; ?>][tags][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
									<option value="">Select tags</option>
									<?php foreach($tags as $key => $val) { ?>
										<option <?php if ($get_tags && in_array($val->slug, $get_tags)) { echo "selected"; } ?> value="<?php echo $val->slug; ?>"><?php echo $val->name; ?></option>
									<?php } ?>
									</select>
									<p class="description">
										<?php echo esc_html( 'Display only posts with selected tag above' ); ?>
									</p>
								</td>
							</tr>
							<?php } ?>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Search string' ); ?></th>
								<td>
									<?php
									$get_search_string = false;
									if(isset($get_ps_setting['search_string'])) {
										$get_search_string = $get_ps_setting['search_string'];	
									}
									?>
									<input type="text" name="ps_setting[<?php echo $slider_no; ?>][search_string]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_search_string); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Display only posts that have the above keywords' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Template setting' ); ?></th>
								<td>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Dynemic tags : Use the below tags to fetch dynamic content.' ); ?>
									</p>
									<p>
										<strong>
											<code title="<?php echo esc_attr( 'Dynemic tag for title, get_the_title()' ); ?>">%title%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for title, get_the_date()' ); ?>">%date%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for link, get_permalink()' ); ?>">%permalink%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for content, get_the_content()' ); ?>">%content%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for excerpt, get_the_excerpt()' ); ?>">%excerpt%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for feature thumb image' ); ?>">%feature_img|thumbnail%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for feature medium image' ); ?>">%feature_img|medium%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for feature large image' ); ?>">%feature_img|large%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for feature full image' ); ?>">%feature_img|full%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for previous button' ); ?>">%author_name%</code>
											<code title="<?php echo esc_attr( 'Dynemic tag for next button' ); ?>">%author_posts_url%</code>
										</strong>
									</p>
									<?php
									if(isset($get_ps_setting['template_setting']) && $get_ps_setting['template_setting'] != '') {
										$template_setting = $get_ps_setting['template_setting'];
									} else {
										$template_setting = '<div class="ps-box animated fadeIn">
 <div class="ps-pad">
   <img class="ps-image" src="%feature_img|thumbnail%">
   <div class="ps-content">
     <h2 class="ps-title">%title%</h2>
     <p><span class="ps-date">%date%</span></p>
     <div class="ps-excerpt">%excerpt%</div>
     <a class="ps-readmore" href="%permalink%">Read more</a>
   </div>
  </div>
</div>';
									}
									$settings = array(
										'media_buttons' => false,
										'quicktags' => false,
										'tinymce' => false
									);
									$editor_id = "ps_setting[".$slider_no."][template_setting]";
									wp_editor( $template_setting, $editor_id, $settings );
									?>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'CSS Animation : Here are all the CSS classes you can use for animation.' ); ?>
										<a href="<?php echo esc_url( 'https://daneden.github.io/animate.css/' ); ?>" target="_blank"><?php echo esc_html( 'https://daneden.github.io/animate.css/' ); ?></a>
									</p>
									<p>
										<strong>
											<code><?php echo esc_html( 'bounceInDown' ); ?></code>
											<code><?php echo esc_html( 'bounceInLeft' ); ?></code>
											<code><?php echo esc_html( 'bounceInRight' ); ?></code>
											<code><?php echo esc_html( 'bounceInUp' ); ?></code>
											<code><?php echo esc_html( 'fadeIn' ); ?></code>
											<code><?php echo esc_html( 'fadeInDown' ); ?></code>
											<code><?php echo esc_html( 'fadeInLeft' ); ?></code>
											<code><?php echo esc_html( 'fadeInRight' ); ?></code>
											<code><?php echo esc_html( 'fadeInUp' ); ?></code>
											<code><?php echo esc_html( 'flipInX' ); ?></code>
											<code><?php echo esc_html( 'lightSpeedIn' ); ?></code>
											<code><?php echo esc_html( 'rotateInDownLeft' ); ?></code>
											<code><?php echo esc_html( 'rotateInDownRight' ); ?></code>
											<code><?php echo esc_html( 'rotateInUpLeft' ); ?></code>
											<code><?php echo esc_html( 'rotateInUpRight' ); ?></code>
											<code><?php echo esc_html( 'slideInUp' ); ?></code>
											<code><?php echo esc_html( 'slideInDown' ); ?></code>
											<code><?php echo esc_html( 'slideInLeft' ); ?></code>
											<code><?php echo esc_html( 'slideInRight' ); ?></code>
											<code><?php echo esc_html( 'zoomIn' ); ?></code>
											<code><?php echo esc_html( 'zoomInDown' ); ?></code>
											<code><?php echo esc_html( 'zoomInLeft' ); ?></code>
											<code><?php echo esc_html( 'zoomInRight' ); ?></code>
											<code><?php echo esc_html( 'zoomInUp' ); ?></code>
											<code><?php echo esc_html( 'rollIn' ); ?></code>
										</strong>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Design option' ); ?></th>
								<td class="<?php echo esc_attr( 'design-option' ); ?>">
									<?php
									$get_design_option = 'fullwidth';
									if(isset($get_ps_setting['design_option'])) {
										$get_design_option = $get_ps_setting['design_option'];	
									}
									?>
									<input id="<?php echo esc_attr( 'fullwidth' ); ?>" type="radio" <?php if($get_design_option == 'fullwidth') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'fullwidth' ); ?>">
									<label for="fullwidth" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'fullwidth') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'Full width' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'fixwidth' ); ?>" type="radio" <?php if($get_design_option == 'fixwidth') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'fixwidth' ); ?>">
									<label style="margin-right:25px;" for="fixwidth" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'fixwidth') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'Fix width' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'grid' ); ?>" type="radio" <?php if($get_design_option == 'grid') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'grid' ); ?>">
									<label for="grid" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'grid') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'Grid' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'grid2' ); ?>" type="radio" <?php if($get_design_option == 'grid2') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'grid2' ); ?>">
									<label style="margin-right:25px;" for="grid2" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'grid2') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'Grid - 2' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'carousel2' ); ?>" type="radio" <?php if($get_design_option == 'carousel2') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'carousel2' ); ?>">
									<label for="carousel2" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'carousel2') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'Carousel - 2' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'carousel3' ); ?>" type="radio" <?php if($get_design_option == 'carousel3') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'carousel3' ); ?>">
									<label for="carousel3" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'carousel3') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'Carousel - 3' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'carousel4' ); ?>" type="radio" <?php if($get_design_option == 'carousel4') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'carousel4' ); ?>">
									<label style="margin-right:25px;" for="carousel4" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'carousel4') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'Carousel - 4' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'insidebar' ); ?>" type="radio" <?php if($get_design_option == 'insidebar') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'insidebar' ); ?>">
									<label for="insidebar" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'insidebar') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'In sidebar (widget)' ); ?>
									</label>
									<input id="<?php echo esc_attr( 'nodesign' ); ?>" type="radio" <?php if($get_design_option == 'nodesign') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][design_option]" value="<?php echo esc_attr( 'nodesign' ); ?>">
									<label for="nodesign" class="<?php echo esc_attr( 'button button-small' ); if($get_design_option == 'nodesign') echo esc_attr( ' checked' ); ?>">
										<?php echo esc_html( 'No design' ); ?>
									</label>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Select the ready design option from above.' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Length of post content' ); ?></th>
								<td>
									<?php
									$get_excerpt_length = 100;
									if(isset($get_ps_setting['excerpt_length'])) {
										$get_excerpt_length = $get_ps_setting['excerpt_length'];
									}
									?>
									<input type="number" name="ps_setting[<?php echo $slider_no; ?>][excerpt_length]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_excerpt_length); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'You can choose the post content length by this option.' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Plain HTML' ); ?></th>
								<td>
									<?php
									$get_no_ajax = 0;
									if(isset($get_ps_setting['no_ajax'])) {
										$get_no_ajax = $get_ps_setting['no_ajax'];	
									}
									?>
									<input type="checkbox" <?php if($get_no_ajax == '1') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][no_ajax]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
									<span class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'No slider or carousel, tick the option to get the plain HTML code' ); ?>
									</span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Hide next / prev' ); ?></th>
								<td>
									<?php
									$get_hide_next_prev = 0;
									if(isset($get_ps_setting['hide_next_prev'])) {
										$get_hide_next_prev = $get_ps_setting['hide_next_prev'];	
									}
									?>
									<input type="checkbox" <?php if($get_hide_next_prev == '1') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][hide_next_prev]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
									<span class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Tick here to hide next / previous buttons' ); ?>
									</span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Hide pagination' ); ?></th>
								<td>
									<?php
									$get_hide_pagi = 0;
									if(isset($get_ps_setting['hide_pagi'])) {
										$get_hide_pagi = $get_ps_setting['hide_pagi'];	
									}
									?>
									<input type="checkbox" <?php if($get_hide_pagi == '1') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][hide_pagi]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
									<span class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Tick here to hide pagination' ); ?>
									</span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Mousewheel ON' ); ?></th>
								<td>
									<?php
									$get_mw_on = 0;
									if(isset($get_ps_setting['mw_on'])) {
										$get_mw_on = $get_ps_setting['mw_on'];
									}
									?>
									<input type="checkbox" <?php if($get_mw_on == '1') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][mw_on]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
									<span class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Tick this to move to the next / previous slide on the mouse wheel event' ); ?>
									</span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Infinite loop OFF' ); ?></th>
								<td>
									<?php
									$get_il_off = 0;
									if(isset($get_ps_setting['il_off'])) {
										$get_il_off = $get_ps_setting['il_off'];	
									}
									?>
									<input type="checkbox" <?php if($get_il_off == '1') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][il_off]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
									<span class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Clicking on the Next button on the last slide will take you to the first slide. Tick here to stop looping.' ); ?>
									</span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Touch swipe ON' ); ?></th>
								<td>
									<?php
									$get_ts_on = 0;
									if(isset($get_ps_setting['ts_on'])) {
										$get_ts_on = $get_ps_setting['ts_on'];	
									}
									?>
									<input type="checkbox" <?php if($get_ts_on == '1') echo 'checked'; ?> name="ps_setting[<?php echo $slider_no; ?>][ts_on]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
									<span class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Tick this to move to the next / previous slide on the tough swipe and mouse swipe event' ); ?>
									</span>
								</td>
							</tr>
						</table>
						<?php submit_button(); ?>
						<h3 class="<?php echo esc_attr( 'rateme alain-right' ); ?>">Don’t forget to rate this plugin if you like it, thanks!... :)</h3>
					</form>
				<?php }
				} else {
					/*Listing*/
					$del_slider_no = 0;
					if(isset($_GET['del_slider_no'])) {
						/*Delete Slider*/
						$del_slider_no = sanitize_text_field($_GET['del_slider_no']);
						update_option("ps_setting{$del_slider_no}", "blank");
					}

					if(isset($_GET['preview'])) {
						$preview = sanitize_text_field($_GET['preview']);
						/*Get slider setting*/
						$get_ps_setting = get_option( "ps_setting{$preview}" );
						?>
						<div class="<?php echo esc_attr('content-box'); ?>">
							<a class="<?php echo esc_attr( 'button' ); ?>" href="<?php echo admin_url('/admin.php?page=postcs'); ?>">
								<?php echo esc_html('close preview'); ?>
							</a>
							<br><br>
							<?php echo do_shortcode('[post-cs id='.$preview.']'); ?>
						</div>
						<?php
					}
					?>
					<div>
						<table class="<?php echo esc_attr( 'wp-list-table widefat fixed striped posts' ) ?>">
							<thead>
								<tr>
									<th width="50"><?php echo esc_html( 'No.' ); ?></th>
									<th width="100"><?php echo esc_html( 'Name' ); ?></th>
									<th><?php echo esc_html( 'Shortcode' ); ?></th>
									<th width="100"></th>
									<th width="100"></th>
									<th width="100"></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								while(get_option( "ps_setting{$i}" )) {
									$get_ps_setting = get_option( "ps_setting{$i}" );
									if($get_ps_setting != 'blank') {
										$get_enter_name = '';
										if(isset($get_ps_setting['enter_name'])) {
											$get_enter_name = $get_ps_setting['enter_name'];	
										}
									?>
									<tr>
										<td><?php echo $i; ?></td>
										<td><?php echo $get_enter_name; ?></td>
										<td>
											<pre>
												<strong><?php echo esc_html( 'Shortcode for CMS' ) ?> :</strong> [post-cs id=<?php echo $i; ?>]
											</pre>
											<pre>
												<strong><?php echo esc_html( 'Shortcode for PHP' ) ?> :</strong> &lt;?php echo do_shortcode('[post-cs id=<?php echo $i; ?>]'); ?&gt;
											</pre>
										</td>
										<td>
											<a class="<?php echo esc_attr('button'); ?>" href="<?php echo admin_url('/admin.php?page=postcs&preview='.$i); ?>">
												<?php echo esc_html( 'Preview' ); ?>
											</a>
										</td>
										<td>
											<a class="<?php echo esc_attr('button'); ?>" href="<?php echo admin_url('/admin.php?page=postcs&slider_no='.$i); ?>">
												<?php echo esc_html( 'Setting' ); ?>
											</a>
										</td>
										<td>
											<a class="<?php echo esc_attr('button'); ?>" href="<?php echo admin_url('/admin.php?page=postcs&del_slider_no='.$i); ?>">
												<?php echo esc_html( 'Delete' ); ?>
											</a>
										</td>
									</tr>
								    <?php
								    }
								    $i++;
								} ?>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="6">
										<a href="<?php echo admin_url('/admin.php?page=postcs&slider_no='.$i); ?>" class="<?php echo esc_attr( 'button button-primary' ); ?>">
											<?php echo esc_html( 'Add New Slider' ); ?>
										</a>
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
			<?php } ?>				
			</div>
	<?php }

	/*Exclude JS setting*/
	public function postcs_page_setting() { ?>
		<div class="<?php echo esc_attr( 'wrap' ); ?>">
			<form method="post" action="options.php">
				<?php
				settings_fields( 'postcs-js-settings' );
				do_settings_sections( 'postcs-js-settings' );
				?>
				<table class="<?php echo esc_attr( 'form-table psform' ); ?>">
					<tr valign="top">
						<th scope="row"><?php echo esc_html( 'Exclude Mousewheel JS' ); ?></th>
						<td>
							<?php
							$ps_setting_mousewheel = false;
							if(get_option( 'ps_setting_mousewheel' )) {
								$ps_setting_mousewheel = get_option( 'ps_setting_mousewheel' );	
							}
							?>
							<input type="checkbox" <?php if($ps_setting_mousewheel == '1') echo 'checked'; ?> name="ps_setting_mousewheel" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
							<span class="<?php echo esc_attr( 'description' ); ?>">
								<?php echo esc_html( 'tick here for exclude mousewheel js from site footer' ); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo esc_html( 'Exclude Touch Swipe JS' ); ?></th>
						<td>
							<?php
							$ps_setting_swipe = false;
							if(get_option( 'ps_setting_swipe' )) {
								$ps_setting_swipe = get_option( 'ps_setting_swipe' );	
							}
							?>
							<input type="checkbox" <?php if($ps_setting_swipe == '1') echo 'checked'; ?> name="ps_setting_swipe" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
							<span class="<?php echo esc_attr( 'description' ); ?>">
								<?php echo esc_html( 'tick here for exclude swipe js from site footer' ); ?>
							</span>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>				
			</form>
		</div>
		<?php
	}
}

$postcs_Admin = new postcs_Admin();