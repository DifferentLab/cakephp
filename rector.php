<?php

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->bootstrapFiles([
		__DIR__ . '/app/webroot/index.php'
	]);

	$rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

	$rectorConfig->paths([
		__DIR__ . '/app',
		__DIR__ . '/lib',
	]);

	$rectorConfig->skip([
		__DIR__ . '/app/tmp',
		__DIR__ . '/lib/Cake/Test',
		LongArrayToShortArrayRector::class,
		MixedTypeRector::class,
		ListToArrayDestructRector::class
	]);

	$rectorConfig->sets([
		SetList::PHP_81,
		LevelSetList::UP_TO_PHP_81
	]);
};
