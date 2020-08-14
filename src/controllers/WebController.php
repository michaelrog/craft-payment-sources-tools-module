<?php
namespace michaelrog\paymentsourcestools\controllers;

use Craft;
use craft\commerce\base\Gateway;
use craft\commerce\elements\Order;
use craft\commerce\gateways\MissingGateway;
use craft\commerce\Plugin as Commerce;
use craft\web\Controller;
use michaelrog\paymentsourcestools\PaymentSourcesTools;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\Response;

class WebController extends Controller
{

	/**
	 * @return Response
	 *
	 * @throws BadRequestHttpException
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function actionGetPaymentSourceModalHtml(): Response
	{

		$this->requireAcceptsJson();
		$view = $this->getView();

		$request = Craft::$app->getRequest();
		$userId = $request->getRequiredParam('userId');

		$gateways = Commerce::getInstance()->getGateways()->getAllGateways();

		$formHtml = '';

		foreach ($gateways as $key => $gateway) {
			/** @var Gateway $gateway */

			if (!$gateway->cpPaymentsEnabled() || $gateway instanceof MissingGateway) {
				unset($gateways[$key]);
				continue;
			}

			// TODO: Enable adding errors and data back to the current form model, like Commerce does.
			$paymentFormModel = $gateway->getPaymentFormModel();

			$paymentFormHtml = $gateway->getPaymentFormHtml([
				'paymentForm' => $paymentFormModel,
				'order' => new Order(),
			]);

			$paymentFormHtml = $view->renderTemplate('payment-sources-tools/cp/_paymentSourceForm', [
				'formHtml' => $paymentFormHtml,
				'userId' => $userId,
				'gateway' => $gateway,
			]);

			$formHtml .= $paymentFormHtml;

		}

		$modalHtml = $view->renderTemplate('payment-sources-tools/cp/_paymentSourceModal', [
			'gateways' => $gateways,
			'paymentForms' => $formHtml,
		]);

		return $this->asJson([
			'success' => true,
			'modalHtml' => $modalHtml,
			'headHtml' => $view->getHeadHtml(),
			'footHtml' => $view->getBodyHtml(),
		]);

	}

	/**
	 * @return Response|null
	 *
	 * @throws HttpException
	 * @throws InvalidConfigException
	 * @throws BadRequestHttpException
	 */
	public function actionCreatePaymentSource()
	{

		$this->requirePostRequest();
		$request = Craft::$app->getRequest();

		// TODO: Require the manage permission?
		if (Craft::$app->getUser()->isGuest)
		{
			// TODO: Translate
			throw new HttpException(401, PaymentSourcesTools::t('You must be logged in to create a new Payment Source.'));
		}

		$userId = $request->getRequiredBodyParam('userId');
		$gatewayId = $request->getRequiredBodyParam('gatewayId');

		/** @var Gateway $gateway */
		$gateway = Commerce::getInstance()->getGateways()->getGatewayById($gatewayId);

		if (!$gateway || !$gateway->supportsPaymentSources())
		{
			$error = PaymentSourcesTools::t('There is no gateway selected that supports payment sources.');
			return $this->returnErrorResponse($error);
		}

		// Get the payment method' gateway adapter's expected form model
		$paymentForm = $gateway->getPaymentFormModel();
		$paymentForm->setAttributes($request->getBodyParams(), false);
		$description = (string)$request->getBodyParam('description');

		try
		{
			$paymentSource = Commerce::getInstance()->getPaymentSources()->createPaymentSource($userId, $gateway, $paymentForm, $description);
		}
		catch (\Throwable $exception)
		{
			PaymentSourcesTools::error($exception->getMessage());
			Craft::$app->getErrorHandler()->logException($exception);
			$error = PaymentSourcesTools::t('Could not create the Payment Source.') . ' (' . $exception->getMessage() . ')';
			return $this->returnErrorResponse($error, ['paymentForm' => $paymentForm]);
		}

		return $this->returnSuccessResponse($paymentSource, ['paymentSource' => $paymentSource]);

	}

	/**
	 * @param string $errorMessage
	 * @param array $routeParams
	 *
	 * @return null|Response
	 */
	protected function returnErrorResponse($errorMessage, $routeParams = [])
	{

		if (Craft::$app->getRequest()->getAcceptsJson())
		{
			return $this->asErrorJson($errorMessage);
		}

		Craft::$app->getSession()->setError($errorMessage);

		Craft::$app->getUrlManager()->setRouteParams([
				'errorMessage' => $errorMessage,
			] + $routeParams);

		return null;

	}

	/**
	 * @param $returnUrlObject
	 * @param array $jsonParams
	 * @param null $defaultRedirectUrl
	 *
	 * @return Response
	 *
	 * @throws BadRequestHttpException from `redirectToPostedUrl()` if the redirect param was tampered with.
	 */
	protected function returnSuccessResponse($returnUrlObject = null, $jsonParams = [], $defaultRedirectUrl = null)
	{

		if (Craft::$app->request->getAcceptsJson())
		{
			return $this->asJson(['success' => true] + $jsonParams);
		}

		return $this->redirectToPostedUrl($returnUrlObject, $defaultRedirectUrl);

	}

}
