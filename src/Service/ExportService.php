<?php declare(strict_types=1);

namespace Lagerbestandsexport\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class ExportService
{
    private EntityRepository $productRepository;
    private SystemConfigService $systemConfigService;
    private string $projectDir;
    private MailerInterface $mailer;

    public function __construct(
        EntityRepository $productRepository,
        SystemConfigService $systemConfigService,
        string $projectDir,
        MailerInterface $mailer
    ) {
        $this->productRepository = $productRepository;
        $this->systemConfigService = $systemConfigService;
        $this->projectDir = $projectDir;
        $this->mailer = $mailer;
    }

    /**
     * Exportiert die Lagerbestände und sendet die E-Mail
     */
    public function exportAndSendMail(Context $context): void
    {
        $csvData = $this->generateCsv($context);
        
        // Temporäre Datei für den E-Mail-Versand erstellen
        $tempFile = tempnam(sys_get_temp_dir(), 'lagerbestand_');
        file_put_contents($tempFile, $csvData);

        // Wenn aktiviert, Export auf Server speichern
        $saveExportFile = $this->systemConfigService->get('Lagerbestandsexport.config.saveExportFile');
        $filePath = $tempFile;

        if ($saveExportFile) {
            $filePath = $this->saveCsv($csvData);
        }

        try {
            $this->sendEmail($filePath, $context);
        } finally {
            // Temporäre Datei löschen, wenn sie nicht auf dem Server gespeichert wurde
            if (!$saveExportFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Generiert die CSV-Daten
     */
    private function generateCsv(Context $context): string
    {
        $criteria = new Criteria();
        $products = $this->productRepository->search($criteria, $context)->getEntities();

        $csv = "Artikelnummer;EAN;Name;Lagerbestand\n";
        
        foreach ($products as $product) {
            $csv .= sprintf(
                "%s;%s;%s;%d\n",
                $product->getProductNumber(),
                $product->getEan() ?: '',
                $product->getTranslation('name'),
                $product->getStock()
            );
        }

        return $csv;
    }

    /**
     * Speichert die CSV-Daten lokal
     */
    private function saveCsv(string $csvData): string
    {
        $exportDir = $this->projectDir . '/files/export';
        if (!is_dir($exportDir)) {
            if (!mkdir($exportDir, 0755, true) && !is_dir($exportDir)) {
                throw new \Exception("Unable to create export directory: $exportDir");
            }
        }

        $fileName = 'lagerbestand_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = $exportDir . '/' . $fileName;

        if (file_put_contents($filePath, $csvData) === false) {
            throw new \Exception("Unable to write CSV file: $filePath");
        }

        // Alte Dateien aufräumen
        $maxFiles = (int)$this->systemConfigService->get('Lagerbestandsexport.config.maxStoredFiles');
        if ($maxFiles > 0) {
            $this->cleanupOldFiles($exportDir, $maxFiles);
        }

        return $filePath;
    }

    /**
     * Löscht alte Export-Dateien
     */
    private function cleanupOldFiles(string $directory, int $maxFiles): void
    {
        $files = glob($directory . '/lagerbestand_*.csv');
        if ($files === false) {
            return;
        }

        // Sortiere Dateien nach Änderungsdatum (neueste zuerst)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Lösche überzählige Dateien
        for ($i = $maxFiles; $i < count($files); $i++) {
            if (file_exists($files[$i])) {
                unlink($files[$i]);
            }
        }
    }

    /**
     * Sendet die E-Mail mit dem CSV-Anhang
     */
    private function sendEmail(string $filePath, Context $context): void
    {
        $recipientEmails = $this->systemConfigService->get('Lagerbestandsexport.config.exportEmailAddresses');
        $senderEmail = $this->systemConfigService->get('Lagerbestandsexport.config.senderEmailAddress');
        $senderName = $this->systemConfigService->get('Lagerbestandsexport.config.senderName') ?? 'Lagerbestandsexport';
        $emailSubject = $this->systemConfigService->get('Lagerbestandsexport.config.emailSubject');
        $emailContent = $this->systemConfigService->get('Lagerbestandsexport.config.emailContent');

        // Ersetze Platzhalter im Betreff und Inhalt
        $date = date('Y-m-d');
        $emailSubject = str_replace('%date%', $date, $emailSubject);
        $emailContent = str_replace('%date%', $date, $emailContent);

        $email = (new Email())
            ->from(new Address($senderEmail, $senderName))
            ->to(...array_map('trim', explode(',', $recipientEmails)))
            ->subject($emailSubject)
            ->text($emailContent)
            ->attachFromPath($filePath);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            throw new \Exception("Fehler beim E-Mail-Versand: " . $e->getMessage());
        }
    }
}
