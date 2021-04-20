(function($) {

	$(
		function() {
			var $buttons = $( '.typesense-reindex-button' );
			$buttons.on( 'click', handleButtonClick );
		}
	);

	function handleButtonClick(e) {
        $clickedButton = $( e.currentTarget );
		var index      = $clickedButton.data( 'index_id' );
		disableButton( $clickedButton );

		pushSettings( $clickedButton,index);
		//alert('wefwerfwfer');
	}

	function disableButton($button) {
		$button.prop( 'disabled', true );
	}

	function enableButton($button) {
		$button.prop( 'disabled', false );
	}

	function pushSettings($clickedButton,index) {

		var data = {
			'action': 'typesense_re_index',
            index: 'qwe'
		};

		$.post(
			ajaxurl, data, function(response) {
				alert(response);
                enableButton( $clickedButton );
			}
		).fail(
			function(response) {
				alert( 'An error occurred: ' + response.responseText );
				enableButton( $clickedButton );
			}
		);
	}

})( jQuery );
