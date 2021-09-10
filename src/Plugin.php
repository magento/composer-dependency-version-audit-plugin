<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Request;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Plugin\PluginInterface;
use Composer\Repository\ComposerRepository;
use Composer\Repository\RepositoryInterface;
use Composer\Package\PackageInterface;
use Exception;
use Magento\ComposerDependencyVersionAuditPlugin\Utils\Version;

/**
 * Composer's entry point for the plugin
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{

    /**#@+
     * URL For Public Packagist Repo
     */
    const URL_REPO_PACKAGIST = 'https://repo.packagist.org';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var Version
     */
    private $versionSelector;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var array
     */
    private $nonConstrainedPackages;

    /**#@+
     * Constant for VBE ALLOW LIST
     */
    private const VBE_ALLOW_LIST = [
        'vertexinc',
        'yotpo',
        'klarna',
        'amzn',
        'dotmailer',
        'braintree',
        'paypal',
        'gene'
    ];

    /**
     * Initialize dependencies
     * @param Version|null $version
     */
    public function __construct(Version $version = null, NullIO $io = null)
    {
        if ($version) {
            $this->versionSelector = $version;
        } else {
            $this->versionSelector = new Version();
        }

        if ($io) {
            $this->io = $io;
        } else {
            $this->io = new NullIO();
        }
    }

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
        return [
            Installer\PackageEvents::PRE_PACKAGE_INSTALL => 'packageUpdate',
            Installer\PackageEvents::PRE_PACKAGE_UPDATE => 'packageUpdate',
        ];
    }

    /**
     * Get all package installations that use non-fixed version constraints (IE: 2.4.*, ^2.4, etc.)
     *
     * @param Request $request
     * @return array
     */
    protected function getNonFixedConstraintList(Request $request): array
    {
        if (!$this->nonConstrainedPackages) {
            $constraintList = [];
            foreach ($request->getJobs() as $job) {
                if ($job['cmd'] === 'install' && !$job['fixed'])
                {
                    $constraintList[$job['packageName']] = true;
                }
            }
            $this->nonConstrainedPackages = $constraintList;
        }
        return $this->nonConstrainedPackages;
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
        $this->composer = $event->getComposer();

        /** @var PackageInterface $package  */
        $package = method_exists($operation, 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        $packageName = $package->getName();
        $privateRepoVersion = '';
        $publicRepoVersion = '';
        $privateRepoUrl = '';
        list($namespace, $project) = explode("/", $packageName);
        $isPackageVBE = in_array($namespace, self::VBE_ALLOW_LIST, true);

        if(!$isPackageVBE) {
            foreach ($this->composer->getRepositoryManager()->getRepositories() as $repository) {

                /** @var RepositoryInterface $repository */
                if ($repository instanceof ComposerRepository) {
                    $found = $this->versionSelector->findBestCandidate($this->composer, $packageName, $repository);
                    $repoUrl = $repository->getRepoConfig()['url'];

                    if ($found) {
                        if (strpos($repoUrl, self::URL_REPO_PACKAGIST) !== false) {
                            $publicRepoVersion = $found->getFullPrettyVersion();
                        } else {
                            $currentPrivateRepoVersion = $found->getFullPrettyVersion();
                            //private repo version should hold highest version of package
                            if (empty($privateRepoVersion) || version_compare($currentPrivateRepoVersion, $privateRepoVersion, '>')) {
                                $privateRepoVersion = $currentPrivateRepoVersion;
                                $privateRepoUrl = $repoUrl;
                            }
                        }
                    }
                }
            }

            if ($privateRepoVersion && $publicRepoVersion && version_compare($publicRepoVersion, $privateRepoVersion, '>')) {
                $exceptionMessage = "Higher matching version {$publicRepoVersion} of {$packageName} was found in public repository packagist.org 
                             than {$privateRepoVersion} in private {$privateRepoUrl}. Public package might've been taken over by a malicious entity, 
                             please investigate and update package requirement to match the version from the private repository";

                if (array_key_exists($packageName, $this->getNonFixedConstraintList($event->getRequest()))) {
                    throw new Exception($exceptionMessage);
                } else {
                    $this->io->writeError('<warning>' . $exceptionMessage . '</warning>');
                }
            }
        }
    }
}
