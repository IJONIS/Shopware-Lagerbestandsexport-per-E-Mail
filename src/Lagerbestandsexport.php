<?php declare(strict_types=1);

namespace Lagerbestandsexport;

use Lagerbestandsexport\ScheduledTask\ExportTask;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class Lagerbestandsexport extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        // Registriere den Scheduled Task
        $this->registerScheduledTask();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if (!$uninstallContext->keepUserData()) {
            // Lösche den Scheduled Task nur, wenn die Benutzerdaten nicht behalten werden sollen
            $this->removeScheduledTask();
        }

        parent::uninstall($uninstallContext);
    }

    private function registerScheduledTask(): void
    {
        /** @var EntityRepository $scheduledTaskRepository */
        $scheduledTaskRepository = $this->container->get('scheduled_task.repository');

        // Prüfe, ob der Task bereits existiert
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', ExportTask::getTaskName()));

        $taskId = $scheduledTaskRepository->searchIds($criteria, \Shopware\Core\Framework\Context::createDefaultContext())->firstId();

        // Wenn der Task noch nicht existiert, erstelle ihn
        if ($taskId === null) {
            // Setze die erste Ausführung auf den nächsten Tag um 6:00 Uhr
            $nextExecution = new \DateTime('tomorrow 06:00:00');

            $scheduledTaskRepository->create([
                [
                    'name' => ExportTask::getTaskName(),
                    'scheduledTaskClass' => ExportTask::class,
                    'runInterval' => ExportTask::getDefaultInterval(),
                    'defaultRunInterval' => ExportTask::getDefaultInterval(),
                    'status' => 'scheduled',
                    'nextExecutionTime' => $nextExecution,
                ]
            ], \Shopware\Core\Framework\Context::createDefaultContext());
        }
    }

    private function removeScheduledTask(): void
    {
        /** @var EntityRepository $scheduledTaskRepository */
        $scheduledTaskRepository = $this->container->get('scheduled_task.repository');

        // Finde den Task
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', ExportTask::getTaskName()));

        $taskId = $scheduledTaskRepository->searchIds($criteria, \Shopware\Core\Framework\Context::createDefaultContext())->firstId();

        // Wenn der Task existiert, lösche ihn
        if ($taskId !== null) {
            $scheduledTaskRepository->delete([['id' => $taskId]], \Shopware\Core\Framework\Context::createDefaultContext());
        }
    }
}
