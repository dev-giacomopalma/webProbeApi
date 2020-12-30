<?php

namespace App\Command;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanRequestsCommand extends Command
{
	const COMMAND_NAME = 'requests:clean';

	/** @var EntityManagerInterface  */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct(self::COMMAND_NAME);
		$this->entityManager = $entityManager;
	}

	protected function configure()
	{
		$this->setName(self::COMMAND_NAME)
			->setDescription('clean requests older than a week');
	}

	public function run(InputInterface $input, OutputInterface $output)
	{
		$now = new DateTime('now');
		$lastCreationDate = $now->sub(new DateInterval('P7D'));
		$query = "
		DELETE FROM `requestResponse`
		WHERE `creation_date` < '{$lastCreationDate->format('Y-m-d H:i:s')}';
		";

		$conn = $this->entityManager->getConnection();
		$conn->executeQuery($query);

		return 0;
	}

}