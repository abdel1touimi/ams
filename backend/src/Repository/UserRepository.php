<?php

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class UserRepository
{
    private DocumentRepository $repository;

    public function __construct(private readonly DocumentManager $dm)
    {
        $this->repository = $dm->getRepository(User::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function save(User $user): void
    {
        $this->dm->persist($user);
        $this->dm->flush();
    }
}
