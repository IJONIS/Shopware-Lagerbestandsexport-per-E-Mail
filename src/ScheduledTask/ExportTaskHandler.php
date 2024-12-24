<?php declare(strict_types=1);

namespace Lagerbestandsexport\ScheduledTask;

use Lagerbestandsexport\Service\ExportService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ExportTaskHandler extends ScheduledTaskHandler
{
    private ExportService $exportService;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        ExportService $exportService
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->exportService = $exportService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ExportTask::class];
    }

    public function run(): void
    {
        $this->exportService->exportAndSendMail(Context::createDefaultContext());
    }
}
