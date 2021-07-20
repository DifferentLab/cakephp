<?php

use Rector\Core\Configuration\Option;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // paths to refactor; solid alternative to CLI arguments
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/app', __DIR__ . '/lib']);

    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_40);
};
