<?php
/*
 * Copyright (c) 2012, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace xrstf\Composer52;

use Composer\Repository\CompositeRepository;
use Composer\Script\Event;

class Generator {
	public static function onPostInstallCmd(Event $event) {
		$composer            = $event->getComposer();
		$installationManager = $composer->getInstallationManager();
		$localRepos          = new CompositeRepository($composer->getRepositoryManager()->getLocalRepositories());
		$package             = $composer->getPackage();
		$config              = $composer->getConfig();

		$generator = new AutoloadGenerator();
		$generator->dump($config, $localRepos, $package, $installationManager, 'composer', false);
	}
}
