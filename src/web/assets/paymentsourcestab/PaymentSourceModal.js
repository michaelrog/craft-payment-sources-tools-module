if (typeof Craft.PaymentSourcesTools === typeof undefined) {
	Craft.PaymentSourcesTools = {};
}

/**
 * Class Craft.PaymentSourcesTools.PaymentSourceModal
 */
Craft.PaymentSourcesTools.PaymentSourceModal = Garnish.Modal.extend(
	{

		$container: null,
		$body: null,

		init: function (settings) {

			this.$container = $('<div id="paymentsourcemodal" class="modal fitted loading"/>').appendTo(Garnish.$bod);

			this.base(this.$container, $.extend({
				resizable: false
			}, settings));

			var data = {
				userId: settings.userId,
			};

			Craft.postActionRequest('payment-sources-tools/get-payment-source-modal-html', data, $.proxy(function (response, textStatus) {

				this.$container.removeClass('loading');

				if (textStatus === 'success') {

					if (response.success) {

						var $this = this;
						this.$container.append(response.modalHtml);
						Craft.appendHeadHtml(response.headHtml);
						Craft.appendFootHtml(response.footHtml);

						var $buttons = $('.buttons', this.$container),
							$cancelBtn = $('<div class="btn">' + Craft.t('app', 'Cancel') + '</div>').prependTo($buttons);

						this.addListener($cancelBtn, 'click', 'cancelPayment');

						$('select#payment-form-select').change($.proxy(function (ev) {
							var id = $(ev.currentTarget).val();
							$('.gateway-form').addClass('hidden');
							$('#gateway-' + id + '-form').removeClass('hidden');
							Craft.initUiElements(this.$container);
							setTimeout(function () {
								$this.updateSizeAndPosition();
							}, 200);
						}, this)).trigger('change');

						Craft.initUiElements(this.$container);

						setTimeout(function () {
							$this.updateSizeAndPosition();
						}, 200);

					} else {

						var error = Craft.t('commerce', 'An unknown error occurred.');

						if (response.error) {
							error = response.error;
						}

						this.$container.append('<div class="body">' + error + '</div>');

					}
				}
			}, this));

		},

		cancelPayment: function () {
			this.hide();
		}

	},
	{}
);
