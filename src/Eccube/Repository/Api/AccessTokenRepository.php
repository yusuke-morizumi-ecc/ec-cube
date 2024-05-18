<?php

namespace Eccube\Repository\Api;

use Eccube\Repository\AbstractRepository;
use Eccube\Entity\Api\AccessToken;
use Doctrine\Persistence\ManagerRegistry as RegistryInterface;

class AccessTokenRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }
}
