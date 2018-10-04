<?php

add_action( 'wp_enqueue_scripts', 'twentyseventeen_parent_theme_enqueue_styles' );

function twentyseventeen_parent_theme_enqueue_styles() {
	wp_enqueue_style( 'twentyseventeen-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'twentyseventeen-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'twentyseventeen-style' )
	);
	wp_enqueue_style( 'bootstrap',
		'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css',
		array( 'twentyseventeen-style' )
	);
	wp_enqueue_script( 'wp-api' );
	wp_localize_script( 'wp-api', 'wpApiSettings', [
		'root'                 => esc_url_raw( rest_url() ),
		'isdaVersionString'    => 'isda/v1/',
		'nonce'                => wp_create_nonce( 'wp_rest' ),
		'siteUrl'              => esc_url_raw( get_site_url() ),
		'isSingle'             => is_single() ? true : false,
		'postType'             => get_post_type(),
		'postId'               => get_the_ID(),
		'templateDirectoryUri' => get_template_directory_uri(),
		'pageUrl'              => get_permalink( get_the_ID() ),
		'pagePath'             => str_replace( get_site_url(), '', get_permalink( get_the_ID()) ),
		'title'                => get_the_title( get_the_ID() ),
	] );
	wp_enqueue_script( 'vue', 'https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js' );
	wp_enqueue_script( 'vee-validate', 'https://cdnjs.cloudflare.com/ajax/libs/vee-validate/2.0.9/vee-validate.js', ['vue' ] );
	wp_enqueue_script( 'axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js', ['vue' ] );


}

$Form_Validation = new Form_Validation();
/*echo '<pre>';
var_dump( $Form_Validation->validate_phone( 'text' ) );
var_dump( $Form_Validation->validate_phone( 1234567 ) );
var_dump( $Form_Validation->get_front_validation() );
die(); */

class Form_Validation {
	public $methids = [];
	public $validation_definitions = [
		'name'  => [
			'min'         => 2,
			'max'         => 50,
			'regex'       => '#^[a-z\d\-\.,_\s]+$#i',
			'is_required' => true,
		],
		'phone' => [
			'min'   => 7,
			'max'   => 15,
			'regex' => '/^[0-9\+\-x\(\) ]+$/',
		],
		'age'   => [
			'min'         => 2,
			'max'         => 3,
			'regex'       => '#^[0-9]+$#',
			'is_required' => true,
		],
	];

	function get_validation_definitions() {
		return $this->validation_definitions;
	}

	function test( $request ) {
		return $this->validate_phone( $request );
	}

	public function __construct() {
		$this->set_methods();
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}


	public function validate_regex_field( $input, $regex ) {
		if ( ! empty( $input ) ) {
			return preg_match( $regex, $input );
		}

		return '';
	}

	function set_methods() {
		foreach ( $this->validation_definitions as $field_key => $validation ) {
			$field_key_full                             = $field_key;
			$field_key                                  = ltrim( $field_key, '_' );
			$validation_function_name                   = 'validate_' . $field_key;
			$validation_function                        = function ( $request ) use ( $field_key_full ) {
				if ( $this->validation_definitions[ $field_key_full ]['is_required'] && empty( trim( $request ) ) ) {
					return false;
				}
				if ( isset( $this->validation_definitions[ $field_key_full ]['min'] ) && strlen( trim( $request ) ) < $this->validation_definitions[ $field_key_full ]['min'] && strlen( trim( $request ) ) > 0 ) {

					return false;
				}
				if ( isset( $this->validation_definitions[ $field_key_full ]['max'] ) && strlen( trim( $request ) ) > $this->validation_definitions[ $field_key_full ]['max'] ) {
					return false;
				}
				if ( isset( $this->validation_definitions[ $field_key_full ]['regex'] ) ) {
					if ( ! $this->validate_regex_field( $request, $this->validation_definitions[ $field_key_full ]['regex'] ) && ! empty( trim( $request ) ) ) {
						return false;
					}
				}

				return true;
			};
			$this->methods[ $validation_function_name ] = \Closure::bind( $validation_function, $this, get_class() );
		}
	}


	function register_routes() {
		$base = 'save-form';
		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_form' ],
				'permission_callback' => [ $this, 'is_user_logged_in' ],
				'args'                => $this->get_args_list()
			]
		] );
	}

	function get_args_list() {
		$args_array = [];
		foreach ( array_keys( $this->get_validation_definitions() ) as $arg ) {
			$args_array[ $arg ]['validate_callback'] = [ $this, 'validate_' . $arg ];
		}

		return $args_array;
	}

	function __call( $method, $args = [] ) {
		if ( is_callable( $this->methods[ $method ] ) ) {
			return call_user_func_array( $this->methods[ $method ], $args );
		}
	}

	public function get_front_validation() {
		$front_validation = [];
		foreach ( $this->validation_definitions as $key => $validation ) {
			$field_validation = [];
			if ( isset( $validation['is_required'] ) && $validation['is_required'] == true ) {
				$field_validation['required'] = true;
			}
			if ( isset( $validation['min'] ) ) {
				$field_validation['min'] = $validation['min'];
			}
			if ( isset( $validation['max'] ) ) {
				$field_validation['max'] = $validation['max'];
			}
			if ( isset( $validation['regex'] ) ) {
				$field_validation['regex']['i'] = '';
				$string                         = $validation['regex'];
				if ( substr( $string, - 1 ) == 'i' ) {
					$field_validation['regex']['i'] = 'i';
					$string                         = substr( $string, 0, - 1 );
				}
				$field_validation['regex']['rule'] = substr( $string, 1, - 1 );
			}
			$front_validation[ $key ] = $field_validation;
		}

		return $front_validation;
	}

}