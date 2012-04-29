<?php
/*
Plugin Name: Easy Gallery
Plugin URI: http://easygallery.miblogo.com
Description: A very easy gallery for your posts, pages and custom posts. Carousel and Slideshow with many jQuery effects and thumbs previews.
Version: 0.1-alpha
Author: Michele Menciassi
Author URI: https://plus.google.com/108252204198990574118/posts
License: GPL2
*/

/*  Copyright 2012  Michele Menciassi  (email : m.menciassi@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class EasyGallery {
	public $shortname = 'easy-gallery';
	
	function __construct() {
		// Loading localization file
		load_plugin_textdomain($this->shortname, false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	function script_easy_gallery_front() {
		wp_enqueue_style('easy-gallery-styles', plugins_url( '/css/default.css', __FILE__ ), array(), '1');
		wp_enqueue_script('jquery-cycle', plugins_url( '/js/jquery.cycle.all.min.js', __FILE__ ), array('jquery'), '1');
		wp_enqueue_script('jquery-easing', plugins_url( '/js/jquery.easing.1.3.min.js', __FILE__ ), array('jquery'), '1');
	}
	
	function print_gallery ($output) {
		global $post;
		$result = '';
		//$size = 'thumb';
		if ( $images = get_children(array(
			'post_parent' => $post->ID,
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_mime_type' => 'image',
			'orderby' => 'menu_order',
			'order' => 'ASC'))) {
			
			if (is_array($images) and count($images) > 0){
$result = <<<EOF
<style type="text/css">
.easygallery-container:after {
clear:both;
}
</style>
<script type="text/javascript">
var j = jQuery.noConflict();
j(document).ready(function() {
	j('.easygallery-slides').cycle({
		prev: '#prev-slide',
		next: '#next-slide',
		fx: 'scrollHorz',
		timeout:0,
		speed: 300,
		height: 330,
		width: 550,
	});
	
	j('#photogallery-nav').cycle({
		fx: 'scrollHorz',
		timeout: 0,
		//pager:  '#photogallery-pages',
		prev:   '#prev-gallery', 
		next:   '#next-gallery',
		after:  onAfterGallery,
		speed: 300,
		height: 150,
		width: 600
	});

	j('.naviga').click(function(){
		var slideid = j(this).attr('id') ;
		j('.easygallery-slides').cycle(parseInt(slideid.substr(6)));
		return false;
	});
	
	function onAfterGallery(curr, next, opts) {
		var index = opts.currSlide;
		j('#prev-gallery')[index == 0 ? 'hide' : 'show']();
		j('#next-gallery')[index == opts.slideCount - 1 ? 'hide' : 'show']();
	}

});


	
</script>
EOF;
				$result .= '<div class="easygallery-container">';
				$result .= '<a href="#" id="prev-slide"><span>' . __('Previous', $this->shortname) . '</span></a>';
				$result .= '<a href="#" id="next-slide"><span>' . __('Next', $this->shortname) . '</span></a>';
				$result .= '<ul class="easygallery-slides">';
				$thumbs  = '<a href="#" id="prev-gallery" style="display: none; ">' . __('Previous', $this->shortname) . '</a>';
				$thumbs .= '<div id="photogallery-nav">';
				$slide = 0;
				$i = 0;
				$max = count($images);
				$max--;
				foreach( $images as $image ) {
					$attachmenturl = wp_get_attachment_url($image->ID);
					$attachmentimage = wp_get_attachment_image( $image->ID, 'large' );
					$result .= '<li>'.$attachmentimage.'</li>';
					
					
					if ($slide == 0){
						// prima slide su 4
						$thumbs .= '<div class="slide">';
					}
					$thumbs .= '<a href="#" class="naviga" id="slide-'.$i.'">';
					$thumbs .=  wp_get_attachment_image( $image->ID, 'thumbnail' );
					$thumbs .= '</a>';
					if ($slide == 3 or $i == $max){
						// ultima slide su 4 o ultimo elemento (devo chiudere comunque) 
						$thumbs .= '</div><!-- /slide-->';
						$slide = -1;
					}
					$i++;
					$slide++;
					
				}
				$thumbs .= '</div><!-- /photogallery-nav -->';
				$thumbs .= '<a href="#" id="next-gallery" style="display: inline; ">' . __('Next', $this->shortname) . '</a>';
				$result .= '</ul></div>';
				$result .= $thumbs;
			}
		}		
		return $result;
	}

}

if (class_exists('EasyGallery')){
	$EasyGallery = new EasyGallery();
	// plugin's javascript & css
	add_action( 'wp_enqueue_scripts', array($EasyGallery, 'script_easy_gallery_front') );
	// override default shortcode [gallery]
	add_filter( 'post_gallery', array($EasyGallery, 'print_gallery'), 11 );

}

?>
