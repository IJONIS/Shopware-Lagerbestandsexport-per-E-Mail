<?php declare(strict_types=1);

namespace Lagerbestandsexport\Command;

use Lagerbestandsexport\Service\ExportService;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    protected static $defaultName = 'lagerbestandsexport:export';

    private ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        parent::__construct(self::$defaultName);
        $this->exportService = $exportService;
    }

    protected function configure(): void
    {
        $this->setDescription('Führt den Lagerbestand-Export manuell aus');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('Starte Export...');
            $this->exportService->exportAndSendMail(Context::createDefaultContext());
            $output->writeln('Export erfolgreich abgeschlossen.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Fehler beim Export: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
