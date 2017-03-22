<?php
/**
 * WebSocketsClientDisconnectSubscriber.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:demo!
 * @subpackage     Controllers
 * @since          1.0.0
 *
 * @date           20.03.17
 */

declare(strict_types = 1);

namespace App\Events;

use IPub\WebSockets\Application\IApplication;
use IPub\WebSockets\Http\IRequest;
use IPub\WebSocketsWAMP\Entities\Clients\IClient;
use IPub\WebSocketsWAMP\Entities\Topics\ITopic;
use IPub\WebSocketsWAMP\Topics\IStorage;
use Nette\SmartObject;
use Nette\Utils\Json;

class WebSocketsClientDisconnectHandler
{
	use SmartObject;

	/**
	 * @var IStorage
	 */
	private $topicsStorage;

	/**
	 * @param IStorage $topicsStorage
	 */
	public function __construct(IStorage $topicsStorage)
	{
		$this->topicsStorage = $topicsStorage;
	}

	public function __invoke(IClient $client, IRequest $httpRequest)
	{
		$now = new \DateTime();

		$subscribedTopics = $client->getParameter('subscribedTopics', new \SplObjectStorage());

		/** @var ITopic $subscribedTopic */
		foreach ($subscribedTopics as $subscribedTopic) {
			$message = new \stdClass();
			$message->type = 'system';
			$message->time = $now->format(DATE_ISO8601);
			$message->content = sprintf('The user %s has left', $client->getParameter('nickname'));

			// Send message to all connected users
			$subscribedTopic->broadcast(Json::encode($message));
		}

		/** @var ITopic $topic */
		foreach ($this->topicsStorage as $topic) {
			$members = [];

			$member = new \stdClass();
			$member->name = 'Lonely Bot';
			$member->type = 'robot';

			$members[] = $member;

			/** @var IClient $joined */
			foreach($topic as $joined) {
				if ($joined->getId() !== $client->getId()) {
					$member = new \stdClass();
					$member->name = $joined->getParameter('nickname');
					$member->type = 'human';

					$members[] = $member;
				}
			}

			$message = new \stdClass();
			$message->type = 'members';
			$message->time = $now->format(DATE_ISO8601);
			$message->content = $members;

			// Send list of room members to all connected users
			$topic->broadcast(Json::encode($message));
		}
	}
}
