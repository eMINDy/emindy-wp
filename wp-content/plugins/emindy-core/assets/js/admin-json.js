(function ( $ ) {
  'use strict';

  var successColor = '#1e7e34';
  var errorColor = '#c00';

  /**
   * Validate a JSON textarea field and update the status label.
   *
   * @param {jQuery} $field  The textarea to validate.
   * @param {jQuery} $status The status element to update.
   */
  function validateField( $field, $status ) {
    if ( !$field.length || !$status.length ) {
      return;
    }

    var value = ( $field.val() || '' ).trim();

    if ( ! value ) {
      updateStatus( $status, '', '' );
      return;
    }

    try {
      JSON.parse( value );
      updateStatus( $status, emindyAdmin.valid, successColor );
    } catch ( error ) {
      updateStatus( $status, emindyAdmin.invalid, errorColor );
    }
  }

  /**
   * Update the status element with the provided message and color.
   *
   * @param {jQuery} $status The status element.
   * @param {string} message The message to display.
   * @param {string} color   The text color to apply.
   */
  function updateStatus( $status, message, color ) {
    if ( !$status.length ) {
      return;
    }

    $status.text( message ).css( { color: color } );
  }

  /**
   * Attach validation to a field/status pair.
   *
   * @param {string} fieldSelector  Selector for the textarea field.
   * @param {string} statusSelector Selector for the status element.
   */
  function attachValidation( fieldSelector, statusSelector ) {
    var $field = $( fieldSelector );
    var $status = $( statusSelector );

    if ( !$field.length || !$status.length ) {
      return;
    }

    $field.on( 'input', function () {
      validateField( $field, $status );
    } );

    validateField( $field, $status );
  }

  $( function () {
    attachValidation( '#em_chapters_json', '#em_chapters_json_status' );
    attachValidation( '#em_steps_json', '#em_steps_json_status' );
  } );
})( jQuery );
