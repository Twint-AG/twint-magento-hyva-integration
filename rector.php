<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/Magewire', __DIR__ . '/Plugin'])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    // register single rule
    ->withRules([TypedPropertyFromStrictConstructorRector::class])
    // here we can define, what prepared sets of rules will be applied
    ->withPreparedSets(deadCode: true, codeQuality: true);
