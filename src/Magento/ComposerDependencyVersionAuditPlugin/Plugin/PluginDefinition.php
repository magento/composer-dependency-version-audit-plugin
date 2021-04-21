<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ComposerDependencyVersionAuditPlugin\Plugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

/**
 * Composer's entry point for the plugin, defines the command provider and Web Setup Wizard Installer's event triggers
 */
class PluginDefinition implements PluginInterface, Capable, EventSubscriberInterface
{
    const PACKAGE_NAME = 'magento/composer-dependency-version-audit-plugin';

    /**
     * @inheritdoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // TODO
    }

    /**
     * @inheritdoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO
    }

    /**
     * @inheritdoc
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO
    }

    /**
     * @inheritdoc
     */
    public function getCapabilities()
    {
        return [CommandProviderCapability::class => CommandProvider::class];
    }

    /**
     * When a package is installed or updated, check if the WebSetupWizard installation needs to be updated
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [Installer\PackageEvents::PRE_PACKAGE_INSTALL => 'packageUpdate',
            Installer\PackageEvents::PRE_PACKAGE_UPDATE => 'packageUpdate'];
    }

    /**
     * Forward package update events to WebSetupWizardPluginInstaller to update the plugin on install or version change
     *
     * @param PackageEvent $event
     * @return void
     */
    public function packageUpdate(PackageEvent $event)
    {
        // TODO

    }
}
