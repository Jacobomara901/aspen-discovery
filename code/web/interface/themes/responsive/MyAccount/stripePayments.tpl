{strip}
	<input type="hidden" name="patronId" value="{$userId}"/>
	<div class="row" style="margin-left: .25em; margin-right: .25em">
			<div class="panel panel-info col-tn-12" style="padding-left: 0; padding-right: 0">
				<div class="panel-heading">
                    {translate text="Pay Online" isPublicFacing=true}
				</div>
				<div class="panel-body">
					<div id="stripe-container" class="row">
						<div id="card-element" class="form-group col-tn-12"></div>
						<div class="form-group col-tn-12">
							<button type="submit" id="process-stripe-payment" class="btn btn-primary">{if !empty($payFinesLinkText)}{$payFinesLinkText}{else}{translate text = 'Submit Payment' isPublicFacing=true}{/if}</button>
						</div>
					</div>
				</div>
				</div>

			<script src="https://js.stripe.com/v3/"></script>
		{literal}
			<script>
				const stripe = Stripe('{/literal}{$stripePublicKey}{literal}');
				let elements = stripe.elements();
				let card = elements.create('card');
				card.mount('#card-element')

				const cardButton = document.getElementById('process-stripe-payment');

				cardButton.addEventListener('click', async function (event) {
					console.log(`Debug: Submitting Payment`);
					event.preventDefault();
					cardButton.disabled = true;
					cardButton.innerHTML = "Submitting Payment...";

					console.log(`Debug: Creating Stripe Order`);
					var paymentId = AspenDiscovery.Account.createStripeOrder('#fines{/literal}{$userId}{literal}', 'fine');

					console.log('Debug: Tokenizing Payment Method');
					stripe.createPaymentMethod({
						type: 'card',
						card: card,
					})
							.then(function(result) {
								// Handle result.error or result.paymentMethod
								if (result.error){
									console.log(result.error.message);
									cardButton.disabled = false;
									cardButton.innerHTML = "{/literal}{if !empty($payFinesLinkText)}{$payFinesLinkText}{else}{translate text = 'Submit Payment' isPublicFacing=true}{/if}{literal}";
								} else {
									console.log("PaymentMethod ID: " + result.paymentMethod.id);
									console.log(`Debug: Completing Stripe Order`);
									AspenDiscovery.Account.completeStripeOrder({/literal}{$userId}{literal}, 'fine', paymentId, result.paymentMethod.id);
								}
							});
				});
			</script>
		{/literal}
	</div>
{/strip}