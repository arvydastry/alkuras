<?php
/**
 * Pristatymo būdas: vežama iš artimiausio karjero pagal kliento miestą.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Užregistruoja pristatymo būdo klasę.
 */
function alkuras_shipping_method_init() {

	if ( class_exists( 'Alkuras_Shipping_Method' ) || ! class_exists( 'WC_Shipping_Method' ) ) {
		return;
	}

	/**
	 * Pristatymas iš karjero.
	 */
	class Alkuras_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Konstruktorius.
		 *
		 * @param int $instance_id Zonos instancijos ID.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'alkuras_karjeras';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = __( 'Pristatymas iš karjero', 'alkuras' );
			$this->method_description = __( 'Kaina skaičiuojama pagal artimiausią karjerą, atstumą ir užsakytą kiekį tonomis. Įkainiai keičiami Alkuras → Nustatymai.', 'alkuras' );
			$this->supports           = array( 'shipping-zones', 'instance-settings', 'settings' );

			$this->init();
		}

		/**
		 * Nustatymai.
		 */
		public function init() {
			$this->init_form_fields();
			$this->init_settings();

			$this->title   = $this->get_option( 'title', __( 'Atvežimas iš artimiausio karjero', 'alkuras' ) );
			$this->enabled = $this->get_option( 'enabled', 'yes' );

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Laukai.
		 */
		public function init_form_fields() {
			$this->instance_form_fields = array(
				'title'    => array(
					'title'       => __( 'Pavadinimas', 'alkuras' ),
					'type'        => 'text',
					'default'     => __( 'Atvežimas iš artimiausio karjero', 'alkuras' ),
					'description' => __( 'Matomas klientui krepšelyje.', 'alkuras' ),
					'desc_tip'    => true,
				),
				'pickup'   => array(
					'title'       => __( 'Atsiėmimas karjere', 'alkuras' ),
					'type'        => 'checkbox',
					'label'       => __( 'Siūlyti nemokamą atsiėmimą savo transportu', 'alkuras' ),
					'default'     => 'yes',
				),
				'fallback' => array(
					'title'       => __( 'Kaina, kai miestas nerastas, €', 'alkuras' ),
					'type'        => 'text',
					'default'     => '',
					'description' => __( 'Palikus tuščią, nežinomiems miestams pristatymas nerodomas ir klientas raginamas susisiekti.', 'alkuras' ),
					'desc_tip'    => true,
				),
			);
			$this->form_fields = $this->instance_form_fields;
		}

		/**
		 * Apskaičiuoja pristatymą.
		 *
		 * @param array $package Krepšelio paketas.
		 */
		public function calculate_shipping( $package = array() ) {
			$tons = 0.0;
			foreach ( $package['contents'] as $item ) {
				$tons += (float) $item['quantity'];
			}
			if ( $tons <= 0 ) {
				return;
			}

			$city  = $package['destination']['city'] ?? '';
			$quote = $city ? alkuras_calc_delivery( $city, $tons ) : null;

			if ( $quote ) {
				$label = $this->title;
				if ( ! empty( $quote['quarry']['name'] ) ) {
					/* translators: 1: method title, 2: quarry name, 3: distance */
					$label = sprintf( __( '%1$s — %2$s (%3$s km)', 'alkuras' ), $this->title, $quote['quarry']['name'], $quote['km'] );
				}

				$this->add_rate(
					array(
						'id'       => $this->id . ':' . $this->instance_id,
						'label'    => $label,
						'cost'     => round( $quote['price'], 2 ),
						'taxes'    => '',
						'meta_data'=> array(
							__( 'Karjeras', 'alkuras' ) => $quote['quarry']['name'] ?? '',
							__( 'Atstumas', 'alkuras' ) => $quote['km'] . ' km',
							__( 'Reisai', 'alkuras' )   => $quote['loads'],
						),
					)
				);
			} else {
				$fallback = $this->get_option( 'fallback', '' );
				if ( '' !== $fallback ) {
					$this->add_rate(
						array(
							'id'    => $this->id . ':' . $this->instance_id . ':fallback',
							'label' => $this->title,
							'cost'  => round( (float) str_replace( ',', '.', $fallback ), 2 ),
						)
					);
				}
			}

			if ( 'yes' === $this->get_option( 'pickup', 'yes' ) ) {
				$this->add_rate(
					array(
						'id'    => $this->id . ':' . $this->instance_id . ':pickup',
						'label' => __( 'Atsiėmimas karjere savo transportu', 'alkuras' ),
						'cost'  => 0,
					)
				);
			}
		}
	}
}
add_action( 'woocommerce_shipping_init', 'alkuras_shipping_method_init' );

/**
 * Prideda metodą į WooCommerce sąrašą.
 *
 * @param array $methods Metodai.
 * @return array
 */
function alkuras_add_shipping_method( $methods ) {
	$methods['alkuras_karjeras'] = 'Alkuras_Shipping_Method';
	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'alkuras_add_shipping_method' );

/**
 * Perskaičiuoja pristatymą pasikeitus miestui.
 *
 * @param array $packages Paketai.
 * @return array
 */
function alkuras_shipping_package_hash( $packages ) {
	foreach ( $packages as $i => $package ) {
		$packages[ $i ]['alkuras_tons'] = array_sum( wp_list_pluck( $package['contents'], 'quantity' ) );
	}
	return $packages;
}
add_filter( 'woocommerce_cart_shipping_packages', 'alkuras_shipping_package_hash' );

/**
 * Krepšelio kiekis rodomas tonomis.
 *
 * @param string $html    HTML.
 * @param array  $item    Elementas.
 * @return string
 */
function alkuras_cart_item_quantity_unit( $html, $item ) {
	return $html;
}
add_filter( 'woocommerce_cart_item_quantity', 'alkuras_cart_item_quantity_unit', 10, 2 );
