<?php
/**
 * @author:burncode
 * @description：显示所有页面类型的文章列表的小部件，用于genesis 子主题
 */

class Mh_Featured_Page_List extends WP_Widget {

    protected $defauts;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
        //设置变量
        $this->defauts= array(
			'title'                   => '',
			'post_type'               => 'page',
			'posts_num'               => 1,
			'posts_offset'            => 0,
			'post_parent'     => '2',
			'orderby'                 => '',
			'order'                   => '',
			'columns'                 => 'one_half_1',
			'exclude_displayed'       => 0,
			'show_image'              => 0,
			'image_alignment'         => '',
			'image_size'              => '',
			'show_title'              => 0,
			'show_byline'             => 0,
			'show_content'            => 'excerpt',
			'content_limit'           => '',
			'more_text'               => 'Read More',
		 );
		$widget_ops = array( 
			'classname' => 'featuredpages featuredlist',
			'description' => 'Display featured page list in homepage by maohan',
        );
        $control_ops = array(
			'id_base' => 'featured-page-list',
			'width'   => 505,
			'height'  => 350,
		);
		parent::__construct( 'featured-page-list', 'Mh Featured Page List', $widget_ops,$control_ops);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $wp_query, $_genesis_displayed_ids;
		extract($args);
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		echo $before_widget;
		//* 小部件标题
		if ( ! empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

		$query_args = array(
			'post_type' => 'page',
			'showposts' => $instance['posts_num'],
			'offset'    => $instance['posts_offset'],
			'orderby'   => $instance['orderby'],
			'order'     => $instance['order'],
			'post_parent'	=> $instance['post_parent']
		);
		//* Exclude displayed IDs from this loop?
		if ( $instance['exclude_displayed'] )
			$query_args['post__not_in'] = (array) $_genesis_displayed_ids;
		if ( 'full' !== $instance['columns'] ) {
			add_filter( 'post_class', array( $this, 'add_post_class_' . $instance['columns'] ) );
		}
		$wp_query = new WP_Query( $query_args );
		if ( have_posts() ) : while ( have_posts() ) : the_post();
			// the_content( esc_html( $instance['more_text'] ) );
			$_genesis_displayed_ids[] = get_the_ID();

			genesis_markup( array(
				'html5'   => '<article %s>',
				'xhtml'   => sprintf( '<div class="%s">', implode( ' ', get_post_class() ) ),
				'context' => 'entry',
			) );
			$image = genesis_get_image( array(
				'format'  => 'html',
				'size'    => $instance['image_size'],
				'context' => 'featured-post-widget',
				'attr'    => genesis_parse_attr( 'entry-image-widget' ),
			) );
			//显示特色图片
			if ( $instance['show_image'] && $image )
				printf( '<a href="%s" title="%s" class="link_image %s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), esc_attr( $instance['image_alignment'] ), $image );
			
			//是否显示标题
			if ( $instance['show_title'] )
				echo genesis_html5() ? '<header class="entry-header">' : '';

				if ( ! empty( $instance['show_title'] ) ) {

					if ( genesis_html5() )
						printf( '<h2 class="entry-title"><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );
					else
						printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );

				}
			//结束header标签
			if ( $instance['show_title'] )
				echo genesis_html5() ? '</header>' : '';
			//开始内容显示
			if ( ! empty( $instance['show_content'] ) ) {

				echo genesis_html5() ? '<div class="entry-content">' : '';
				//摘要
				if ( 'excerpt' == $instance['show_content'] ) {
					the_excerpt();
				}
				elseif ( 'content-limit' == $instance['show_content'] ) {
					the_content_limit( (int) $instance['content_limit'], esc_html( $instance['more_text'] ) );
				}
				else {

					global $more;

					$orig_more = $more;
					$more = 0;
					
					the_content( esc_html( $instance['more_text'] ) );

					$more = $orig_more;

				}
				echo genesis_html5() ? '</div>' : '';
			}
			//关闭文章标签
			genesis_markup( array(
				'html5' => '</article>',
				'xhtml' => '</div>',
			) );
			endwhile; endif;
	}
	
	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		//* Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		// $item     = $this->build_lists( $instance ); ?>
		<p style="text-align:center">Code By maohan</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title:</label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<div class="genesis-widget-column">

			<div class="genesis-widget-column-box genesis-widget-column-box-top">
			<!--设置page的父id-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'post_parent' ) ); ?>">Post Parent </label>
					
						<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'post_parent' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_parent' ) ); ?>" value="<?php echo esc_attr( $instance['post_parent'] ); ?>" class="widefat" />
				</p>
				<!--文章数-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'posts_num' ) ); ?>">Number of Posts to Show: </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'posts_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts_num' ) ); ?>" value="<?php echo esc_attr( $instance['posts_num'] ); ?>" size="2" />
				</p>
				<!--文章offset-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'posts_offset' ) ); ?>"><?php _e( 'Number of Posts to Offset:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'posts_offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts_offset' ) ); ?>" value="<?php echo esc_attr( $instance['posts_offset'] ); ?>" size="2" />
				</p>
				<!--文章order by排序选择-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>">Order By:</label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
						<option value="date" <?php selected( 'date', $instance['orderby'] ); ?>>Date</option>
						<option value="title" <?php selected( 'title', $instance['orderby'] ); ?>>Title</option>
						<option value="parent" <?php selected( 'parent', $instance['orderby'] ); ?>>Parent</option>
						<option value="ID" <?php selected( 'ID', $instance['orderby'] ); ?>>ID</option>
						<option value="comment_count" <?php selected( 'comment_count', $instance['orderby'] ); ?>>Comment Count</option>
						<option value="rand" <?php selected( 'rand', $instance['orderby'] ); ?>>Random</option>
					</select>
				</p>
				<!--排序 顺序和倒序-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">Sort Order:</label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
						<option value="DESC" <?php selected( 'DESC', $instance['order'] ); ?>>Descending (3, 2, 1)</option>
						<option value="ASC" <?php selected( 'ASC', $instance['order'] ); ?>>Ascending (1, 2, 3)</option>
					</select>
				</p>
				<!--文章排除id-->
				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'exclude_displayed' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'exclude_displayed' ) ); ?>" value="1" <?php checked( $instance['exclude_displayed'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'exclude_displayed' ) ); ?>">Exclude Previously Displayed Posts?</label>
				</p>
				<!--显示样式几列-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"> Number of Columns:</label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>">
						<option value="full" <?php selected( 'full', $instance['columns'] ); ?>>1</option>
						<option value="one_half" <?php selected( 'one_half', $instance['columns'] ); ?>>2</option>
						<option value="one_third" <?php selected( 'one_third', $instance['columns'] ); ?>>3</option>
						<option value="one_fourth" <?php selected( 'one_fourth', $instance['columns'] ); ?>>4</option>
					</select>
				</p>
			</div>
		</div>
		<div class="genesis-widget-column genesis-widget-column-right">
			<!--右侧设置选项-->
			<div class="genesis-widget-column-box  genesis-widget-column-box-top">
				<p>
				<!--显示特色图片-->
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>" value="1" <?php checked( $instance['show_image'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>">Show Featured Image</label>
				</p>
				<!--图片大下，来源于系统自定义图片大小-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>">Image Size: </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>" class="genesis-image-size-selector" name="<?php echo esc_attr( $this->get_field_name( 'image_size' ) ); ?>">
						<?php
						$sizes = genesis_get_image_sizes();
						foreach( (array) $sizes as $name => $size )
							echo '<option value="' . esc_attr( $name ) . '"' . selected( $name, $instance['image_size'], FALSE ) . '>' . esc_html( $name ) . ' ( ' . absint( $size['width'] ) . 'x' . absint( $size['height'] ) . ' )</option>';
						?>
					</select>
				</p>
				<!--图片显示方向:left center right-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>">Image Alignment: </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_alignment' ) ); ?>">
						<option value="mhalignnone">- None -</option>
						<option value="mhalignleft" <?php selected( 'mhalignleft', $instance['image_alignment'] ); ?>>Left</option>
						<option value="mhalignright" <?php selected( 'mhalignright', $instance['image_alignment'] ); ?>>Right</option>
						<option value="mhaligncenter" <?php selected( 'mhaligncenter', $instance['image_alignment'] ); ?>>Center</option>
					</select>
				</p>
			</div>

			<div class="genesis-widget-column-box">
				<!--显示标题-->
				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_title' ) ); ?>" value="1" <?php checked( $instance['show_title'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>">Show Post Title</label>
				</p>
				<!--显示内容-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>">Content Type: </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>">
						<option value="content" <?php selected( 'content', $instance['show_content'] ); ?>>Show Content</option>
						<option value="excerpt" <?php selected( 'excerpt', $instance['show_content'] ); ?>>Show Excerpt</option>
						<option value="content-limit" <?php selected( 'content-limit', $instance['show_content'] ); ?>> Show Content Limit</option>
						<option value="" <?php selected( '', $instance['show_content'] ); ?>>No Content</option>
					</select>
					<br />
					<!--内容文字截断-->
					<label for="<?php echo esc_attr( $this->get_field_id( 'content_limit' ) ); ?>">Limit content to
						<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'content_limit' ) ); ?>" value="<?php echo esc_attr( intval( $instance['content_limit'] ) ); ?>" size="3" />
						 Characters
					</label>
				</p>
				<!--阅读更多-->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'more_text' ) ); ?>"><?php _e( 'More Text (if applicable):', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'more_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'more_text' ) ); ?>" value="<?php echo esc_attr( $instance['more_text'] ); ?>" />
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$new_instance['title']     = strip_tags( $new_instance['title'] );
		$new_instance['more_text'] = strip_tags( $new_instance['more_text'] );
		return $new_instance;
	}

	/**
	 * add bootstrap column to class
	 */
	function add_column_classes( $classes, $columns ) {
		global $wp_query;
		//* Bail if we don't have a column number or the one we do have is invalid.
		if ( ! isset( $columns ) || ! in_array( $columns, array( 2, 3, 4) ) ) {
			return;
		}
		//定义class空数组 为后面动态修改content list内容做准备
		$classes = array();
		$column_classes = array(
			2 => 'one-half-2 col-md-6 col-sx-12',
			3 => 'one-third-3 col-md-4 col-sx-12',
			4 => 'one-fourth-4 col-md-3 col-sx-6',
		);
		//* Add the appropriate column class.
		$classes[] = 'grid';
		$classes[] = $column_classes[absint($columns)];
		//* Add an "odd" class to 
		if ( ( $wp_query->current_post + 1 ) % 2 ) {
			//清除浮动 或者使用bootstrap 内置的 clearfix
			$classes[] = 'odd';
		}
		//为第一篇文章添加first class属性
		if ( 0 === $wp_query->current_post || 0 === $wp_query->current_post % $columns ) {
			$classes[] = 'first';
		}
		return $classes;
	}
	/**
	 * 用来形成bootstrap grid 布局
	 */
	//2列 col-md-6
	function add_post_class_one_half( $classes ) {
		return array_merge( (array) $this->add_column_classes( $classes, 2 ), $classes );
	}
	//三列 col-md-4
	function add_post_class_one_third( $classes ) {
		return array_merge( (array) $this->add_column_classes( $classes, 3 ), $classes );
	}
	//四列 col-md-3
	function add_post_class_one_fourth( $classes ) {
		return array_merge( (array) $this->add_column_classes( $classes, 4 ), $classes );
	}

}