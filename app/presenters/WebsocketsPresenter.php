<?php
/**
 * WebsocketsPresenter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:demo!
 * @subpackage     Presenters
 * @since          1.0.0
 *
 * @date           20.03.17
 */

declare(strict_types = 1);

namespace App\Presenters;

use IPub\WebSocketsWAMP\PushMessages\IPusher;

class WebsocketsPresenter extends BasePresenter
{
	/**
	 * @var IPusher
	 */
	private $pusher;

	/**
	 * @param IPusher $pusher
	 */
	public function __construct(
		IPusher $pusher
	) {
		parent::__construct();

		$this->pusher = $pusher;
	}

	/**
	 * @return void
	 */
	public function handleAdvert()
	{
		$room = 'general';

		$data = [
			'type'    => 'system',
			'content' => 'This is example advertising.',
		];

		$this->pusher->push($data, 'DemoChat:', [
			'room' => $room,
		]);

		$this->terminate();
	}
}
