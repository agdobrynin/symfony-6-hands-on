<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class FlashTypeService implements FlashTypeServiceInterface
{
    private FlashBagInterface|null $flashBag;

    public function __construct(RequestStack $requestStack)
    {
        $this->flashBag = $requestStack->getCurrentRequest()
            ?->getSession()
            ?->getFlashBag();
    }

    public function successes(): array
    {
        if ($this->flashBag instanceof FlashBagInterface) {
            return $this->flashBag->get(FlashTypeServiceInterface::SUCCESS);
        }

        throw new \RuntimeException('Not defined FlashBag');
    }

    public function errors(): array
    {
        if ($this->flashBag instanceof FlashBagInterface) {
            return $this->flashBag->get(FlashTypeServiceInterface::ERROR);
        }

        throw new \RuntimeException('Not defined FlashBag');
    }
}
