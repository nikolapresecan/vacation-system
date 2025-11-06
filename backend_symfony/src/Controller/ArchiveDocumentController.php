<?php

namespace App\Controller;

use App\Entity\ApprovalStatus;
use App\Entity\ArchiveDocument;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/management')]
class ArchiveDocumentController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('/download/by-approval/{approvalId}', name: 'archive_download_by_approval', methods: ['GET'])]
    public function downloadByApproval(int $approvalId, HttpRequest $req): BinaryFileResponse|JsonResponse
    {
        $approval = $this->em->getRepository(ApprovalStatus::class)->find($approvalId);
        if (!$approval) {
            return new JsonResponse(['error' => 'Odobreni zahtjev nije pronađen'], 404);
        }

        $doc = $this->em->getRepository(ArchiveDocument::class)
            ->findOneBy(['approvalStatus' => $approval]);

        if (!$doc) {
            return new JsonResponse(['error' => 'Rješenje ne postoji za ovaj zahtjev'], 404);
        }

        $root = (string) $this->getParameter('app_archive_dir');
        $absolute = rtrim($root, '/').'/'.$doc->getFilePath();
        if (!is_file($absolute)) {
            return new JsonResponse(['error' => 'Datoteka nije pronađena na disku'], 404);
        }

        $response = new BinaryFileResponse($absolute);
        $response->headers->set('Content-Type', 'application/pdf'); 
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($absolute)
        );

        return $response;
    }
}
