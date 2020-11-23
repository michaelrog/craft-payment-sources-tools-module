<?php
namespace michaelrog\paymentsourcestools\web\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use michaelrog\paymentsourcestools\PaymentSourcesTools;
use michaelrog\paymentsourcestools\web\assets\PaymentSourcesTabAsset;
use yii\base\Component;
use yii\base\Event;

class CpCustomizations extends Component
{

	/**
	 *
	 */
	public function init()
	{

		// Register CP templates root

		Event::on(
			View::class,
			View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
			function(RegisterTemplateRootsEvent $e) {
				$e->roots['payment-sources-tools'] = Craft::getAlias('@michaelrog/paymentsourcestools/web/templates');
			}
		);

	}

	/**
	 * Optionally adds a Recurring Orders tab on the Users edit screen.
	 *
	 * @param array $context
	 */
	public function cpUsersEditHook(array &$context)
	{

		$currentUser = Craft::$app->getUser()->getIdentity();

		// TODO: Add/check custom permissions

		if ($context['isNewUser'] || !$currentUser->can('commerce-manageOrders'))
		{
			return;
		}

		Craft::$app->getView()->registerAssetBundle(PaymentSourcesTabAsset::class);

		$context['tabs']['paymentSourceTools'] = [
			'label' => PaymentSourcesTools::t('Payment Sources'),
			'url' => '#paymentSourcesTools'
		];

	}

	/**
	 * Fills in the content for the Recurring Orders tab on the Users edit screen.
	 *
	 * @param array $context
	 *
	 * @return string
	 *
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 * @throws \yii\base\Exception
	 */
	public function cpUsersEditContentHook(array &$context)
	{

		$currentUser = Craft::$app->getUser()->getIdentity();

		if (empty($context['user']) || $context['isNewUser'] || !$currentUser->can('commerce-manageOrders'))
		{
			return;
		}

		return Craft::$app->getView()->renderTemplate('payment-sources-tools/cp/_hooks/cp.users.edit.content', [
			'user' => $context['user'],
		]);

	}

}
