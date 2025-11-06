<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment as Twig;

class VacationPdfRenderer
{
    public function __construct(private readonly Twig $twig) {}

    public function render(array $vars): string
    {
        $html = $this->twig->render('pdf/vacation_decision.html.twig', $vars);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
