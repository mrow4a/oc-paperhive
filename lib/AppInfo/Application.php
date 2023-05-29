<?php
/**
 * @author Piotr Mrowczynski <piotr@owncloud.com>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_PaperHive\AppInfo;

use OC\Files\View;
use OCA\Files_PaperHive\PaperHiveMetadata;
use OCP\AppFramework\App;
use OCA\Files_PaperHive\Controller\PaperHiveController;
use OCP\AppFramework\IAppContainer;

class Application extends App {
	/**
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct('files_paperhive', $urlParams);
		$this->registerServices();
		$this->registerHooks();
	}

	private function registerServices() {
		$container = $this->getContainer();
		$server = $container->getServer();

		$container->registerService('PaperHiveController', function (IAppContainer $c) use ($server) {
			$user = $server->getUserSession()->getUser();
			if ($user) {
				$uid = $user->getUID();
			} else {
				throw new \BadMethodCallException('no user logged in');
			}
			return new PaperHiveController(
				$c->getAppName(),
				$server->getRequest(),
				$server->getL10N($c->getAppName()),
				new View('/' . $uid . '/files'),
				$server->getLogger(),
				\OC::$server->getHTTPClientService()->newClient(),
				new PaperHiveMetadata(
					\OC::$server->getDatabaseConnection(),
					\OC::$server->getLogger()
				)
			);
		});
	}

	private function registerHooks() {
		\OCP\Util::connectHook('OC_Filesystem', 'delete', 'OCA\Files_PaperHive\Hooks', 'delete_metadata_hook');
	}
}
