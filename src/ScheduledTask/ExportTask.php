<?php declare(strict_types=1);

namespace Lagerbestandsexport\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ExportTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'lagerbestandsexport.export';
    }

    public static function getDefaultInterval(): int
    {
        return 86400; // 24 Stunden in Sekunden
    }
}
