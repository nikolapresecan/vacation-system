<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\Request;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendVacationApprovalEmail(Request $vacationRequest, string $pdfBytes, string $documentNumber, string $filename = 'Rjesenje_GO.pdf'): void
    {
        $employee = $vacationRequest->getEmployee();

        $email = (new Email())
            ->from('nikolapresecan11@gmail.com')
            ->to($employee->getEmail())
            ->subject('Zahtjev za godišnji odmor odobren')
            ->html('<p>Vaš zahtjev za godišnji odmor od ' . $vacationRequest->getStartDate()->format('d.m.Y.') . ' do ' . $vacationRequest->getEndDate()->format('d.m.Y.') . ' je odobren.</p>')
            ->attach($pdfBytes, $filename, 'application/pdf');

        $this->mailer->send($email);
    }

    public function sendVacationDeclineEmail(Request $vacationRequest)
    {
        $employee = $vacationRequest->getEmployee();

        $email = (new Email())
            ->from('nikolapresecan11@gmail.com')
            ->to($employee->getEmail())
            ->subject('Zahtjev za godišnji odmor odbijen')
            ->html('<p>Vaš zahtjev za godišnji odmor od ' . $vacationRequest->getStartDate()->format('d.m.Y,') . ' do ' . $vacationRequest->getEndDate()->format('d.m.Y.') . ' je odbijen.</p>');

        $this->mailer->send($email);
    }

    public function sendResetPasswordEmail(string $to, string $template, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->from('nikolapresecan11@gmail.com')
            ->to($to)
            ->subject('Ponovno postavljanje lozinke')
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }

    public function sendExportEmail(string $to, string $filePath, string $format): void
    {
        $email = (new Email())
            ->from('nikolapresecan11@gmail.com')
            ->to($to)
            ->subject('Export Employees')
            ->text('Attachment in format: ' . strtoupper($format))
            ->attachFromPath($filePath);

        $this->mailer->send($email);
    }
}