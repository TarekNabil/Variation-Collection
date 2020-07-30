<?php

/**
 * Provide a admin area view for the plugin
 *
 * @since      1.0.0
 *
 * @package   WordPress
 * @author    Tarek Nabil
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      https://tareknabil.net/variation-collection/
 */

?>
	<div class="wrap">		        
		<div class="wrap">
			<h2>Variation Collection Settings: </h2>  
			<form method="POST" action="options.php">  
				<?php 
					settings_fields( 'variation-collection-settings-group' );
					do_settings_sections( 'variation-collection-settings-group' ); 
				?>             
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Section Title</th>
						<td><input type="text" name="variation_collection_section_title" value="<?php echo esc_attr( get_option('variation_collection_section_title') ); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>  
			</form> 
			        
		</div>
	</div>