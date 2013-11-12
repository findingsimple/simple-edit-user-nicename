<?php
/*
Plugin Name: Simple Edit User Nicename
Plugin URI: http://plugins.findingsimple.com
Description: Simple plugin to allow the editing of users nicename (which is used for setting author slug)
Version: 1.0
Author: Finding Simple
Author URI: http://findingsimple.com
License: GPL2
*/
/*
Copyright 2013  Finding Simple  (email : plugins@findingsimple.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'Simple_Edit_User_Nicename' ) ) {

	/**
	 * Plugin Main Class.
	 *
	 */
	class Simple_Edit_User_Nicename {

		/**
		 * Initialise
		 */
		function Simple_Edit_User_Nicename() {

			/**
			 * Own profile
			 */
			add_action('show_user_profile', array( $this, 'seun_edit_user_options' ) );		
			add_action('personal_options_update',  array( $this, 'seun_save_user_options' ) );
			
			/**
			 * Other users profiles
			 */
			add_action('edit_user_profile', array( $this, 'seun_edit_user_options') ) ;
			add_action('edit_user_profile_update', array( $this, 'seun_save_user_options') );

		}

		/**
		 * Add field to user profile
		 */
		function seun_edit_user_options() {

			global $user_id;

			$user_id = isset($user_id) ? (int) $user_id : 0;

			if ( ! current_user_can('edit_users') )
				return;

			if ( ! ($userdata = get_userdata( $user_id ) ) )
				return;

			$default_user_nicename = sanitize_title( $userdata->user_login );

			echo '<h3>'.__('User Nicename', 'seun').'</h3>'
				.'<table class="form-table">'."\n"
					.'<tr>'."\n"
						.'<th><label for="user_nicename">'.__('User nicename/slug', 'seun').'</label></th>'."\n"
						.'<td>'
							.'<input id="user_nicename" name="user_nicename" class="regular-text code" type="text" value="'.sanitize_title($userdata->user_nicename, $default_user_nicename).'"/> '
							.'<span class="description">('.sprintf(__('Leave empty for default value: %s', 'seun'), $default_user_nicename).')</span> '
							.'<a href="'.get_author_posts_url($user_id).'">'.__('Your Profile').'</a> '
						."</td>\n"
					.'</tr>'."\n"
				.'</table>'."\n";

		}

		/**
		 * Save user nicename
		 */
		function seun_save_user_options() {

			$user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;

			if ( ! isset( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'update-user_'.$user_id ) )
				return;

			if ( ! current_user_can('edit_users') )
				return;

			if ( ! isset( $_POST['user_nicename'] ) || ! ( $userdata = get_userdata( $user_id ) ) )
				return;

			$user_nicename		= $userdata->user_nicename;
			
			$default_user_nicename	= sanitize_title( $userdata->user_login );

			if ( sanitize_title($_POST['user_nicename'], $default_user_nicename) != $user_nicename )
				$new_nicename = sanitize_title($_POST['user_nicename'], $default_user_nicename);
			else
				return;

			if ( ! get_user_by('slug', $new_nicename ) ) {

				if ( ! wp_update_user( array ('ID' => $user_id, 'user_nicename' => $new_nicename ) ) )
					add_action('user_profile_update_errors', array( $this, 'seun_user_profile_nicename_generic_error' ), 10, 3 );

			} else {

				add_action('user_profile_update_errors', array( $this, 'seun_user_profile_nicename_error' ) , 10, 3 );

			}
		}

		/**
		 * Set generic error message
		 */
		function seun_user_profile_nicename_generic_error( $errors, $update, $user ) {

			$errors->add( 'user_nicename', __( '<strong>ERROR</strong>: There was an error updating the user nicename. Please try again.', 'seun' ) );
		
		}

		/**
		 * Set existing nicename error
		 */
		function seun_user_profile_nicename_error( $errors, $update, $user ) {
		
			$errors->add( 'user_nicename', __( '<strong>ERROR</strong>: This user nicename is already registered. Please choose another one.', 'seun' ) );
		
		}

	}

	$Simple_Edit_User_Nicename = new Simple_Edit_User_Nicename();

}