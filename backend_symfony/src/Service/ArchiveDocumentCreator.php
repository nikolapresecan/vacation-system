<?php

namespace App\Service;

use App\Entity\ArchiveDocument;
use App\Entity\ApprovalStatus;
use Doctrine\ORM\EntityManagerInterface;

class ArchiveDocumentCreator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VacationPdfRenderer $renderer,
        private readonly DecisionNumberGenerator $numGen,
        private readonly string $archiveDir, 
    ) {}

    public function createFromApproval(ApprovalStatus $approval): ArchiveDocument
    {
        $existing = $this->em->getRepository(ArchiveDocument::class)
            ->findOneBy(['approvalStatus' => $approval]);
        if ($existing) {
            return $existing; 
        }

        $request = $approval->getRequest();
        $employee = $request->getEmployee();
        $team = $request->getTeam();

        $issuedAt = new \DateTimeImmutable();
        $documentNumber = $this->numGen->generate($issuedAt);

        $vars = [
            'documentNumber'     => $documentNumber,
            'issuedAt'           => $issuedAt,
            'fullName'           => trim($employee->getFirstName().' '.$employee->getLastName()),
            'oib'                => $employee->getOib(),
            'start'              => $request->getStartDate(),
            'end'                => $request->getEndDate(),
            'days'               => $request->getNumberOfDays(),
            'teamName'           => method_exists($team, 'getName') ? $team->getName() : null,
            'projectManagerName' => $approval->getProjectManager()?->getFirstName().' '.$approval->getProjectManager()?->getLastName(),
        ];

        $pdfBytes = $this->renderer->render($vars);

        $year = $issuedAt->format('Y');
        $oib = $employee->getOib();
        $slug = $this->slugify($vars['fullName']);
        $filename = sprintf('%s_%s_%s.pdf', $documentNumber, $slug, $request->getStartDate()->format('Ymd'));
        $relative = sprintf('%s/%s/%s', $year, $oib, $filename);
        $absolute = rtrim($this->archiveDir, '/').'/'.$relative;

        $dir = \dirname($absolute);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Ne mogu kreirati direktorij arhive: '.$dir);
        }
        file_put_contents($absolute, $pdfBytes);

        $doc = new ArchiveDocument();
        $doc->setApprovalStatus($approval);
        $doc->setDocumentNumber($documentNumber);
        $doc->setFilePath($relative);
        $doc->setCreatedAt($issuedAt);

        $this->em->persist($doc);
        $this->em->flush();

        return $doc;
    }

    public function getAbsolutePath(ArchiveDocument $doc): string
    {
        return rtrim($this->archiveDir, '/').'/'.$doc->getFilePath();
    }

    public function readAbsolute(ArchiveDocument $doc): string
    {
        $absolute = $this->getAbsolutePath($doc);
        if (!is_file($absolute)) {
            throw new \RuntimeException('PDF nije pronađen: '.$absolute);
        }
        $bytes = file_get_contents($absolute);
        if ($bytes === false) {
            throw new \RuntimeException('Ne mogu pročitati PDF: '.$absolute);
        }
        return $bytes;
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: 'document';
    }
}
