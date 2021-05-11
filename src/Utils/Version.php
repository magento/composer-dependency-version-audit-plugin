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
use Magento\ComposerDependencyVersionAuditPlugin\VersionSelectorFactory;

/**
 * Wrapper class for calling Composer functions
 */
class Version
{

    /**
     * Get Highest version package
     *
     * @param Composer $composer
     * @param string $packageName
     * @param RepositoryInterface $repository
     * @return PackageInterface|bool
     */
    public function findBestCandidate(Composer $composer, string $packageName, RepositoryInterface $repository)
    {
        $pool = new Pool(
            $composer->getPackage()->getMinimumStability(),
            $composer->getPackage()->getStabilityFlags()
        );
        $pool->addRepository($repository);
        $versionSelector = VersionSelectorFactory::create($pool);
        return $versionSelector->findBestCandidate($packageName);
    }
}