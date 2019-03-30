# mh-featured_page_list

显示所有页面类型的文章列表的小部件，用于 genesis 子主题
加入 bootstrap 样式 用来自定义显示列数: 1 列 2 列 3 列

使用方法:

//Include
include_once( get_stylesheet_directory() . '/lib/custom_pagelist_widget.php' );

// Register the paga_list widget
add_action('widgets_init','regist_pagelist_widget');
function regist_pagelist_widget(){
register_widget( 'Mh_Featured_Page_List' );
}
