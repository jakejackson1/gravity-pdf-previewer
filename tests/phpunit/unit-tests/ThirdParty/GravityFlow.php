<?php

namespace GFPDF\Tests\Previewer;

use WP_UnitTestCase;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TestRegisterPreviewerField
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   thirdparty
 */
class TestGravityFlow extends WP_UnitTestCase {

	/**
	 * @var \GFPDF\Plugins\Previewer\ThirdParty\GravityFlow
	 *
	 * @since 1.1
	 */
	protected $class;

	/**
	 * @since 1.1
	 */
	public function setUp() {
		$this->class = $this->getMockBuilder( 'GFPDF\Plugins\Previewer\ThirdParty\GravityFlow' )
							->setMethods( [ 'get_form', 'get_entry' ] )
							->getMock();

		$this->class
			->expects( $this->any() )
			->method( 'get_form' )
			->will( $this->onConsecutiveCalls( false, $this->mock_form_object() ) );

		$this->class
			->expects( $this->any() )
			->method( 'get_entry' )
			->will( $this->returnValue( $this->mock_entry_object() ) );
	}

	/**
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function mock_form_object() {
		$form = [
			'id'     => 2,
			'fields' => [],
		];

		for ( $i = 0; $i < 5; $i++ ) {
			$field            = new Field();
			$field->id        = $i;
			$form['fields'][] = $field;
		}

		for ( $i = 6; $i < 8; $i++ ) {
			$field     = new Field_Name();
			$field->id = $i;

			$form['fields'][] = $field;
		}

		for ( $i = 9; $i < 11; $i++ ) {
			$field       = new Field();
			$field->id   = $i;
			$field->type = 'pdfpreview';

			$form['fields'][] = $field;
		}

		return $form;
	}

	/**
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function mock_entry_object() {
		return [
			'id'      => 7,
			'form_id' => 2,

			'1'       => 'Stored Value 1',
			'2'       => 'Stored Value 2',
			'3'       => 'Stored Value 3',
			'4'       => 'Stored Value 4',
			'5'       => 'Stored Value 5',
			'6.3'     => 'Stored First 6',
			'6.6'     => 'Stored Last 6',
			'7.3'     => 'Stored First 7',
			'7.6'     => 'Stored Last 7',
		];
	}

	/**
	 * @since 1.1
	 */
	public function test_generate_preview_markup() {
		$field = new Field();
		$this->assertEquals( 'not working', $this->class->generate_preview_markup( 'not working', $field, '', '' ) );

		$field->type = 'pdfpreview';
		$this->assertEquals( 'not working', $this->class->generate_preview_markup( 'not working', $field, '', '' ) );

		$field->gravityflow_is_display_field = true;
		$this->assertEquals( 'working', $this->class->generate_preview_markup( 'not working', $field, '', '' ) );
	}

	/**
	 * @since 1.1
	 */
	public function test_merge_gravityflow_entry_data() {
		$input = [
			'gravityflow_submit' => true,
			'gform_field_values' => 'id=7',
			'step_id'            => 5,
		];

		/* Do a fallback test (when the form cannot be found) */
		$entry = [
			'1'   => 'New Value 1',
			'3'   => 'New Value 2',
			'6.3' => 'New First 6',
			'7.3' => 'New First 7',
		];

		$results = [
			1         => 'New Value 1',
			2         => 'Stored Value 2',
			3         => 'New Value 2',
			4         => 'Stored Value 4',
			5         => 'Stored Value 5',
			'6.3'     => 'New First 6',
			'6.6'     => 'Stored Last 6',
			'7.3'     => 'New First 7',
			'7.6'     => 'Stored Last 7',
			'id'      => 7,
			'form_id' => 2,
		];

		$this->assertEquals( $results, $this->class->merge_gravityflow_entry_data( $entry, '', '', $input ) );

		/* Do a skipped test */
		$entry = [
			'1'   => 'New Value 1',
			'2'   => 'New Value 2',
			'3'   => 'New Value 3',
			'4'   => 'New Value 4',
			'5'   => 'New Value 5',
			'6.3' => 'New First 6',
			'6.6' => 'New Last 6',
			'7.3' => 'New First 7',
			'7.6' => 'New Last 7',
		];

		$this->assertEquals( $entry, $this->class->merge_gravityflow_entry_data( $entry, '', '', [] ) );

		/* Do valid test */
		$results = [
			1         => 'Stored Value 1',
			2         => 'New Value 2',
			3         => 'Stored Value 3',
			4         => 'New Value 4',
			5         => 'Stored Value 5',
			'6.3'     => 'New First 6',
			'6.6'     => 'New Last 6',
			'7.3'     => 'Stored First 7',
			'7.6'     => 'Stored Last 7',
			'id'      => 7,
			'form_id' => 2,
		];

		$this->assertEquals( $results, $this->class->merge_gravityflow_entry_data( $entry, '', '', $input ) );
	}

	/**
	 * @since 1.1
	 */
	public function test_set_form_id() {
		$this->assertSame( 5, $this->class->set_form_id( 5, [] ) );
		$this->assertSame( 8, $this->class->set_form_id( 5, [ 'gravityflow_submit' => 8 ] ) );
	}

	/**
	 * @since 1.1
	 */
	public function test_override_previewer_field_display() {
		$field = new Field();
		$this->assertFalse( $this->class->override_previewer_field_display( false, $field ) );

		$field->type = 'pdfpreview';
		$this->assertTrue( $this->class->override_previewer_field_display( false, $field ) );
	}

	/**
	 * @since 1.1
	 */
	public function test_remove_previewer_form_display_fields() {
		$form = $this->mock_form_object();
		$feed = [ 'meta' => [ 'step_type' => 'approval' ] ];

		$choices = [];
		foreach ( $form['fields'] as $field ) {
			$choices[] = [
				'label' => $field->type,
				'value' => $field->id,
			];
		}

		$num_of_choices = count( $choices );

		/* Test previewer fields are removed */
		$this->assertSame( $num_of_choices - 2, count( $this->class->remove_previewer_form_display_fields( $choices, $form, false ) ) );
		$this->assertSame( $num_of_choices - 2, count( $this->class->remove_previewer_form_display_fields( $choices, $form, $feed ) ) );

		/* Test previewer fields are not removed */
		$feed['meta']['step_type'] = 'user_input';
		$this->assertSame( $num_of_choices, count( $this->class->remove_previewer_form_display_fields( $choices, $form, $feed ) ) );

		$_POST['_gaddon_setting_step_type'] = 'user_input';
		$this->assertSame( $num_of_choices, count( $this->class->remove_previewer_form_display_fields( $choices, $form, false ) ) );
	}
}

/**
 * Class Field
 *
 * @package GFPDF\Tests\Previewer
 *
 * @since   1.1
 */
class Field extends \stdClass {
	public $type;

	public function get_entry_inputs() {
		return '';
	}

	public function get_field_input( $form, $value, $entry ) {
		return 'working';
	}
}

/**
 * Class Field_Name
 *
 * @package GFPDF\Tests\Previewer
 *
 * @since   1.1
 */
class Field_Name extends \stdClass {
	public $type;

	public function get_entry_inputs() {
		return [
			[ 'id' => $this->id . '.3' ],
			[ 'id' => $this->id . '.6' ],
		];
	}

	public function get_field_input( $form, $value, $entry ) {
		return 'working';
	}
}

namespace GFPDF\Plugins\Previewer\ThirdParty;

/**
 * @return Gravity_Flow
 *
 * @since 1.1
 */
function gravity_flow() {
	return new Gravity_Flow();
}

/**
 * Class Mock_GravityFlow
 *
 * @package GFPDF\Tests\Previewer
 *
 * @since   1.1
 */
class Gravity_Flow {
	function get_step() {
		return new Steps();
	}
}

/**
 * Class Steps
 *
 * @package GFPDF\Tests\Previewer
 *
 * @since   1.1
 */
class Steps {
	function get_editable_fields() {
		return [ 2, 4, 5, 6 ];
	}
}
