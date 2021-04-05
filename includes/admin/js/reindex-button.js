(function($) {

	$(
		function() {
			var $buttons = $( '.typesense-reindex-button' );
			$buttons.on( 'click', handleButtonClick );
		}
	);

	function handleButtonClick(e) {
        $clickedButton = $( e.currentTarget );
		disableButton( $clickedButton );

		pushSettings( $clickedButton);
		//alert('wefwerfwfer');
	}

	function disableButton($button) {
		$button.prop( 'disabled', true );
	}

	function enableButton($button) {
		$button.prop( 'disabled', false );
	}

	function pushSettings($clickedButton) {

		var data = {
			'action': 'typesense_re_index',
            'whatever': 123
		};

		$.post(
			ajaxurl, data, function(response) {
<<<<<<< HEAD
				alert(response);
                enableButton( $clickedButton );
=======
				if (typeof response.totalPagesCount === 'undefined') {
					alert( 'An error occurred' );
					resetButton( $clickedButton );
					return;
				}

				if (response.totalPagesCount === 0) {
					$clickedButton.parents( '.error' ).fadeOut();
					resetButton( $clickedButton );
					return;
				}
				progress = Math.round( (currentPage / response.totalPagesCount) * 100 );
				updateIndexingPourcentage( $clickedButton, progress );

				if (response.finished !== true) {
					reIndex( $clickedButton, index, ++currentPage );
				} else {
					$clickedButton.parents( '.error' ).fadeOut();
					resetButton( $clickedButton );
				}
>>>>>>> 166b5f2155c2ca82bc753bc9391d1a3f95402c15
			}
		).fail(
			function(response) {
				alert( 'An error occurred: ' + response.responseText );
<<<<<<< HEAD
				enableButton( $clickedButton );
=======
				resetButton( $clickedButton );
>>>>>>> 166b5f2155c2ca82bc753bc9391d1a3f95402c15
			}
		);
	}

<<<<<<< HEAD
=======
	function resetButton($clickedButton) {
		ongoing--;
		$clickedButton.text( $clickedButton.data( 'originalText' ) );
		$clickedButton.removeAttr( 'disabled' );
		$clickedButton.data( 'currentPage', 1 );
	}

>>>>>>> 166b5f2155c2ca82bc753bc9391d1a3f95402c15
})( jQuery );
