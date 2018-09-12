window.buddyverified = {};
	( function( window, $, that ) {

		// Constructor.
		that.init = function() {
			that.cache();
			that.bindEvents();
		}

		// Cache all the things.
		that.cache = function() {
			that.$c = {
				window: $( window ),
				body: $( 'body' ),
			};
		}

		// Combine all events.
		that.bindEvents = function() {

        }

		// Engage!
		//$( that.init );

	})( window, jQuery, window.buddyverified );
