<?php
class postcs_Front extends postcsCls {
	
	public function __construct() {
		/*Generate HTML (ajax function)*/
		add_action( 'wp_ajax_postcs_getdata', array( $this, 'postcs_getdata' ));
		add_action( 'wp_ajax_nopriv_postcs_getdata', array( $this, 'postcs_getdata' ));
	}

	/*Generate slider HTML*/
	public function postcs_getdata() {
 		global $wpdb;
 		if(isset($_POST['data']['id'])) {
 			$id = sanitize_text_field($_POST['data']['id']);
 			if (get_option("ps_setting{$id}")) {
 				$get_ps_setting = get_option("ps_setting{$id}");

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

				$get_paged = sanitize_text_field($_POST['data']['paged']);
				if(!$get_paged) {
					$get_paged = 1;
				}

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

				$get_hide_next_prev = '';
				if(isset($get_ps_setting['hide_next_prev'])) {
					$get_hide_next_prev = $get_ps_setting['hide_next_prev'];
				}

				$get_hide_pagi = '';
				if(isset($get_ps_setting['hide_pagi'])) {
					$get_hide_pagi = $get_ps_setting['hide_pagi'];
				}

				$get_excerpt_length = 100;
				if(isset($get_ps_setting['excerpt_length'])) {
					$get_excerpt_length = $get_ps_setting['excerpt_length'];
				}

				$get_il_off = 0;
				if(isset($get_ps_setting['il_off'])) {
					$get_il_off = $get_ps_setting['il_off'];
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
						'posts_per_page' => -1,
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
						'posts_per_page' => -1,
						'post__not_in' => $get_exclude_post,
						'order' => $get_order,
						'orderby' => $get_orderby,
						'cat' => $get_cat,
						'tag' => $get_tags,
						's' => $get_search_string,
						'tax_query' => $tax_query
					);
				}
				
				$count_all_postcs_query = new WP_Query( $args );
				$count_all_postcs = $count_all_postcs_query->post_count;
				$count_all_postcs = ceil($count_all_postcs / $get_posts_per_page);

				if ($get_post_type == 'page') {
					$args = array(
						'post_type' => $get_post_type,
						'posts_per_page' => $get_posts_per_page,
						'post__not_in' => $get_exclude_post,
						'order' => $get_order,
						'orderby' => $get_orderby,
						'paged'	=> $get_paged,
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
						'paged'	=> $get_paged,
						'cat' => $get_cat,
						'tag' => $get_tags,
						's' => $get_search_string,
						'tax_query' => $tax_query
					);
				}

				$postcs_query = new WP_Query( $args );
				$count = $postcs_query->post_count;
				if($count == 0) {
					echo $count;
					wp_die();	
				}
							
				if ( $postcs_query->have_posts() ) :
					global $post;
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
						if($postcs_query->query['paged'] > 1) {
							$prev = $postcs_query->query['paged'] - 1;
						}
						$next = $postcs_query->query['paged'] + 1;

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
								if(strrpos($val, "get_field") > -1) {
									$get_field_arr = explode("|", $val);
									if(!$get_field_arr[2]) {
										$get_field_arr[2] = 'text';
									}
									if($get_field_arr[2] == 'text' || $get_field_arr[2] == 'number' || $get_field_arr[2] == 'email' || $get_field_arr[2] == 'editor' || $get_field_arr[2] == 'textarea') {
										if (function_exists('get_field')) {
											$get_fields = get_field($get_field_arr[1]);
											$template_setting = str_replace("%{$val}%", "{$get_fields}", $template_setting);
										}										
									}
									if($get_field_arr[2] == 'image') {
										if(!$get_field_arr[3]) {
											$get_field_arr[3] = 'url';
										}
										if (function_exists('get_field')) {
											$get_fields = get_field($get_field_arr[1]);
											if(is_array ($get_fields)) {
												if($get_field_arr[3] == 'url') {
													if($get_fields['url']) {
														$template_setting = str_replace("%{$val}%", "<img src='".$get_fields['url']."'>", $template_setting);
													}
												} else {
													if($get_fields['sizes']) {
														if($get_fields['sizes'][$get_field_arr[3]]) {
															$template_setting = str_replace("%{$val}%", "<img src='".$get_fields['sizes'][$get_field_arr[3]]."'>", $template_setting);
														}
													}
												}																	
											} else {
												if($get_fields) {
													if(strrpos($get_fields, "http") > -1) {
														$template_setting = str_replace("%{$val}%", "<img src='".$get_fields."'>", $template_setting);
													} else {
														$img = wp_get_attachment_image( $get_fields, $get_field_arr[3] );
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
							echo $template_setting;
							
						endwhile;

						if($count_all_postcs > 1) {
							if($get_hide_next_prev && $get_hide_next_prev == '1') {
							} else {
								if($get_il_off == 1) {
									if($prev) {
										echo '<a href="javascript:;" class="ps-prev" data-paged="'.$prev.'">Prev</a>';
									}
								} else {
									$data_total = ceil($count_all_postcs_query->post_count / $get_posts_per_page);
									echo '<a href="javascript:;" class="ps-prev" data-paged="'.$prev.'" data-total="'.$data_total.'">Prev</a>';
								}
								echo '<a href="javascript:;" class="ps-next" data-paged="'.$next.'">Next</a>';
							}
							$pagi = '<div class="ps-pagi">';
							if($get_hide_pagi && $get_hide_pagi == '1') {
							} else{
								for($j = 1; $j <= $count_all_postcs; $j++ ) {
									$activecls = $get_paged == $j ? 'active' : '';
									$pagi .= '<a href="javascript:;" data-paged="'.$j.'" class="'.$activecls.'">'.$j.'</a>';
								}							
							}
							$pagi .= '</div>';
							echo $pagi;
						}
						wp_reset_postdata();
					else :
					endif;
				}
	        }
	        wp_die();
	    }        
	}

$postcs_Front = new postcs_Front();