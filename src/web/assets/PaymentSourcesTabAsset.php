<?php
namespace michaelrog\paymentsourcestools\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PaymentSourcesTabAsset extends AssetBundle
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{

		$this->sourcePath = __DIR__ . '/paymentsourcestab';

		$this->depends = [
			CpAsset::class,
		];

		$this->js[] = 'PaymentSourcesTab.js';
		$this->js[] = 'PaymentSourceModal.js';

		parent::init();

	}

}
