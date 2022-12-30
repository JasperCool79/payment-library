<!DOCTYPE html>
<html lang="en-Us">
<head>
	<title>Master/ Payment</title>
	<script src="{{ $hostedcheckoutUrl }}" 
	data-error="{{ $errorUrl }}" 
	data-cancel="{{ $cancelUrl }}" 
	data-complete="{{ $completeUrl }}" 
	data-timeout="{{ $timeoutUrl }}" >
</script>

</head>
<body>
	<script type="text/javascript">

	      Checkout.configure({
	      	merchant: '{{ $merchantId }}',
	      	order: {
				customerNote: "{{ $customerNote??''}}",
				customerOrderDate: "{{ date('Y-m-d') }}",
				description: "{{ $description??'' }}",
	        },
	        session: {
				id: '{{ $sessionId }}'
			},              

			interaction: {
				operation: "PURCHASE",
				merchant : {
					name : '{{ config("app.name") }}',
					/*address: {
					  line1: '200 Sample St',
					  line2: '1234 Example Town'            
					},						
					phone  : '',*/
					logo : ''
				},
				locale        : 'en_US',
				theme         : 'default',
				displayControl: {
					billingAddress : 'HIDE',
					customerEmail  : 'HIDE',
					orderSummary   : 'HIDE',
					shipping       : 'HIDE',
					paymentConfirmation : "SHOW"
				},
			},
			
			customer:{ 
				firstName: "{{ $customerName??'' }}" 
			}

		});
	     
	    Checkout.showPaymentPage();

	  </script>

</body>
</html>