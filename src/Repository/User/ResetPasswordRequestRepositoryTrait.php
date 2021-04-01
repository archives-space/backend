<?php

/*
 * This file is part of the SymfonyCasts ResetPasswordBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\User;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

/**
 * Trait can be added to a Doctrine ORM repository to help implement
 * ResetPasswordRequestRepositoryInterface.
 *
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
trait ResetPasswordRequestRepositoryTrait
{
    public function getUserIdentifier(object $user): string
    {
        return $this->customGetDocumentManager()
            ->getUnitOfWork()
            ->getDocumentIdentifier($user)
        ;
    }

    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->customGetDocumentManager()->persist($resetPasswordRequest);
        $this->customGetDocumentManager()->flush();
    }

    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        return $this->findOneBy(['selector' => $selector]);
    }

    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        // Normally there is only 1 max request per use, but written to be flexible
        /** @var ResetPasswordRequestInterface $resetPasswordRequest */
        $resetPasswordRequest =  $this->getqueryBuilder()
            ->field('user')->equals($user)
            ->sort('requestedAt', 'DESC')
            ->limit(1)
            ->getQuery()
            ->getSingleResult()
        ;

        if (null !== $resetPasswordRequest && !$resetPasswordRequest->isExpired()) {
            return $resetPasswordRequest->getRequestedAt();
        }

        return null;
    }

    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $tokens =  $this->getqueryBuilder()
            ->field('user')->equals($resetPasswordRequest->getUser())
            ->getQuery()
            ->execute()
        ;

        foreach ($tokens as $token) {
            $this->dm->remove($token);
        }
        $this->dm->flush();
    }

    public function removeExpiredResetPasswordRequests(): int
    {
        $time = new \DateTimeImmutable('-1 week');
        $tokens = $this->getqueryBuilder()
            ->field('expiresAt')->lte($time)
            ->getQuery()
            ->execute()
        ;

        foreach ($tokens as $token) {
            $this->dm->remove($token);
        }

        $this->dm->flush();
        return 0;
    }

    /**
     * @return Builder
     */
    private function getqueryBuilder()
    {
        return $this->createQueryBuilder('t');
    }

    /**
     * @return DocumentManager
     */
    private function customGetDocumentManager()
    {
        return $this->getDocumentManager();
    }
}
