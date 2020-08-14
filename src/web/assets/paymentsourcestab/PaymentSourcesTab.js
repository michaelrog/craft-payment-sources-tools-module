if (typeof Craft.PaymentSourcesTools === typeof undefined) {
	Craft.PaymentSourcesTools = {};
}

Craft.PaymentSourcesTools.PaymentSourcesTab = Garnish.Base.extend(
	{

		userId: null,

		init: function(settings) {

			this.setSettings(settings);
			this.userId = this.settings.userId;

			this.$addPaymentSource = $('#paymentSourcesTools-add-payment-source');

			this.addListener(this.$addPaymentSource, 'click', 'addPaymentSource');

		},

		openPaymentSourceModal: function() {
			if (!this.paymentModal) {
				this.paymentModal = new Craft.PaymentSourcesTools.PaymentSourceModal({
					userId: this.userId,
				});

			} else {
				this.paymentModal.show();
			}
		},

		addPaymentSource: function(e) {
			e.preventDefault();
			this.openPaymentSourceModal();
		},

	},
	{
		defaults: {
			userId: null,
		}
	}
);
