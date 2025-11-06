<?php

namespace App\Service;

final class DecisionNumberGenerator
{
    public function generate(?\DateTimeInterface $date = null): string
    {
        $y = ($date ?? new \DateTimeImmutable())->format('Y');
        $rand = strtoupper(bin2hex(random_bytes(3)));
        return sprintf('GO-%s-%s', $y, $rand);
    }
}
