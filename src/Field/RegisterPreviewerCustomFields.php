<?php

namespace GFPDF\Plugins\Previewer\Field;

use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;
use GFPDF\Helper\Helper_Trait_Logger;
use GPDFAPI;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RegisterPreviewerCustomFields
 *
 * @package GFPDF\Plugins\Previewer\Field
 */
class RegisterPreviewerCustomFields implements Helper_Interface_Actions, Helper_Interface_Filters {

	/*
	 * Add logging support
	 *
	 * @since 0.2
	 */
	use Helper_Trait_Logger;

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gform_field_standard_settings', [ $this, 'add_pdf_selector' ], 10, 2 );
		add_action( 'gform_field_standard_settings', [ $this, 'add_pdf_preview_height' ] );
		add_action( 'gform_field_standard_settings', [ $this, 'add_pdf_download_support' ] );
		add_action( 'gform_field_standard_settings', [ $this, 'add_pdf_watermark_support' ] );
		add_action( 'gform_editor_js', [ $this, 'editor_js' ] );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gform_tooltips', [ $this, 'add_tooltips' ] );
	}

	/**
	 * Add tooltip support for our new PDF Preview form editor fields
	 *
	 * @param array $tooltips
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_tooltips( $tooltips ) {
		$tooltips['pdf_selector_setting']  = '<h6>' . esc_html__( 'PDF to Preview', 'gravity-pdf-previewer' ) . '</h6>' . esc_html__( 'Select one of the active PDFs you want the end-user to preview before the form is submitted.', 'gravity-pdf-previewer' );
		$tooltips['pdf_preview_height']    = '<h6>' . esc_html__( 'Preview Height', 'gravity-pdf-previewer' ) . '</h6>' . esc_html__( 'Set the PDF Preview height the document will be displayed at (in pixels). The default height is 600px.', 'gravity-pdf-previewer' );
		$tooltips['pdf_watermark_setting'] = '<h6>' . esc_html__( 'Watermark', 'gravity-pdf-previewer' ) . '</h6>' . esc_html__( 'Add a diagonal text-based watermark to each page of the PDF Preview and control the font type used.', 'gravity-pdf-previewer' );

		return $tooltips;
	}

	/**
	 * Add support for a PDF selector field in the Form Editor
	 *
	 * @param init $position
	 * @param int  $form_id
	 *
	 * @since 0.1
	 */
	public function add_pdf_selector( $position, $form_id ) {
		if ( $position === 25 ) {
			$this->get_logger()->notice( 'Add PDF Selector field to form editor' );

			$pdfs              = $this->get_active_pdfs( $form_id );
			$form_pdf_settings = network_admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview=pdf&id=' . $form_id );
			include __DIR__ . '/markup/pdf-selector-setting.php';
		}
	}

	/**
	 * Return a list of active PDFs for our form
	 *
	 * @param int $form_id
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function get_active_pdfs( $form_id ) {
		$pdfs = GPDFAPI::get_form_pdfs( $form_id );

		if ( is_wp_error( $pdfs ) ) {
			return [];
		}

		/* Filter the inactive PDFs */
		$pdfs = array_filter(
			$pdfs,
			function( $pdf ) {
				return $pdf['active'];
			}
		);

		$this->get_logger()->notice(
			'Active PDFs on form',
			[
				'pdfs' => $pdfs,
			]
		);

		return $pdfs;
	}

	/**
	 * Add support for a PDF Height field in the Form Editor
	 *
	 * @param init $position
	 *
	 * @since 0.1
	 */
	public function add_pdf_preview_height( $position ) {
		if ( $position === 25 ) {
			$this->get_logger()->notice( 'Add PDF Height selector to form editor' );

			include __DIR__ . '/markup/preview-height-setting.php';
		}
	}

	/**
	 * Add support for PDF Watermark fields in the Form Editor
	 *
	 * @param init $position
	 *
	 * @since 0.1
	 */
	public function add_pdf_watermark_support( $position ) {
		if ( $position === 25 ) {
			$this->get_logger()->notice( 'Add PDF Watermark fields to form editor' );

			$font_stack = GPDFAPI::get_pdf_fonts();
			include __DIR__ . '/markup/pdf-watermark-setting.php';
		}
	}

	/**
	 * Add support for PDF Download Toggle in the Form Editor
	 *
	 * @param init $position
	 *
	 * @since 0.1
	 */
	public function add_pdf_download_support( $position ) {
		if ( $position === 25 ) {
			$this->get_logger()->notice( 'Add PDF Download toggle to form editor' );
			include __DIR__ . '/markup/pdf-download-setting.php';
		}
	}

	/**
	 * Load our custom form editor JS to ensure our custom PDF Preview fields save and update correctly
	 *
	 * @since 0.1
	 */
	public function editor_js() {
		$this->get_logger()->notice( 'Load PDF Preview Editor Javascript' );

		?>
		<script type="text/javascript">

		  /* Setup default values for our PDF Preview field */
		  function SetDefaultValues_pdfpreview (field) {
			field['label'] = <?php echo json_encode( __( 'PDF Preview', 'gravity-pdf-previewer' ) ); ?>;
			field['pdfpreviewheight'] = "600";
			field['pdfwatermarktext'] = <?php echo json_encode( __( 'SAMPLE', 'gravity-pdf-previewer' ) ); ?>;
			field['pdfwatermarkfont'] = <?php echo json_encode( GPDFAPI::get_plugin_option( 'default_font', 'dejavusanscondensed' ) ); ?>;

			return field;
		  }

		  <?php echo file_get_contents( __DIR__ . '/markup/editor.js' ); ?>
		</script>
		<?php
	}
}
