<?php
/**
 * RouterFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:demo!
 * @subpackage     Router
 * @since          1.0.0
 *
 * @date           20.03.17
 */

declare(strict_types = 1);

namespace App\Router;

use Nette\Application\IRouter;
use Nette\StaticClass;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	use StaticClass;

	/**
	 * @return IRouter
	 */
	public static function createRouter() : IRouter
	{
		$router = new RouteList;

		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}

}
