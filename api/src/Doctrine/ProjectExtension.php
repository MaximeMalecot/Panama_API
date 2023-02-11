<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Project;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class ProjectExtension implements QueryCollectionExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Project::class !== $resourceClass || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }
        if(null !== $user = $this->security->getUser()){
            if($this->security->isGranted('ROLE_CLIENT')) {
                $rootAlias = $queryBuilder->getRootAliases()[0];
                $queryBuilder->andWhere(sprintf('%s.status IN (:status)', $rootAlias));
                $queryBuilder->orWhere(sprintf('%s.owner = :current_user', $rootAlias));
                $queryBuilder->setParameters([
                    'current_user' => $user,
                    'status' => array('ACTIVE')
                ]);
            } else if($this->security->isGranted('ROLE_FREELANCER_PREMIUM')) {
                $rootAlias = $queryBuilder->getRootAliases()[0];
                $queryBuilder->leftJoin(sprintf('%s.propositions', $rootAlias), 'p');
                $queryBuilder->andWhere(sprintf('%s.status IN (:status)', $rootAlias));
                $queryBuilder->orWhere("p.freelancer = :current_user AND p.status = :proposition_status");
                $queryBuilder->setParameters([
                    'status' => array('ACTIVE'),
                    'current_user' => $user,
                    'proposition_status' => 'ACCEPTED'
                ]);
            } else {
                $rootAlias = $queryBuilder->getRootAliases()[0];
                $queryBuilder->andWhere(sprintf('%s.status in (:status)', $rootAlias));
                $queryBuilder->setParameter('status', array('ACTIVE'));
            }
        } else {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere(sprintf('%s.status in (:status)', $rootAlias));
            $queryBuilder->setParameter('status', array('ACTIVE'));
        }
    }
}