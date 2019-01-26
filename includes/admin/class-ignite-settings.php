<?php

namespace Ignite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // No direct access allowed ;)

class Settings {

	public $setting_name;
	public $options = array();

	public function __construct() {
		$this->setting_name = 'ignite_settings';
		$this->get_settings();
		$this->options = get_option( $this->setting_name );

		if ( empty( $this->options ) ) {
			update_option( $this->setting_name, array() );
		}

		add_action( 'admin_menu', array( $this, 'add_settings_menu' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Admin page settings
	 * */
	public function add_settings_menu() {
		add_submenu_page( 'ignite', __( 'Settings', 'ignite' ), __( 'Settings', 'ignite' ), 'ignite_setting', 'ignite-settings', array(
			$this,
			'render_settings'
		) );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Gets saved settings from WP core
	 *
	 * @since           2.0
	 * @return          array
	 */
	public function get_settings() {
		$settings = get_option( $this->setting_name );
		if ( ! $settings ) {
			update_option( $this->setting_name, array(
				'rest_api_status' => 1,
			) );
		}

		return apply_filters( 'ignite_get_settings', $settings );
	}

	/**
	 * Registers settings in WP core
	 *
	 * @since           2.0
	 * @return          void
	 */
	public function register_settings() {
		if ( false == get_option( $this->setting_name ) ) {
			add_option( $this->setting_name );
		}

		foreach ( $this->get_registered_settings() as $tab => $settings ) {
			add_settings_section(
				'ignite_settings_' . $tab,
				__return_null(),
				'__return_false',
				'ignite_settings_' . $tab
			);

			if ( empty( $settings ) ) {
				return;
			}

			foreach ( $settings as $option ) {
				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'ignite_settings[' . $option['id'] . ']',
					$name,
					array( $this, $option['type'] . '_callback' ),
					'ignite_settings_' . $tab,
					'ignite_settings_' . $tab,
					array(
						'id'      => isset( $option['id'] ) ? $option['id'] : null,
						'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'    => isset( $option['name'] ) ? $option['name'] : null,
						'section' => $tab,
						'size'    => isset( $option['size'] ) ? $option['size'] : null,
						'options' => isset( $option['options'] ) ? $option['options'] : '',
						'std'     => isset( $option['std'] ) ? $option['std'] : ''
					)
				);

				register_setting( $this->setting_name, $this->setting_name, array( $this, 'settings_sanitize' ) );
			}
		}
	}

	/**
	 * Gets settings tabs
	 *
	 * @since               2.0
	 * @return              array Tabs list
	 */
	public function get_tabs() {
		$tabs = array(
			'general' => __( 'General', 'ignite' ),
		);

		return $tabs;
	}

	/**
	 * Sanitizes and saves settings after submit
	 *
	 * @since               2.0
	 *
	 * @param               array $input Settings input
	 *
	 * @return              array New settings
	 */
	public function settings_sanitize( $input = array() ) {

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		parse_str( $_POST['_wp_http_referer'], $referrer );

		$settings = $this->get_registered_settings();
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'wp';

		$input = $input ? $input : array();
		$input = apply_filters( 'ignite_settings_' . $tab . '_sanitize', $input );

		// Loop through each setting being saved and pass it through a sanitization filter
		foreach ( $input as $key => $value ) {

			// Get the setting type (checkbox, select, etc)
			$type = isset( $settings[ $tab ][ $key ]['type'] ) ? $settings[ $tab ][ $key ]['type'] : false;

			if ( $type ) {
				// Field type specific filter
				$input[ $key ] = apply_filters( 'ignite_settings_sanitize_' . $type, $value, $key );
			}

			// General filter
			$input[ $key ] = apply_filters( 'ignite_settings_sanitize', $value, $key );
		}

		// Loop through the whitelist and unset any that are empty for the tab being saved
		if ( ! empty( $settings[ $tab ] ) ) {
			foreach ( $settings[ $tab ] as $key => $value ) {

				// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
				if ( is_numeric( $key ) ) {
					$key = $value['id'];
				}

				if ( empty( $input[ $key ] ) ) {
					unset( $this->options[ $key ] );
				}

			}
		}

		// Merge our new settings with the existing
		$output = array_merge( $this->options, $input );

		add_settings_error( 'ignite-notices', '', __( 'Settings updated', 'ignite' ), 'updated' );

		return $output;

	}

	/**
	 * Get settings fields
	 *
	 * @since           2.0
	 * @return          array Fields
	 */
	public function get_registered_settings() {

		$options = array(
			'enable'  => __( 'Enable', 'ignite' ),
			'disable' => __( 'Disable', 'ignite' )
		);

		$settings = apply_filters( 'ignite_registered_settings', array(
			// General tab
			'general' => array(
				'basic_fields'    => array(
					'id'   => 'basic_fields',
					'name' => __( 'Basic fields', 'ignite' ),
					'type' => 'header'
				),
				'ignite_text'     => array(
					'id'   => 'ignite_text',
					'name' => __( 'Text', 'ignite' ),
					'type' => 'text'
				),
				'ignite_number'   => array(
					'id'   => 'ignite_number',
					'name' => __( 'Number', 'ignite' ),
					'type' => 'number'
				),
				'ignite_textarea' => array(
					'id'   => 'ignite_textarea',
					'name' => __( 'Textarea', 'ignite' ),
					'type' => 'textarea'
				),
				'ignite_editor'   => array(
					'id'   => 'ignite_editor',
					'name' => __( 'Editor', 'ignite' ),
					'type' => 'rich_editor',
				),
				'dropdown_fields' => array(
					'id'   => 'dropdown_fields',
					'name' => __( 'Select fields', 'ignite' ),
					'type' => 'header'
				),
				'ignite_select'   => array(
					'id'      => 'ignite_select',
					'name'    => __( 'Select', 'ignite' ),
					'type'    => 'select',
					'options' => array(
						'bar'  => __( 'Bar', 'ignite' ),
						'line' => __( 'Line', 'ignite' ),
					)
				),
				'ignite_checkbox' => array(
					'id'   => 'ignite_checkbox',
					'name' => __( 'Checkbox', 'ignite' ),
					'type' => 'checkbox',
				),
				'ignite_radio'    => array(
					'id'      => 'ignite_radio',
					'name'    => __( 'Radio', 'ignite' ),
					'type'    => 'radio',
					'options' => array(
						'bar'  => __( 'Bar', 'ignite' ),
						'line' => __( 'Line', 'ignite' ),
					)
				),
				'advanced_fields' => array(
					'id'   => 'advanced_fields',
					'name' => __( 'Advanced fields', 'ignite' ),
					'type' => 'header'
				),
				'ignite_password' => array(
					'id'   => 'ignite_password',
					'name' => __( 'Password', 'ignite' ),
					'type' => 'password'
				),
				'ignite_color'    => array(
					'id'   => 'ignite_color',
					'name' => __( 'Color', 'ignite' ),
					'type' => 'color',
					'std'  => '#528CF9',
				),
				'ignite_upload'   => array(
					'id'   => 'ignite_upload',
					'name' => __( 'Upload', 'ignite' ),
					'type' => 'upload',
				),
			),
		) );

		return $settings;
	}

	public function header_callback( $args ) {
		echo '<hr/>';
	}

	public function html_callback( $args ) {
		echo $args['options'];
	}

	public function notice_callback( $args ) {
		echo $args['desc'];
	}

	public function checkbox_callback( $args ) {
		$checked = isset( $this->options[ $args['id'] ] ) ? checked( 1, $this->options[ $args['id'] ], false ) : '';
		$html    = '<input type="checkbox" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
		$html    .= '<label for="ignite_settings[' . $args['id'] . ']"> ' . __( 'Active', 'ignite' ) . '</label>';
		$html    .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function multicheck_callback( $args ) {
		$html = '';
		foreach ( $args['options'] as $key => $value ) {
			$option_name = $args['id'] . '-' . $key;
			$this->checkbox_callback( array(
				'id'   => $option_name,
				'desc' => $value
			) );
			echo '<br>';
		}

		echo $html;
	}

	public function radio_callback( $args ) {
		foreach ( $args['options'] as $key => $option ) :
			$checked = false;

			if ( isset( $this->options[ $args['id'] ] ) && $this->options[ $args['id'] ] == $key ) {
				$checked = true;
			} elseif ( isset( $args['std'] ) && $args['std'] == $key && ! isset( $this->options[ $args['id'] ] ) ) {
				$checked = true;
			}

			echo '<input name="ignite_settings[' . $args['id'] . ']"" id="ignite_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>';
			echo '<label for="ignite_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label>&nbsp;&nbsp;';
		endforeach;

		echo '<p class="description">' . $args['desc'] . '</p>';
	}

	public function text_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) and $this->options[ $args['id'] ] ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function number_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$max  = isset( $args['max'] ) ? $args['max'] : 999999;
		$min  = isset( $args['min'] ) ? $args['min'] : 0;
		$step = isset( $args['step'] ) ? $args['step'] : 1;

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function textarea_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<textarea class="large-text" cols="50" rows="5" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function password_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="password" class="' . $size . '-text" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function missing_callback( $args ) {
		echo '&ndash;';

		return false;
	}

	public function select_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html = '<select id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']"/>';

		foreach ( $args['options'] as $option => $name ) :
			$selected = selected( $option, $value, false );
			$html     .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function multiselect_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html     = '<select id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . '][]" multiple="true" class="chosen-select"/>';
		$selected = '';

		foreach ( $args['options'] as $k => $name ) :
			foreach ( $name as $option => $name ):
				if ( isset( $value ) AND is_array( $value ) ) {
					if ( in_array( $option, $value ) ) {
						$selected = " selected='selected'";
					} else {
						$selected = '';
					}
				}
				$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
			endforeach;
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function countryselect_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html     = '<select id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . '][]" multiple="true" class="chosen-select"/>';
		$selected = '';

		foreach ( $args['options'] as $option => $country ) :
			if ( isset( $value ) AND is_array( $value ) ) {
				if ( in_array( $country['code'], $value ) ) {
					$selected = " selected='selected'";
				} else {
					$selected = '';
				}
			}
			$html .= '<option value="' . $country['code'] . '" ' . $selected . '>' . $country['name'] . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}


	public function advancedselect_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		if ( is_rtl() ) {
			$class_name = 'chosen-select chosen-rtl';
		} else {
			$class_name = 'chosen-select';
		}

		$html = '<select class="' . $class_name . '" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']"/>';

		foreach ( $args['options'] as $key => $v ) {
			$html .= '<optgroup label="' . ucfirst( str_replace( '_', ' ', $key ) ) . '">';

			foreach ( $v as $option => $name ) :
				$disabled = ( $key == 'pro_pack_gateways' ) ? $disabled = ' disabled' : '';
				$selected = selected( $option, $value, false );
				$html     .= '<option value="' . $option . '" ' . $selected . ' ' . $disabled . '>' . ucfirst( $name ) . '</option>';
			endforeach;

			$html .= '</optgroup>';
		}

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function color_select_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html = '<select id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']"/>';

		foreach ( $args['options'] as $option => $color ) :
			$selected = selected( $option, $value, false );
			$html     .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function rich_editor_callback( $args ) {
		$rows  = isset( $args['size'] ) ? $args['size'] : 20;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$class = isset( $args['field_class'] ) ? $args['field_class'] : '';

		ob_start();
		wp_editor( stripslashes( $value ), 'ignite_settings_' . $args['id'], array( 'textarea_name' => 'ignite_settings[' . $args['id'] . ']', 'textarea_rows' => absint( $rows ), 'editor_class' => $class ) );
		$html = ob_get_clean();

		$html .= '<br/><label for="ignite_settings[' . $args['id'] . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

		echo $html;
	}

	public function upload_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text ignite_upload_field" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<span>&nbsp;<input type="button" class="ignite_settings_upload_button button-secondary" value="' . __( 'Upload File', 'ignite' ) . '"/></span>';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	public function color_callback( $args ) {
		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$default = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="ignite-color-picker" id="ignite_settings[' . $args['id'] . ']" name="ignite_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
		$html .= '<p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	/**
	 * Tabbable JavaScript codes & Initiate Color Picker
	 *
	 * This code uses localstorage for displaying active tabs
	 */
	public function script() {
		?>
        <script>
            jQuery(document).ready(function ($) {
                //Initiate Color Picker
                $('.ignite-color-picker').wpColorPicker();
            });
        </script>
		<?php
	}

	public function render_settings() {
		$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->get_tabs() ) ? $_GET['tab'] : 'general';

		ob_start();
		?>
        <div class="wrap ignite-settings-wrap">
            <h2><?php _e( 'Settings', 'ignite' ) ?></h2>
            <h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->get_tabs() as $tab_id => $tab_name ) {
					$tab_url = add_query_arg( array(
						'settings-updated' => false,
						'tab'              => $tab_id
					) );

					$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo $tab_name;
					echo '</a>';
				}
				?>
            </h2>
			<?php echo settings_errors( 'ignite-notices' ); ?>
            <div id="tab_container">
                <form method="post" action="options.php">
                    <table class="form-table">
						<?php
						settings_fields( $this->setting_name );
						do_settings_fields( 'ignite_settings_' . $active_tab, 'ignite_settings_' . $active_tab );
						?>
                    </table>
					<?php submit_button(); ?>
                </form>
            </div><!-- #tab_container-->
        </div><!-- .wrap -->
		<?php
		echo ob_get_clean();
		$this->script();
	}
}

new Settings();