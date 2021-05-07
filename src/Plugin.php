<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\ComposerRepository;
use Composer\Repository\RepositoryInterface;
use Exception;

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
        // Declaration must exist
    }

    /**
     * @inheritdoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Declaration must exist
    }

    /**
     * @inheritdoc
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Declaration must exist
    }

    /**
     * Event subscriber
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [Installer\PackageEvents::PRE_PACKAGE_INSTALL => 'packageUpdate',
            Installer\PackageEvents::PRE_PACKAGE_UPDATE => 'packageUpdate'
        ];
    }

    /**
     * Event listener for Package Install or Update
     *
     * @param PackageEvent $event
     * @return void
     * @throws Exception
     */
    public function packageUpdate(PackageEvent $event): void
    {
        /** @var  OperationInterface */
        $operation = $event->getOperation();
        $composer = $event->getComposer();

        /** @var PackageInterface $package  */
        $package = method_exists($operation, 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        $packageName = $package->getName();
        $privateRepoVersion = '';
        $publicRepoVersion = '';

        foreach ($composer->getRepositoryManager()->getRepositories() as $repository) {
            /** @var RepositoryInterface $repository  */
            if ($repository instanceof ComposerRepository) {
                $found = $repository->findPackage($packageName, '*');
                $repoUrl = $repository->getRepoConfig()['url'];

                if ($found) {
                    switch ($repoUrl) {
                        case self::URL_REPO_MAGENTO:
                            $privateRepoVersion = $found->getFullPrettyVersion();
                            break;
                        case self::URL_REPO_PACKAGIST:
                            $publicRepoVersion = $found->getFullPrettyVersion();
                            break;
                    }
                }
            }
        }

        if ($privateRepoVersion && $publicRepoVersion && (version_compare($publicRepoVersion, $privateRepoVersion, '>'))) {

            throw new Exception(
                'A higher version for this package was found in packagist.org, which might need further investigation.'
            );
        }
    }
}
