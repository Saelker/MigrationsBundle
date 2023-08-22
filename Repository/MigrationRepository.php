<?php

namespace Saelker\MigrationsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Util\ImportFile;
use Symfony\Component\Finder\Finder;

class MigrationRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Migration::class);
	}

	public function getLatestMigration($directory): mixed
	{
		return $this
			->getIdentifierQueryBuilder($directory)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	private function getIdentifierQueryBuilder($directory): QueryBuilder
	{
		return $this
			->getQueryBuilder()
			->andWhere('m.directory = :directory')
			->orderBy('m.identifier', 'DESC')
			->setParameter('directory', $directory);
	}

	public function getQueryBuilder(): QueryBuilder
	{
		return $this
			->createQueryBuilder('m');
	}

	public function getAllMigrationIdentifiers($directory): mixed
	{
		return $this
			->getIdentifierQueryBuilder($directory)
			->select('m.identifier')
			->getQuery()
			->getResult();
	}

	public function getLatestSequence(): mixed
	{
		try {
			$sequence = $this
				->getQueryBuilder()
				->select('MAX(m.sequence) as sequence')
				->getQuery()
				->getOneOrNullResult();

			$sequence = $sequence['sequence'];
		} catch (\Exception) {
			$sequence = 0;
		}

		return $sequence;
	}

	public function getNextIdentifier($directory): string
	{
		$currentIdentifier = (new \DateTime())->format('YmdHis');

		$sort = fn(\SplFileInfo $a, \SplFileInfo $b): int => strcmp($b->getRealPath(), $a->getRealPath());

		$finder = new Finder();
		$finder
			->files()
			->in($directory)
			->name('/V_\d*_.*/')
			->sort($sort);

		if ($finder->getIterator()->current()) {
			$lastFile = new ImportFile($finder->getIterator()->current(), null, null, null, null, null);
			$newNumber = substr($lastFile->getFileIdentifier(), 0, -3) == $currentIdentifier ? (int)substr($lastFile->getFileIdentifier(), -3) + 1 : 1;
		} else {
			$newNumber = 1;
		}

		return $currentIdentifier . sprintf('%03d', $newNumber);
	}

	public function deleteFromSequence(int $sequence): void
	{
		$this
			->getEntityManager()
			->createQueryBuilder()
			->delete(Migration::class, 'm')
			->andWhere('m.sequence = :sequenceFilter')
			->setParameter('sequenceFilter', $sequence)
			->getQuery()
			->execute();
	}
}
