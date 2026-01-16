<?php
/**
 * Rector configuration file.
 *
 * > NOTE: {@see AddTypeToConstRector::canBeInherited()} has to be modified manually
 * > so it actually adds types to constants because the rule expects classes to be
 * > final, which is almost never the case in the codebase.
 *
 * @package Serializers
 * @subpackage Core
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\FuncCall\NullToStrictIntPregSlitFuncCallLimitArgRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindKeyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;

const TYPE_ROOT = 'root';
const TYPE_FOREACH = 'foreach';
const TYPE_UTILS = 'utils';

$enabled = array(
    TYPE_ROOT,
    TYPE_UTILS,
    TYPE_FOREACH
);

$paths = array(
    __DIR__ . '/src/classes',
    __DIR__ . '/tests',
);

$ruleDefs = array(
    AddTypeToConstRector::class => TYPE_ROOT,
    ExplicitNullableParamTypeRector::class => TYPE_ROOT,
    NewMethodCallWithoutParenthesesRector::class => TYPE_UTILS,
    Utf8DecodeEncodeToMbConvertEncodingRector::class => TYPE_UTILS,
    NullToStrictIntPregSlitFuncCallLimitArgRector::class => TYPE_UTILS,
    NullToStrictStringFuncCallArgRector::class => TYPE_UTILS,
    ArrayToFirstClassCallableRector::class => TYPE_UTILS,
    ForeachToArrayAllRector::class => TYPE_FOREACH,
    ForeachToArrayAnyRector::class => TYPE_FOREACH,
    ForeachToArrayFindKeyRector::class => TYPE_FOREACH,
    ForeachToArrayFindRector::class => TYPE_FOREACH,
);

$rules = array();
foreach ($ruleDefs as $rule => $type) {
    if (in_array($type, $enabled, true)) {
        $rules[] = $rule;
    }
}

echo "Running Rector with the following rules:\n";
foreach ($rules as $rule) {
    echo " - " . $rule . "\n";
}

echo "\n";

return RectorConfig::configure()
    ->withPaths($paths)
    ->withRules($rules);
