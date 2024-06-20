<?php

use NetInventors\EcsSetList\SetList;
use Reinfi\EasyCodingStandard\JUnitOutputFormatter;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\Contract\Console\Output\OutputFormatterInterface;

return static function (ECSConfig $config): void {
    $config->sets([ SetList::NET_INVENTORS ]);

    $baseDir = dirname(__DIR__, 3);

    $config->paths([
        $baseDir . '/src',
    ]);

    $config->tag(JUnitOutputFormatter::class, OutputFormatterInterface::class);

    $config->skip([
        $baseDir . '/dev-ops',
        $baseDir . '/vendor',
    ]);

    $config->lineEnding(PHP_EOL);
};
