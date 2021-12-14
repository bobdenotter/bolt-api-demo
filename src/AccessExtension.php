<?php

namespace App;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AccessExtension extends AbstractExtension
{
    /** @var Security */
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    public function getFunctions()
    {
        return [
           new TwigFunction('deny_access_unless_granted' , [$this, 'denyAccessUnlessGranted'])
        ];
    }

    public function denyAccessUnlessGranted(string $role): void
    {
        if(! $this->security->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }
}
