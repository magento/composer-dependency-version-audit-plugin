<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\ComposerRepository;

/**
 * Composer's entry point for the plugin
 */
class PluginDefinition implements PluginInterface, EventSubscriberInterface
{

    /**#@+
     * URL For Private Magento Repo
     */
    const URL_REPO_MAGENTO = 'https://repo.magento.com';

    /**#@+
     * URL For Public Packagist Repo
     */
    const URL_REPO_PACKAGIST = 'https://repo.packagist.org';

    /**
     * @inheritdoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // Method must exist
    }

    /**
     * @inheritdoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Method must exist
    }

    /**
     * @inheritdoc
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        // method must exist
    }

    /**
     * Event subscriber
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [Installer\PackageEvents::PRE_PACKAGE_INSTALL => 'packageUpdate',
            Installer\PackageEvents::PRE_PACKAGE_UPDATE => 'packageUpdate'];
    }

    /**
     * Event listener for Package Install or Update
     *
     * @param PackageEvent $event
     * @return void
     */
    public function packageUpdate(PackageEvent $event): void
    {
        $operation = $event->getOperation();
        $composer = $event->getComposer();

        /** @var PackageInterface $package  */

        $package = method_exists($operation, 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        $packageName = $package->getName();
        $foundInInternalRepo = false;
        $foundInPublicRepo =  false;

        foreach ($composer->getRepositoryManager()->getRepositories() as $repository) {
            if ($repository instanceof ComposerRepository) {

                if ($repository->getRepoConfig()['url'] === self::URL_REPO_MAGENTO) {
                    if ($repository->findPackage($packageName, '*')) {
                        $foundInInternalRepo = true;
                    }
                }

                if ($repository->getRepoConfig()['url'] === self::URL_REPO_PACKAGIST) {
                    if ($repository->findPackage($packageName, '*')) {
                        $foundInPublicRepo = true;
                    }
                }
            }
        }

        if($foundInInternalRepo && !$foundInPublicRepo && strpos($packageName, 'magento') === false){
            //to do possibly throw an exception here
        }
    }
}
