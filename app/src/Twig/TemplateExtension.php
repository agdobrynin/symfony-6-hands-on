<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TemplateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('mask_email', [$this, 'maskEmail']),
        ];
    }

    public function maskEmail(string $email): string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            list($first, $last) = explode('@', $email);
            $firstLen = (int)floor(strlen($first) / 2);
            $first = str_replace(substr($first, $firstLen), str_repeat('*', strlen($first) - $firstLen), $first);
            $lastIndex = strpos($last, '.');
            $last1 = substr($last, 0, $lastIndex);
            $last2 = substr($last, $lastIndex);
            $lastLen = (int)floor(strlen($last1) / 2);
            $last1 = str_replace(substr($last1, $lastLen), str_repeat('*', strlen($last1) - $lastLen), $last1);

            return $first . '@' . $last1 . $last2;
        }

        throw new \UnexpectedValueException(sprintf('string "%s" is not email address', $email));
    }
}
