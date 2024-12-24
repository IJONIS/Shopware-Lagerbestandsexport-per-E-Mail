<?php declare(strict_types=1);

namespace Lagerbestandsexport\Controller;

use Lagerbestandsexport\Service\ExportService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class TestMailController extends AbstractController
{
    private ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * @Route("/api/_action/lagerbestandsexport/send/test", name="api.action.lagerbestandsexport.send.test", methods={"POST"})
     */
    public function sendTestMail(Context $context): JsonResponse
    {
        try {
            $this->exportService->exportAndSendMail($context, true);
            return new JsonResponse(['success' => true, 'message' => 'Test-E-Mail wurde erfolgreich versendet.']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
