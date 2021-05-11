<?php
/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ComposerDependencyVersionAuditPlugin;

use Composer\Package\Version\VersionSelector;
use Composer\DependencyResolver\Pool;

/**
 * Factory class for @see VersionSelector
 */
class VersionSelectorFactory
{

    /**
     * Creates New Instance
     *
     * @param Pool $pool
     * @return VersionSelector
     */
    public static function create(Pool $pool): VersionSelector
    {
        return new VersionSelector($pool);
    }
}
