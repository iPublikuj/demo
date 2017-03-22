<?php
/**
 * WebsocketsPresenter.php
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

namespace App\Controllers;

use IPub\WebSockets\Application\Controller\Controller;
use IPub\WebSocketsWAMP\Entities\Clients\IClient;
use IPub\WebSocketsWAMP\Entities\Topics\ITopic;
use Nette\Utils\Json;

class DemoChatController extends Controller
{
	/**
	 * @param IClient $client
	 * @param ITopic $topic
	 *
	 * @return void
	 */
	public function actionSubscribe(IClient $client, ITopic $topic)
	{
		// Get nickname stored in cookies
		$nickname = urldecode($client->getRequest()->getCookie('nickname'));

		// Store nick name to client entity
		$client->addParameter('nickname', $nickname);

		$now = new \DateTime();

		$message = new \stdClass();
		$message->type = 'system';
		$message->time = $now->format(DATE_ISO8601);
		$message->content = sprintf('The user %s has joined', $nickname);

		// Send message to all connected users
		$topic->broadcast(Json::encode($message), [$client->getId()]);

		$message = new \stdClass();
		$message->type = 'message';
		$message->time = $now->format(DATE_ISO8601);
		$message->from = 'Lonely Bot';
		$message->content = sprintf('Hi %s! This is demo chatroom powered by IPub/Websockets', $nickname);
		$message->isMe = FALSE;

		// Send welcome message to current user
		$client->event($topic, $message);

		$members = [];

		$member = new \stdClass();
		$member->name = 'Lonely Bot';
		$member->type = 'robot';

		$members[] = $member;

		/** @var IClient $joined */
		foreach($topic as $joined) {
			$member = new \stdClass();
			$member->name = $joined->getParameter('nickname');
			$member->type = 'human';

			$members[] = $member;
		}

		$message = new \stdClass();
		$message->type = 'members';
		$message->time = $now->format(DATE_ISO8601);
		$message->content = $members;

		// Send list of room members to all connected users
		$topic->broadcast(Json::encode($message));
	}

	/**
	 * @param IClient $client
	 * @param ITopic $topic
	 *
	 * @return void
	 */
	public function actionUnsubscribe(IClient $client, ITopic $topic)
	{
		$nickname = $client->getParameter('nickname');

		$now = new \DateTime();

		$message = new \stdClass();
		$message->type = 'system';
		$message->time = $now->format(DATE_ISO8601);
		$message->content = sprintf('The user %s has left', $nickname);

		// Send message to all connected users
		$topic->broadcast(Json::encode($message));

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

	/**
	 * @param \stdClass $event
	 * @param IClient $client
	 * @param ITopic $topic
	 *
	 * @return void
	 */
	public function actionPublish(\stdClass $event, IClient $client, ITopic $topic)
	{
		$now = new \DateTime();

		$message = new \stdClass();
		$message->type = 'message';
		$message->time = $now->format(DATE_ISO8601);
		$message->from = $client->getParameter('nickname');
		$message->content = $event->message;

		/** @var IClient $topicMember */
		foreach ($topic as $topicMember) {
			if ($topicMember->getId() === $client->getId()) {
				$message->isMe = TRUE;

			} else {
				$message->isMe = FALSE;
			}

			// Send message to all connected users
			$topicMember->event($topic, Json::encode($message));
		}
	}

	/**
	 * @param array $data
	 * @param ITopic $topic
	 *
	 * @return void
	 */
	public function actionPush(array $data, ITopic $topic)
	{
		$now = new \DateTime();

		$message = new \stdClass();
		$message->type = $data['type'];
		$message->time = $now->format(DATE_ISO8601);
		$message->content = $data['content'];

		$topic->broadcast(Json::encode($message));
	}
}
