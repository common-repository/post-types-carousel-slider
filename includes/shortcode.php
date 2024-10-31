<?php
class postcs_Shortcode extends postcsCls {
	
	public function __construct() {
		/*Shortcode*/
		add_shortcode('post-cs', array( $this, 'postcs_shortcode' ));
	}

	/*Shortcode function*/
	public function postcs_shortcode($atts) {
		$id = 1;
		if(isset($atts['id'])) {
			$id = $atts['id'];
		}
		$class = "";
		if (get_option("ps_setting{$id}")) {
			$get_ps_setting = get_option("ps_setting{$id}");
			if(isset($get_ps_setting['no_ajax'])) {
				$get_no_ajax = $get_ps_setting['no_ajax'];
				if($get_no_ajax == 1) {
					$get_post_type = 'any';
					if(isset($get_ps_setting['post_type'])) {
						$get_post_type = $get_ps_setting['post_type'];	
					}

					$get_posts_per_page = 1;
					if(isset($get_ps_setting['posts_per_page'])) {
						$get_posts_per_page = $get_ps_setting['posts_per_page'];
					}

					$get_exclude_post = 0;
					if(isset($get_ps_setting['exclude_post'])) {
						$get_exclude_post = $get_ps_setting['exclude_post'];
						$get_exclude_post = trim($get_exclude_post);
						$get_exclude_post = explode(",", $get_exclude_post);
					}

					$get_order = 'DESC';
					if(isset($get_ps_setting['order'])) {
						$get_order = $get_ps_setting['order'];
					}

					$get_orderby = 'date';
					if(isset($get_ps_setting['orderby'])) {
						$get_orderby = $get_ps_setting['orderby'];
					}

					$get_post_status = 'publish';

					$get_cat = array();
					if(isset($get_ps_setting['cat'])) {
						$get_cat = $get_ps_setting['cat'];
					}

					if(isset($get_ps_setting['excat'])) {
						$get_excat = $get_ps_setting['excat'];
						foreach ($get_excat as $key => $value) {
							array_push($get_cat, $value * -1);
						}
					}
					$get_cat = array_values(array_filter($get_cat));

					$get_tax = '';
					if(isset($get_ps_setting['tax'])) {
						$get_tax = $get_ps_setting['tax'];
					}

					$get_tags = array();
					if(isset($get_ps_setting['tags'])) {
						$get_tags = $get_ps_setting['tags'];
					}

					$get_tags = array_values(array_filter($get_tags));
					$get_tags = implode(",", $get_tags);

					$get_search_string = '';
					if(isset($get_ps_setting['search_string'])) {
						$get_search_string = $get_ps_setting['search_string'];
					}

					$get_excerpt_length = 100;
					if(isset($get_ps_setting['excerpt_length'])) {
						$get_excerpt_length = $get_ps_setting['excerpt_length'];
					}

					$tax_query = false;
					if($get_tax) {
						$tax_query = array( 'relation' => 'OR' );
						foreach($get_tax as $key => $val) {
							$tax_term = explode("?", trim($val));
							$tax_query[] = array(
						        'taxonomy' => ''.$tax_term[0].'',
						        'field'    => 'slug',
						        'terms'    => ''.$tax_term[1].''
						    );			
						}
					}

					if ($get_post_type == 'page') {
						$args = array(
							'post_type' => $get_post_type,
							'posts_per_page' => $get_posts_per_page,
							'post__not_in' => $get_exclude_post,
							'order' => $get_order,
							'orderby' => $get_orderby,
							'tag' => $get_tags,
							's' => $get_search_string,
							'tax_query' => $tax_query
						);
					} else {
						$args = array(
							'post_type' => $get_post_type,
							'posts_per_page' => $get_posts_per_page,
							'post__not_in' => $get_exclude_post,
							'order' => $get_order,
							'orderby' => $get_orderby,
							'cat' => $get_cat,
							'tag' => $get_tags,
							's' => $get_search_string,
							'tax_query' => $tax_query
						);
					}

					$postcs_query = new WP_Query( $args );

					if ( $postcs_query->have_posts() ) :
					global $post;
					$returnhtml = false;
					while ( $postcs_query->have_posts() ) : $postcs_query->the_post();
						$postid = get_the_ID();
						$title = get_the_title();
						$author_name = get_the_author();
						$author_id = get_the_author_meta('id');
						$author_posts_url = get_author_posts_url($author_id);
						$date = get_the_date();
						$content = get_the_content();
						$excerpt = parent::postcs_the_excerpt($get_excerpt_length);
						$permalink = get_permalink();
						$feature_img = false;
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
							$find_rep_str = parent::get_inbetween_strings('%', '%', $template_setting);
							foreach($find_rep_str as $key => $val) {
								if(strrpos($val, "getfield") > -1) {
									$get_field_arr = explode("|", $val);
									if(!$get_field_arr[2]) {
										$get_field_arr[2] = 'text';
									}
									if($get_field_arr[2] == 'text' || $get_field_arr[2] == 'number' || $get_field_arr[2] == 'email' || $get_field_arr[2] == 'editor' || $get_field_arr[2] == 'textarea') {
										if (function_exists('get_field')) {
											$getfields = get_field($get_field_arr[1]);
											$template_setting = str_replace("%{$val}%", "{$getfields}", $template_setting);
										}
									}
									if($get_field_arr[2] == 'image') {
										if(!$get_field_arr[3]) {
											$get_field_arr[3] = 'url';
										}
										if (function_exists('get_field')) {
											$getfields = get_field($get_field_arr[1]);
											if(is_array ($getfields)) {
												if($get_field_arr[3] == 'url') {
													if($getfields['url']) {
														$template_setting = str_replace("%{$val}%", "<img src='".$getfields['url']."'>", $template_setting);
													}
												} else {
													if($getfields['sizes']) {
														if($getfields['sizes'][$get_field_arr[3]]) {
															$template_setting = str_replace("%{$val}%", "<img src='".$getfields['sizes'][$get_field_arr[3]]."'>", $template_setting);
														}
													}
												}																	
											} else {
												if($getfields) {
													if(strrpos($getfields, "http") > -1) {
														$template_setting = str_replace("%{$val}%", "<img src='".$getfields."'>", $template_setting);
													} else {
														$img = wp_get_attachment_image( $getfields, $get_field_arr[3] );
														$template_setting = str_replace("%{$val}%", $img, $template_setting);
													}
												} else {
													$template_setting = str_replace("%{$val}%", "", $template_setting);
												}
											}
										}
									}
								} else {
									if(strrpos($val, "feature_img") > -1) {
										$get_feature_img = explode("|", $val);
										if(isset($get_feature_img[1])) {
											if ( has_post_thumbnail() ) {
												$img_path = wp_get_attachment_image_src( get_post_thumbnail_id( $postid ), $get_feature_img[1] );
												if($img_path[0]) {
													$feature_img = $img_path[0];
												}
											}
										} else {
											if ( has_post_thumbnail() ) {
												$img_path = wp_get_attachment_image_src( get_post_thumbnail_id( $postid ), 'thumbnail' );
												if($img_path[0]) {
													$feature_img = $img_path[0];
												}
											}
										}
										if(!$feature_img){
											$feature_img = plugins_url( 'assets/img/no-img.png', dirname(__FILE__) );
										}
										$template_setting = str_replace("%{$val}%", $feature_img, $template_setting);			
									} else {
										$template_setting = str_replace("%{$val}%", $$val, $template_setting);
									}
								}						
							}
							$returnhtml .= $template_setting;
						endwhile;
						wp_reset_postdata();
					else :
					endif;
				    return $returnhtml;
					wp_die();
				}
			}
			if(isset($get_ps_setting['design_option'])) {
				$get_design_option = $get_ps_setting['design_option'];
			}
			if($get_design_option == '') {
				$get_design_option = 'fullwidth';
			}
			$get_mw_on = '';
			if(isset($get_ps_setting['mw_on'])) {
				$get_mw_on = ' data-mw="'.$get_ps_setting['mw_on'].'" ';
			}
			$get_il_off = '';
			if(isset($get_ps_setting['il_off'])) {
				$get_il_off = ' data-il="'.$get_ps_setting['il_off'].'" ';
			}
			$get_ts_on = '';
			if(isset($get_ps_setting['ts_on'])) {
				$get_ts_on = ' data-ts="'.$get_ps_setting['ts_on'].'" ';
			}
		}
		$class = " class='".$get_design_option."'";
		return "<div id='post-cs' ".$class." ".$get_mw_on." ".$get_il_off." ".$get_ts_on." data-id='".$id."'><div class='loading-box'><span></span></div></div>";
		wp_die();
	}      
}

$postcs_Shortcode = new postcs_Shortcode();