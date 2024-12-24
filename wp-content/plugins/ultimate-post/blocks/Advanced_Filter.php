<?php

namespace ULTP\blocks;

use WP_Taxonomy;

defined( 'ABSPATH' ) || exit;

/**
 * Class Advanced_Filter
 */
class Advanced_Filter {

	private $pro_select_types = array(
		'adv_sort',
		'custom_tax',
	);

	public $order = array(
		array(
			'id'   => 'DESC',
			'name' => 'DESC',
		),
		array(
			'id'   => 'ASC',
			'name' => 'ASC',
		),
	);

	public $order_by = array(
		array(
			'id'   => 'date',
			'name' => 'Created Date',
		),
		array(
			'id'   => 'modified',
			'name' => 'Date Modified',
		),
		array(
			'id'   => 'title',
			'name' => 'Title',
		),
		array(
			'id'   => 'menu_order',
			'name' => 'Menu Order',
		),
		array(
			'id'   => 'rand',
			'name' => 'Random',
		),
		// array(
		// 'id'   => 'post__in',
		// 'name' => 'Post In',
		// ),
		array(
			'id'   => 'comment_count',
			'name' => 'Number of Comments',
		),
	);

	public $adv_sort = array(
		array(
			'id'   => 'popular_post_1_day_view',
			'name' => 'Popular Posts (1 Day - Views)',
		),
		array(
			'id'   => 'popular_post_7_days_view',
			'name' => 'Popular Posts (7 Days - Views)',
		),
		array(
			'id'   => 'popular_post_30_days_view',
			'name' => 'Popular Posts (30 Days - Views)',
		),
		array(
			'id'   => 'popular_post_all_times_view',
			'name' => 'Popular Posts (All Time - Views)',
		),
		array(
			'id'   => 'random_post',
			'name' => 'Random Posts',
		),
		array(
			'id'   => 'random_post_7_days',
			'name' => 'Random Posts (7 Days)',
		),
		array(
			'id'   => 'random_post_30_days',
			'name' => 'Random Posts (30 Days)',
		),
		array(
			'id'   => 'latest_post_published',
			'name' => 'Latest Posts - Published Date',
		),
		array(
			'id'   => 'latest_post_modified',
			'name' => 'Latest Posts - Last Modified Date',
		),
		array(
			'id'   => 'oldest_post_published',
			'name' => 'Oldest Posts - Published Date',
		),
		array(
			'id'   => 'oldest_post_modified',
			'name' => 'Oldest Posts - Last Modified Date',
		),
		array(
			'id'   => 'alphabet_asc',
			'name' => 'Alphabetical ASC',
		),
		array(
			'id'   => 'alphabet_desc',
			'name' => 'Alphabetical DESC',
		),
		array(
			'id'   => 'sticky_posts',
			'name' => 'Sticky Post',
		),
		array(
			'id'   => 'most_comment',
			'name' => 'Most Comments',
		),
		array(
			'id'   => 'most_comment_1_day',
			'name' => 'Most Comments (1 Day)',
		),
		array(
			'id'   => 'most_comment_7_days',
			'name' => 'Most Comments (7 Days)',
		),
		array(
			'id'   => 'most_comment_30_days',
			'name' => 'Most Comments (30 Days)',
		),
	);

	private function filter_select_options( $options, $filter ) {
		$res = array();
		$map = array();

		foreach ( $options as $option ) {
			$map[ $option['id'] ] = $option['name'];
		}

		foreach ( $filter as $f ) {

			if ( isset( $map[ $f ] ) ) {
				$res[] = array(
					'id'   => strtolower( $f ),
					'name' => $map[ $f ],
				);
			}
		}

		return $res;
	}

	/**
	 * Advanced_Filter constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_select_attributes() {
		return array(
			'advanceId'           => '',
			'blockId'             => '',
			'advanceCss'          => '',
			'filterStyle'         => 'dropdown',
			'filterValues'        => '["_all"]',
			'dropdownOptionsType' => 'all',
			'dropdownOptions'     => '["_all"]',
			'allText'             => 'All',
			'sPlaceholderText'    => 'Search...',
			'searchEnabled'       => false,
		);
	}

	public function get_search_attributes() {
		return array(
			'advanceId'   => '',
			'blockId'     => '',
			'advanceCss'  => '',
			'placeholder' => 'Search...',
		);
	}

	public function get_clear_attributes() {
		return array(
			'advanceId'           => '',
			'blockId'             => '',
			'clearButtonText'     => 'Clear Filters',
			'clearButtonPosition' => 'normal',
			'cAlign'              => 'margin-bottom: inherit',
		);
	}

	public function register() {
		register_block_type(
			'ultimate-post/filter-select',
			array(
				'editor_script'   => 'ultp-blocks-editor-script',
				'editor_style'    => 'ultp-blocks-editor-css',
				'render_callback' => array( $this, 'select_content' ),
			)
		);

		register_block_type(
			'ultimate-post/filter-search-adv',
			array(
				'editor_script'   => 'ultp-blocks-editor-script',
				'editor_style'    => 'ultp-blocks-editor-css',
				'render_callback' => array( $this, 'search_content' ),
			)
		);

		register_block_type(
			'ultimate-post/filter-clear',
			array(
				'editor_script'   => 'ultp-blocks-editor-script',
				'editor_style'    => 'ultp-blocks-editor-css',
				'render_callback' => array( $this, 'clear_content' ),
			)
		);
	}

	public function get_button_data( $type, $ids, $post_types = '', $allText = 'All' ) {
		$res = array();

		if ( in_array( '_all', $ids ) ) {
			$res['_all'] = $allText;
			// $ids = array_filter($ids, function ($item) {
			// return $item != '_all';
			// });
		}

		$ids = implode( ',', $ids );

		switch ( $type ) {
			case 'adv_sort':
				$adv_sort_id = explode( ',', $ids );
				foreach ( $this->adv_sort as $adv ) {
					foreach ( $adv_sort_id as $id ) {
						if ( $adv['id'] === $id ) {
							$res[ $id ] = $adv['name'];
						}
					}
				}
				break;
			case 'category':
				if ( ! empty( $ids ) && $ids !== '_all' ) {
					$categories = get_categories(
						array(
							'per_page' => -1,
							'include'  => $ids,
						),
					);

					foreach ( $categories as $category ) {
						$res[ $category->slug ] = $category->name;
					}
				}
				break;
			case 'tags':
				if ( ! empty( $ids ) && $ids !== '_all' ) {
					$tags = get_tags(
						array(
							'per_page' => -1,
							'include'  => $ids,
						),
					);

					foreach ( $tags as $tag ) {
						$res[ $tag->slug ] = $tag->name;
					}
				}
				break;
			case 'author':
				if ( ! empty( $ids ) && $ids !== '_all' ) {
					$authors = get_users(
						array(
							'per_page' => -1,
							'role__in' => array( 'author' ),
							'include'  => $ids,
						),
					);

					foreach ( $authors as $author ) {
						$res[ $author->ID ] = $author->display_name;
					}
				}

				break;
			case 'order':
				foreach ( $this->order as $order ) {
					$res[ $order['id'] ] = $order['name'];
				}
				break;
			case 'order_by':
				$orders = explode( ',', $ids );
				foreach ( $this->order_by as $order_by ) {
					foreach ( $orders as $order ) {
						if ( $order_by['id'] === $order ) {
							$res[ $order ] = $order_by['name'];
						}
					}
				}
				break;
			case 'custom_tax':
				if ( $post_types == '' ) {
					break;
				}

				$post_types = json_decode( $post_types );

				foreach ( $post_types as $post_type ) {
					$taxonomies = get_object_taxonomies( $post_type );
					foreach ( $taxonomies as $taxonomy ) {
						$terms = get_terms(
							array(
								'taxonomy'   => $taxonomy,
								'hide_empty' => false,
							)
						);
						foreach ( $terms as $term ) {
							$res[ $term->slug ] = array(
								'name'     => $term->name,
								'taxonomy' => $taxonomy,
							);
						}
					}
				}

			default:
				break;
		}

		return $res;
	}

	/**
	 * Inserts all option depending on condition
	 *
	 * @param array  $arr array.
	 * @param array  $item all item.
	 * @param int    $idx all item idx.
	 * @param string $mode mode.
	 * @return void
	 */
	private function maybe_insert_all_option( &$arr, &$item, $idx, $mode ) {
		if ( 'all' === $mode ) {
			array_unshift( $arr, $item );
		} elseif ( ! empty( $item ) && false !== $idx ) {
			array_splice( $arr, $idx, 0, array( $item ) );
		}
	}

	public function get_select_data( $type, $all_text, $post_types = '', $specific = array(), $mode = 'all' ) {

		$all               = null;
		$all_idx           = array_search( '_all', $specific, true );
		$filtered_specific = $specific;

		if ( false !== $all_idx || 'all' === $mode ) {
			$all = array(
				'id'   => '_all',
				'name' => $all_text,
			);

			$filtered_specific = array_filter(
				$specific,
				function ( $item ) {
					return '_all' !== $item;
				}
			);
		}

		switch ( $type ) {

			case 'category':
				$categories = array();

				if ( 'all' === $mode || ( 'specific' === $mode && count( $filtered_specific ) > 0 ) ) {
					$categories = get_categories(
						array(
							'per_page' => -1,
							'include'  => $filtered_specific,
							'orderby'  => 'include',
						)
					);

					$categories = array_map(
						function ( $category ) {
							return array(
								'id'   => $category->slug,
								'name' => $category->name,
							);
						},
						$categories
					);
				}

				$this->maybe_insert_all_option( $categories, $all, $all_idx, $mode );

				return $categories;

			case 'tags':
				$tags = array();

				if ( 'all' === $mode || ( 'specific' === $mode && count( $filtered_specific ) > 0 ) ) {
					$tags = get_tags(
						array(
							'per_page' => -1,
							'include'  => $filtered_specific,
						)
					);
					$tags = array_map(
						function ( \WP_Term $tag ) {
							return array(
								'id'   => $tag->slug,
								'name' => $tag->name,
							);
						},
						$tags
					);
				}

				$this->maybe_insert_all_option( $tags, $all, $all_idx, $mode );

				return $tags;

			case 'author':
				$authors = array();

				if ( 'all' === $mode || ( 'specific' === $mode && count( $filtered_specific ) > 0 ) ) {

					$authors = get_users(
						array(
							'per_page' => -1,
							'include'  => $filtered_specific,
						)
					);

					$authors = array_map(
						function ( $author ) {
							return array(
								'id'   => $author->ID,
								'name' => $author->display_name,
							);
						},
						$authors
					);
				}

				$this->maybe_insert_all_option( $authors, $all, $all_idx, $mode );

				return $authors;

			case 'order':
				$filtered_specific = array_map( 'strtoupper', $filtered_specific );

				return 'specific' === $mode ?
				$this->filter_select_options( $this->order, $filtered_specific )
				: $this->order;

			case 'order_by':
				return 'specific' === $mode ?
				$this->filter_select_options( $this->order_by, $filtered_specific )
				: $this->order_by;

			case 'adv_sort':
				$data = 'specific' === $mode ?
				$this->filter_select_options( $this->adv_sort, $filtered_specific )
				: $this->adv_sort;

				$this->maybe_insert_all_option( $data, $all, $all_idx, $mode );

				return $data;

			case 'custom_tax':
				if ( '' == $post_types ) {
					return array();
				}

				$post_types = json_decode( $post_types );

				$data = array();

				foreach ( $post_types as $post_type ) {
					$taxonomies = get_object_taxonomies( sanitize_text_field( $post_type ) );
					foreach ( $taxonomies as $taxonomy ) {
						$terms = array();

						if ( 'specific' === $mode && count( $filtered_specific ) > 0 ) {
							foreach ( $filtered_specific as $s ) {
								$res = get_term_by( 'slug', $s, $taxonomy );
								if ( ! empty( $res ) ) {
									$terms[] = $res;
								}
							}
						} else {
							$terms = get_terms(
								array(
									'taxonomy'   => $taxonomy,
									'hide_empty' => false,
								)
							);
						}

						foreach ( $terms as $term ) {
							$data[] = array(
								'id'       => $term->slug,
								'name'     => $term->name,
								'taxonomy' => $taxonomy,
							);
						}
					}
				}

				$this->maybe_insert_all_option( $data, $all, $all_idx, $mode );

				return $data;
			default:
				return array();
		}
	}

	public function select_content( $attr ) {
		$block_name = 'filter-select';
		$is_active  = ultimate_post()->is_lc_active();
		$attr       = wp_parse_args( $attr, $this->get_select_attributes() );

		if ( ! $is_active && in_array( $attr['type'], $this->pro_select_types ) ) {
			return '';
		}

		$post_types = isset( $attr['postTypes'] ) ? $attr['postTypes'] : '';

		$attr['blockId'] = ultimate_post()->sanitize_attr( $attr, 'blockId', 'sanitize_html_class', 'missing_block_id' );
		$attr['allText'] = ultimate_post()->sanitize_attr( $attr, 'allText', 'sanitize_text_field', 'missing_block_id' );

		if ( 'inline' === $attr['filterStyle'] ) {
			$inline_values = json_decode( $attr['filterValues'], true );

			if ( ! is_array( $inline_values ) ) {
				return '';
			}

			$data = $this->get_button_data( $attr['type'], $inline_values, $post_types, $attr['allText'] );

			$btn_wrapper_attrs = get_block_wrapper_attributes(
				array(
					'class'          => 'ultp-block-' . $attr['blockId'] . ' ultp-filter-button',
					'role'           => 'button',
					'data-blockId'   => $attr['blockId'],
					'data-is-active' => 'false',
				)
			);

			ob_start();
			?>

			<div class="ultp-block-<?php echo esc_attr( $attr['blockId'] ); ?>-wrapper">
			<?php foreach ( $data as $key => $value ) : ?>
				<?php
				if ( is_array( $value ) ) {
					$name = $value['name'];
					$tax  = isset( $value['taxonomy'] ) ? 'data-tax="' . esc_attr( $value['taxonomy'] ) . '"' : '';
				} else {
					$name = $value;
					$tax  = '';
				}
				?>
				<div <?php echo $btn_wrapper_attrs; ?> <?php echo $tax; ?> data-selected="<?php echo esc_attr( $key ); ?>" data-type="<?php echo esc_attr( $attr['type'] ); ?>">
					<?php echo $name; ?>
				</div>
			<?php endforeach ?>
			</div>
			<?php

			$content = ob_get_clean();
			return $content;
		} elseif ( 'dropdown' === $attr['filterStyle'] ) {

			$mode     = 'all';
			$specific = array();

			if ( 'specific' === $attr['dropdownOptionsType'] && isset( $attr['dropdownOptions'] ) ) {
				$specific_data = json_decode( stripslashes( $attr['dropdownOptions'] ) );
				$mode          = 'specific';
				if ( is_array( $specific_data ) ) {
					$specific = array_map(
						function ( $item ) {
							return sanitize_text_field( $item );
						},
						$specific_data
					);
				}
			}

			$data = $this->get_select_data( $attr['type'], $attr['allText'], $post_types, $specific, $mode );

			$def_value = ! empty( current( $data ) ) ? current( $data ) : null;

			$wrapper_attrs = get_block_wrapper_attributes(
				array(
					'class'         => 'ultp-block-' . $attr['blockId'] . ' ultp-filter-select',
					'data-selected' => isset( $def_value['id'] ) ? $def_value['id'] : 0,
					'data-type'     => $attr['type'],
					'data-blockId'  => $attr['blockId'],
					'aria-expanded' => 'false',
					'aria-label'    => 'Select Filter (' . $attr['type'] . ')',
				)
			);

			ob_start();
			?>
			<div <?php echo $wrapper_attrs; ?>>
				<div class="ultp-filter-select-field ultp-filter-select__parent">
					<span class="ultp-filter-select-field-selected ultp-filter-select__parent-inner">
				<?php echo esc_html( isset( $def_value['name'] ) ? $def_value['name'] : '' ); ?>
					</span>
					<span class="ultp-filter-select-field-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34.1 19.95"><path d="M17.05 19.949.601 3.499a2.05 2.05 0 0 1 2.9-2.9l13.551 13.55L30.603.599a2.05 2.05 0 0 1 2.9 2.9Z"/></svg>
					</span>
				</div>
				<ul style="display: none;" class="ultp-filter-select-options ultp-filter-select__dropdown">

					<?php
					if ( isset( $attr['searchEnabled'] ) && $attr['searchEnabled'] ) :
						?>
						<input 
							type="search" 
							class="ultp-filter-select-search" 
							placeholder="<?php echo esc_attr( isset( $attr['sPlaceholderText'] ) ? $attr['sPlaceholderText'] : '' ); ?>" 
						/>
					<?php endif; ?>

					<?php foreach ( $data as $item ) : ?>
						<?php
							$tax = isset( $item['taxonomy'] ) ? 'data-tax="' . esc_attr( $item['taxonomy'] ) . '"' : '';
						?>
						<li class="ultp-filter-select__dropdown-inner" <?php echo $tax; ?> data-id="<?php echo esc_attr( $item['id'] ); ?>">
							<?php echo esc_html( $item['name'] ); ?>
						</li>
					<?php endforeach; ?>

				</ul>
			</div>

			<?php
			$content = ob_get_clean();
			return $content;
		}

		return '';
	}

	public function search_content( $attr ) {
		$block_name = 'filter-search-adv';
		$is_active  = ultimate_post()->is_lc_active();

		if ( ! $is_active ) {
			return '';
		}

		$attr = wp_parse_args( $attr, $this->get_search_attributes() );

		$attr['blockId']     = ultimate_post()->sanitize_attr( $attr, 'blockId', 'sanitize_html_class', 'missing_block_id' );
		$attr['placeholder'] = ultimate_post()->sanitize_attr( $attr, 'placeholder', 'sanitize_text_field' );

		$wrapper_attrs = get_block_wrapper_attributes(
			array(
				'class'      => 'ultp-block-' . $attr['blockId'] . ' ultp-filter-search',
				'aria-label' => 'Search Filter',
				'role'       => 'searchbox',
			)
		);

		ob_start();
		?>
		<div <?php echo $wrapper_attrs; ?>>
			<div class="ultp-filter-search-input">
				<input
					type="search"
					placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>"
				/>
				<span class="ultp-filter-search-input-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.05 47.05"><path stroke="rgba(0,0,0,0)" strokeMiterlimit="10" d="m43.051 45.948-9.618-9.616a20.183 20.183 0 1 1 2.9-2.9l9.617 9.616a2.05 2.05 0 1 1-2.9 2.9Zm-22.367-9.179A16.084 16.084 0 1 0 4.6 20.684a16.1 16.1 0 0 0 16.084 16.085Z"/></svg>
				</span>
			</div>
		</div>
		<?php
		$content = ob_get_clean();
		return $content;
	}

	public function clear_content( $attr ) {
		$block_name = 'filter-clear';
		// $is_active     = ultimate_post()->is_lc_active();
		$attr = wp_parse_args( $attr, $this->get_clear_attributes() );

		$attr['blockId']         = ultimate_post()->sanitize_attr( $attr, 'blockId', 'sanitize_html_class', 'missing_block_id' );
		$attr['clearButtonText'] = ultimate_post()->sanitize_attr( $attr, 'clearButtonText', 'sanitize_text_field' );

		$wrapper_attrs = get_block_wrapper_attributes(
			array(
				'class'        => 'ultp-block-' . $attr['blockId'] . ' ultp-filter-clear ultp-filter-clear-button ',
				'data-blockid' => $attr['blockId'],
			)
		);

		$selected_filter_wrapper_attr = get_block_wrapper_attributes(
			array(
				'class' => 'ultp-block-' . $attr['blockId'] . ' ultp-filter-clear ultp-filter-clear-template',
				'style' => 'display: none;',
			)
		);

		ob_start();
		?>

		<div class="ultp-block-<?php echo $attr['blockId']; ?>-wrapper">
			<div <?php echo $selected_filter_wrapper_attr; ?>>
				<div class="ultp-selected-filter">
					<span class="ultp-selected-filter-icon" role="button">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31.1 31.25"><path stroke="rgba(0,0,0,0)" strokeMiterlimit="10" d="M27.1 30.153 15.549 18.601 4 30.153a2.05 2.05 0 0 1-2.9-2.9l11.551-11.55L1.1 4.153a2.05 2.05 0 0 1 2.9-2.9l11.549 11.552L27.1 1.253a2.05 2.05 0 0 1 2.9 2.9l-11.553 11.55L30 27.253a2.05 2.05 0 1 1-2.9 2.9Z"/></svg>
					</span>
					<span class="ultp-selected-filter-text">
					</span>
				</div>
			</div>

			<div <?php echo $wrapper_attrs; ?>>
				<button class="ultp-clear-button">
					<?php echo esc_html( $attr['clearButtonText'] ); ?>
				</button>
			</div>
		</div>

		<?php
		$content = ob_get_clean();
		return $content;
	}
}
