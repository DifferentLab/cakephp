<?php

use Rector\Core\Configuration\Option;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // paths to refactor; solid alternative to CLI arguments
    $parameters = $containerConfigurator->parameters();
//    $parameters->set(Option::PATHS, [__DIR__ . '/app', __DIR__ . '/lib']);

    $parameters->set(Option::PATHS, [__DIR__ . '/lib/Cake/Test', __DIR__ . '/lib/Cake/TestSuite']);
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan.neon');
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_80);
//    $containerConfigurator->import(SetList::PHP_80);

};
