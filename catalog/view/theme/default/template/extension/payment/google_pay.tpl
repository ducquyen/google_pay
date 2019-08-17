<div id="form_google_pay" class="form-horizontal">
	<div class="buttons">
		<iframe id="google_pay_button_container" class="pull-right" src="https://googlepay.opencart.com" width="300" height="60" frameBorder="0"></iframe>
	</div>
</div>
<script type="text/javascript">

var iframe_data = {
	api_version_major: <?php echo $api_version_major; ?>,
	api_version_minor: <?php echo $api_version_minor; ?>,
	ship_require_phone: <?php echo $ship_require_phone; ?>,
	tokenization_specification: <?php echo json_encode($tokenization_specification); ?>,
	allowed_card_networks: <?php echo json_encode($allowed_card_networks); ?>,
	allowed_card_auth_methods: <?php echo json_encode($allowed_card_auth_methods); ?>,
	accept_prepay_cards: <?php echo $accept_prepay_cards; ?>,
	bill_require_phone: <?php echo $bill_require_phone; ?>,
	merchant_id: '<?php echo $merchant_id; ?>',
	merchant_name: '<?php echo $merchant_name; ?>',
	environment: '<?php echo $environment; ?>',
	button_color: '<?php echo $button_color; ?>',
    button_type: '<?php echo $button_type; ?>',
	currency_code: '<?php echo $currency_code; ?>',
	total_price: '<?php echo $total_price; ?>'
}

// addEventListener support for IE8	
function bindEvent(element, eventName, eventHandler) {
    if (element.addEventListener) {
		element.addEventListener(eventName, eventHandler, false);
    } else if (element.attachEvent) {
        element.attachEvent('on' + eventName, eventHandler);
    }
}

var google_pay_button_container = document.getElementById('google_pay_button_container');

// Send a message to the child iframe
function sendData(data) {
    // Make sure you are sending a string, and to stringify JSON
	google_pay_button_container.contentWindow.postMessage(data, '*');
}

if (typeof window.bind_event_status === 'undefined') {
	window.bind_event_status = true;
               
	// Listen to message from child window
	bindEvent(window, 'message', function(event) {	
		if (event.data['code'] == 'ready') {
			sendData({code: 'setup', data: iframe_data}); 
		}
	
		if (event.data['code'] == 'send') {
			$.ajax({
				method: 'post',
				url: 'index.php?route=extension/payment/google_pay/send',
				data: {'data': JSON.stringify(event.data['data'])},
				dataType: 'json',
				success: function(data) {
					showAlert(data);
														
					if (data['success']) {
						location = data['success'];
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					showConsoleDebug({log: thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText});
				}
			});
		}
	
		if (event.data['code'] == 'alert') {
			showAlert(event.data['data']);
		}
	
		if (event.data['code'] == 'console') {
			showConsoleDebug(event.data['data']);
		}
	});
}

/**
* Show Alert
*/
function showAlert(data) {
	$('#form_google_pay .alert').remove();
			
	if (data['error']) {
		if (data['error']['warning']) {
			$('#form_google_pay').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><button type="button" class="close" data-dismiss="alert">&times;</button> ' + data['error']['warning'] + '</div>');
		}
	}
}

/**
* Show Console Debug
*/
function showConsoleDebug(data) {
	<?php if ($debug == 1) { ?>
	if (data['log']) {
		console.log(data['log']);
	}
	
	if (data['warn']) {
		console.warn(data['warn']);
	}
	
	if (data['error']) {
		console.error(data['error']);
	}
    <?php } ?>
}

</script>