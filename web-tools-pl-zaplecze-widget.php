<?php

	/**
	 * Plugin Name: Zaplecze Web-Tools.pl
	 * Description: Custom Widget for displaying links from Web-Tools.pl Link Management system.
	 * Version: 0.1
	 * Author: Marek Sciubidlo
	 * License: GPLv2 or later
	 */
	 
	/**  
	 * Copyright 2014   Marek Sciubidlo ( email : wordpresszaplecze@web-tools.pl )
	 * This program is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License, version 2, as 
	 * published by the Free Software Foundation.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	 */

	defined('ABSPATH') or die("No script kiddies please!");

	class recent_comments extends WP_Widget {

		private $ver = '0.1';
		private $domain = 'pci_text_domain';
		private $wtlinki = array();
		
		function register_client_id($id = '')
		{
			 add_option( 'wtpl_z_id', $id, '', 'yes' ); 
			return $id;
		}
		
		public function __construct() {
	
		$clientId = get_option('wtpl_z_id');
		
		if($clientId === false)
		{
			foreach (glob(ABSPATH."wt_*.php") as $filename) {
				preg_match("/wt_(.*)\.php/i", $filename, $res);
				$clientId = $this->register_client_id( $res[0] );
				
			}
		}	
				if(file_exists(ABSPATH.'/'.$clientId))
				{
		include_once(ABSPATH.'/'.$clientId);
		$wt = new wtengine;
		$linki = explode("\n", $wt -> fetch());
		}else
		{
			$linki = array('<!-- Brak pliku klienta WT -->');
		}
		foreach( $linki as $link)
		{
		if(strlen($link) > 5)
			$this->wtlinki[] = $link;
		}
		
		
			$widget_ops = array(
				'description' => __( 'Custom Widget for displaying links from Web-Tools.pl System.', $this->domain ),
				'customizer_support' => true
			);
			$control_ops = array( 'width' => 400 );
			parent::__construct( false, __( 'Web-Tools Linki' , $this->domain ), $widget_ops, $control_ops );
			//add_action( 'wp_enqueue_scripts', array( $this, 'jsjr_pci_wp_styles_and_scripts' ));
			add_action( 'admin_enqueue_scripts', array( $this, 'jsjr_pci_admin_styles_and_scripts' ));
		}
		
		public function jsjr_pci_admin_styles_and_scripts( $hook ){
			if ( 'widgets.php' == $hook ) {
			//	wp_enqueue_media();
				wp_enqueue_script( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'jsjr-pci-admin-scripts' , plugin_dir_url( __FILE__ ) . 'js/admin-scripts.js', array('jquery'), $this->ver , false );
				wp_enqueue_style( 'jsjr-pci-admin-css' , plugin_dir_url( __FILE__ ) . 'css/admin-styles.css' , array() , $this->ver , false );				
			}			
		}
		
		public function jsjr_pci_wp_styles_and_scripts(){
			
		}
		public function getLink($limit = 100, $mode = ''){

			$rt ='';
			$limit = (int)$limit;
			if($limit == 0)
			{
				$limit = 5;
			}
			for($i=0;$i<$limit;$i++)
			{
				if(count($this->wtlinki)>0)
				{
					if($mode == 'li'){$rt.='<li>';}
					$rt .= array_shift($this->wtlinki);
					if($mode == 'li'){$rt.='</li>';}
				}
			}
			return $rt;
		}
		public function widget( $args, $instance ) {

			extract( $args );
			extract( $instance );
				
				
				$linki = $this->getLink($limit, $mode);

				if(strlen($linki) > 5)
				{
			
			echo $before_widget;
			
			
			if ( !empty( $title ) ) {
				echo $before_title , $title , $after_title;
			}
			if($mode == 'li'){$rt.='<ul>';}
				echo $linki;
			if($mode == 'li'){$rt.='</ul>';}
			
					
			echo $after_widget;
			} // strlen
		}

		public function update( $new_instance, $old_instance ) {
			foreach ( $new_instance as $key => $value ) {
				$old_instance[ $key ] = trim( strip_tags( $value ) );
			}
		
		return $old_instance;
		}

		public function form( $instance ) {	

			foreach ( $instance as $key => $value ) {
				$$key = esc_attr( $value );
			}
			
			$select_options = array(
			'li' => 'Lista li',
			'none' => 'Bez stylu',
			
			);
				
			?>
			
			<p>
				<p>Plik wt_UnikalnyId.php i katalog wt_UnikalnyId umieść w katalogu głównym Wordpress.</p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Tytuł:', $this->domain ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php _e( isset( $title ) ?  $title : '', $this->domain ); ?>" />
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Limit linków (optional):', $this->domain ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php _e( isset( $limit ) ?  $limit : '5', $this->domain ); ?>" />
				<p>Jeżeli dodano kilka widgetów, można zarządać ile linków maksymalnie będzie widoczne w danym widgecie.</p>
				<label for="<?php echo $this->get_field_id('mode'); ?>"><?php _e('Format wyświetlnia:'); ?></label>
						<a href="#" class="jsjr-pci-question" title="<?php _e( 'NOTICE: These styles do not work on old internet browsers.', $this->domain ) ?>">?</a>
						<select name="<?php echo $this->get_field_name('mode'); ?>" id="<?php echo $this->get_field_id('mode'); ?>" class="widefat">
							<?php
							$mode = isset( $mode ) ? $mode : 'li';
							foreach ( $select_options as $key => $value ) {
								echo '<option value="' , $key , '" ', selected( $mode, $key ) , '>', __( $value, $this->domain ) , '</option>';
							}
							?>
						</select>
			</p>
			
						
			<?php
		}
		
	}
	add_action('widgets_init', create_function('', 'return register_widget("recent_comments");'));