<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BillingController extends AbstractController
{
    #[Route('/admin/order/{id}/billing', name: 'app_billing')]
    public function index($id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        /* $pdfOptions->set('isRemoteEnabled', true); */

        $domPdf = new Dompdf($pdfOptions);

        /* $logoPath = realpath($this->getParameter('kernel.project_dir') . '/public/uploads/images/Logo-Noir-Long.jpg');
        if (!$logoPath) {
            throw new \Exception('Le fichier Logo-Noir-Long.jpg est introuvable.');
        }
        $logoPath = 'file://' . $logoPath; */

        $html = $this->renderView('billing/index.html.twig', [
            'order' => $order,
            /* 'logoPath' => $logoPath */
        ]);

        /* echo $html;
        die();  */

        $domPdf->loadHtml($html);
        $domPdf->render();
        $domPdf->stream("tamizee-billing-n-".$order->getId().'.pdf', [
            'Attachment'=>false,
        ]);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }
}
