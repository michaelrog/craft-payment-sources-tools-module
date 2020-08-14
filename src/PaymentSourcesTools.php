<?php
namespace michaelrog\paymentsourcestools;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\console\Application as ConsoleApplication;
use craft\helpers\FileHelper;
use craft\web\Application as WebApplication;
use michaelrog\paymentsourcestools\controllers\WebController;
use michaelrog\paymentsourcestools\web\cp\CpCustomizations;
use yii\base\Module;

/**
 * @property CpCustomizations $cpCustomizations
 */
class PaymentSourcesTools extends Module
{

	/**
	 * @param Module $parent
	 * @param string $handle
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function registerModule($handle = 'payment-sources-tools')
	{
		$module = Craft::createObject(static::class, [$handle, Craft::$app]);
		/** @var static $module */
		static::setInstance($module);
		Craft::$app->setModule($handle, $module);
	}

	/**
	 * @param $id
	 * @param null $parent
	 * @param array $config
	 */
	public function __construct($id, $parent = null, $config = [])
	{

		$config['components'] = [
			'cpCustomizations' => CpCustomizations::class,
		];

		parent::__construct($id, $parent, $config);

	}

	/**
	 *
	 */
	public function init()
	{

		Craft::setAlias('@paymentSourcesToolsTemplates', __DIR__ . DIRECTORY_SEPARATOR . 'web/templates');
		parent::init();

		/*
		 * Register controllers
		 */

		if (Craft::$app instanceof WebApplication) {
			Craft::$app->controllerMap['payment-sources-tools'] = WebController::class;
		}

		/*
		 * Register template hooks
		 */

		Craft::$app->getView()->hook('cp.users.edit', [$this->cpCustomizations, 'cpUsersEditHook']);
		Craft::$app->getView()->hook('cp.users.edit.content', [$this->cpCustomizations, 'cpUsersEditContentHook']);

	}

	/*
	 *
	 */

	/**
	 * @param $msg
	 * @param string $level
	 * @param string $file
	 */
	public static function log($msg, $level = 'notice', $file = 'PaymentSourcesTools')
	{
		try
		{
			$file = Craft::getAlias('@storage/logs/' . $file . '.log');
			$log = "\n" . date('Y-m-d H:i:s') . " [{$level}]" . "\n" . print_r($msg, true);
			FileHelper::writeToFile($file, $log, ['append' => true]);
		}
		catch(\Exception $e)
		{
			Craft::error($e->getMessage(), $file);
		}
	}

	/**
	 * @param $msg
	 * @param string $level
	 * @param string $file
	 */
	public static function error($msg, $level = 'error', $file = 'PaymentSourcesTools')
	{
		static::log($msg, $level, $file);
	}

	/**
	 * @param $message
	 * @param array $params
	 * @param null $language
	 *
	 * @return string
	 */
	public static function t($message, $params = [], $language = null)
	{
		return Commerce::t($message, $params, $language);
	}

}
