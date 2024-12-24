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
     *
     * @param Context $context
     * @param bool $isTest
     * @throws \Exception
     */
    public function exportAndSendMail(Context $context, bool $isTest = false): void
    {
        // Export-Logik
        $csvData = $this->generateCsv($context);
        $filePath = $this->saveCsv($csvData);

        // E-Mail senden
        $this->sendEmail($filePath, $isTest, $context);
    }

    /**
     * Generiert die CSV-Daten
     *
     * @param Context $context
     * @return string
     */
    private function generateCsv(Context $context): string
    {
        $criteria = new Criteria();
        $products = $this->productRepository->search($criteria, $context)->getEntities();

        // CSV Header
        $csv = "Artikelnummer;EAN;Name;Lagerbestand\n";
        
        foreach ($products as $product) {
            $csv .= sprintf(
                "%s;%s;%s;%d\n",
                $product->getProductNumber(), // Artikelnummer/SKU
                $product->getEan() ?: '', // EAN, falls nicht vorhanden leerer String
                $product->getTranslation('name'),
                $product->getStock()
            );
        }

        return $csv;
    }

    /**
     * Speichert die CSV-Daten lokal
     *
     * @param string $csvData
     * @return string
     * @throws \Exception
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

        return $filePath;
    }

    /**
     * Sendet die E-Mail mit dem CSV-Anhang
     *
     * @param string $filePath
     * @param bool $isTest
     * @param Context $context
     * @throws \Exception
     */
    private function sendEmail(string $filePath, bool $isTest, Context $context): void
    {
        $recipientEmails = $this->systemConfigService->get('Lagerbestandsexport.config.exportEmailAddresses');
        $senderEmail = $this->systemConfigService->get('Lagerbestandsexport.config.senderEmailAddress');
        $emailSubject = $this->systemConfigService->get('Lagerbestandsexport.config.emailSubject');
        $emailSubject = str_replace('%date%', date('Y-m-d'), $emailSubject);

        if ($isTest) {
            $recipientEmails = $senderEmail;
        }

        $email = (new Email())
            ->from(new Address($senderEmail, 'Lagerbestandsexport'))
            ->to(...array_map('trim', explode(',', $recipientEmails)))
            ->subject($emailSubject)
            ->text('Im Anhang finden Sie den aktuellen Lagerbestand.')
            ->attachFromPath($filePath);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            throw new \Exception("Fehler beim E-Mail-Versand: " . $e->getMessage());
        }
    }
}
