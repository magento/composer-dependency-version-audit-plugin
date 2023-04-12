<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin\Utils;

use Composer\Composer;
use Composer\DependencyResolver\Pool;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\RepositorySet;
use Exception;

/**
 * Wrapper class for calling Composer functions
 */
class Version
{
    /**
     * Preferred stability level
     *
     * @var string
     */
    public const STABILITY_DEV = 'dev';

    /**
     * Get Highest version package
     *
     * @param Composer $composer
     * @param string $packageName
     * @param RepositoryInterface $repository
     * @return PackageInterface|null
     * @throws Exception
     */
    public function findBestCandidate(Composer $composer, string $packageName, RepositoryInterface $repository): ?PackageInterface
    {
        $composerVersion = method_exists('Composer\Composer', 'getVersion') ? Composer::getVersion() : Composer::VERSION;
        $composerMajorVersion = (int)explode('.', $composerVersion)[0];

        if ($composerMajorVersion === 1) {
            $bestCandidate = $this->findBestCandidateComposer1($composer, $packageName, $repository);
        } elseif ($composerMajorVersion === 2) {
            $bestCandidate = $this->findBestCandidateComposer2($composer, $packageName, $repository);
        }

        if($bestCandidate instanceof PackageInterface){
            return $bestCandidate;
        }
        return null;
    }

    /**
     * Get Highest version package for Composer V1
     *
     * @param Composer $composer
     * @param string $packageName
     * @param RepositoryInterface $repository
     * @return PackageInterface|false
     */
    public function findBestCandidateComposer1(Composer $composer, string $packageName, RepositoryInterface $repository)
    {
        $minStability = $composer->getPackage()->getMinimumStability();
        $stabilityFlags = $composer->getPackage()->getStabilityFlags();
        if (!$minStability) {
            $minStability = 'stable';
        }
        $pool = new Pool($minStability, $stabilityFlags);
        $pool->addRepository($repository);
        return (new VersionSelector($pool))->findBestCandidate($packageName, null, null, self::STABILITY_DEV);
    }

    /**
     * Get Highest version package for Composer V2
     *
     * @param Composer $composer
     * @param string $packageName
     * @param RepositoryInterface $repository
     * @return PackageInterface|false
     */
    public function findBestCandidateComposer2(Composer $composer, string $packageName, RepositoryInterface $repository)
    {
        $minStability = $composer->getPackage()->getMinimumStability();
        $stabilityFlags = $composer->getPackage()->getStabilityFlags();

        if (!$minStability) {
            $minStability = 'stable';
        }

        $repositorySet = new RepositorySet($minStability, $stabilityFlags);
        $repositorySet->addRepository($repository);

        return (new VersionSelector($repositorySet))->findBestCandidate($packageName, null, self::STABILITY_DEV);
    }
}
