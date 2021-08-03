<?php

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // paths to refactor; solid alternative to CLI arguments
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::BOOTSTRAP_FILES, [
        __DIR__ . '/app/webroot/index.php'
    ]);
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan.neon');

    $parameters->set(Option::SOURCE, [__DIR__ . '/app/', __DIR__ . '/lib/']);
    $parameters->set(Option::PATHS, [__DIR__ . '/lib/Cake/Test']);
    $parameters->set(Option::CLEAR_CACHE, true);

    $parameters->set(Option::SKIP, [
        __DIR__ . '/app/tmp/*',
    ]);

   $containerConfigurator->import(SetList::PHP_80);
};
