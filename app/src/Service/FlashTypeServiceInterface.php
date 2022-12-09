<?php

namespace App\Service;

interface FlashTypeServiceInterface
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';

    public function successes(): array;

    public function errors(): array;
}
