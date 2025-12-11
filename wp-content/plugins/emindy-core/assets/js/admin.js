/* global jQuery, window */
/*!
 * eMINDy Admin JSON Helper
 *
 * Validates JSON meta fields (e.g., chapters and steps) in the post editor and
 * provides accessible feedback for content editors.
 */
(function ( $ ) {
	'use strict';

	var SUCCESS_COLOR = '#1e7e34';
	var ERROR_COLOR   = '#c00';
	var defaults      = window.emindyAdmin || {};

	/**
	 * Get a localized label from the global config, with a safe fallback.
	 *
	 * @param {string} key      The label key.
	 * @param {string} fallback Fallback text if not found.
	 *
	 * @return {string} The resolved label.
	 */
	function getLabel( key, fallback ) {
		if ( defaults && defaults[ key ] ) {
			return defaults[ key ];
		}

		return fallback || '';
	}

	/**
	 * Update the status element with message, color, and accessibility state.
	 *
	 * @param {jQuery} $status The status element.
	 * @param {string} message The message to display.
	 * @param {string} color   The text color to apply.
	 */
	function setStatusState( $status, message, color ) {
		if ( ! $status || ! $status.length ) {
			return;
		}

		var hasMessage = !! message;
		var isError    = hasMessage && color === ERROR_COLOR;
		var isValid    = hasMessage && color === SUCCESS_COLOR;

		$status
			.text( message )
			.css( 'color', color || '' )
			.attr( 'role', 'status' )
			.attr( 'aria-live', hasMessage ? 'polite' : 'off' )
			.attr( 'aria-hidden', hasMessage ? 'false' : 'true' )
			.attr( 'data-emindy-json-state', isError ? 'invalid' : ( isValid ? 'valid' : '' ) )
			.toggleClass( 'emindy-json-status--valid', isValid )
			.toggleClass( 'emindy-json-status--invalid', isError );
	}

	/**
	 * Validate a JSON textarea field and update the status label.
	 *
	 * @param {jQuery} $field  The textarea to validate.
	 * @param {jQuery} $status The status element to update.
	 */
	function validateField( $field, $status ) {
		if ( ! $field.length || ! $status.length ) {
			return;
		}

		var value = ( $field.val() || '' ).trim();

		if ( ! value ) {
			setStatusState( $status, '', '' );
			return;
		}

		try {
			JSON.parse( value );
			setStatusState(
				$status,
				getLabel( 'valid', 'Valid JSON' ),
				SUCCESS_COLOR
			);
		} catch ( error ) {
			setStatusState(
				$status,
				getLabel( 'invalid', 'Invalid JSON' ),
				ERROR_COLOR
			);
		}
	}

	/**
	 * Attach validation to a field/status pair.
	 *
	 * @param {string|Element|jQuery} fieldSelector  Selector or element for the textarea field.
	 * @param {string|Element|jQuery} statusSelector Selector or element for the status element.
	 */
	function attachValidation( fieldSelector, statusSelector ) {
		var $fields = $( fieldSelector );
		var $status = $( statusSelector );

		if ( ! $fields.length || ! $status.length ) {
			return;
		}

		$fields.each( function ( index, field ) {
			var $field = $( field );
			var timer  = null;

			function runValidation() {
				timer = null;
				validateField( $field, $status );
			}

			// Debounced live validation as the user types.
			$field.on( 'input emindy:validate', function () {
				if ( timer ) {
					window.clearTimeout( timer );
				}

				timer = window.setTimeout( runValidation, 150 );
			} );

			// Ensure validation runs on blur as well.
			$field.on( 'blur', runValidation );

			// Initial validation on page load.
			runValidation();
		} );
	}

	/**
	 * Auto-attach validation for any status element that declares a
	 * data-emindy-json-status-for attribute pointing to its field(s).
	 *
	 * Example:
	 * <textarea id="em_custom_json"></textarea>
	 * <span id="em_custom_json_status"
	 *       data-emindy-json-status-for="#em_custom_json"></span>
	 */
	function autoAttachFromDataAttributes() {
		$( '[data-emindy-json-status-for]' ).each( function () {
			var $status       = $( this );
			var fieldSelector = $status.attr( 'data-emindy-json-status-for' );

			if ( ! fieldSelector ) {
				return;
			}

			attachValidation( fieldSelector, $status );
		} );
	}

	$( function () {
		// Explicit known JSON meta fields.
		attachValidation( '#em_chapters_json', '#em_chapters_json_status' );
		attachValidation( '#em_steps_json', '#em_steps_json_status' );

		// Generic hook for any future JSON fields declared via data attributes.
		autoAttachFromDataAttributes();
	} );
})( jQuery );
