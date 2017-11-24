<?php

namespace Saelker\MigrationsBundle\Entity;

/**
 * Migration
 */
class Migration
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $identifier;

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var \DateTime
	 */
	private $createdAt;

	/**
	 * @var int
	 */
	private $sequence;

	/**
	 * @var string
	 */
	private $note;


	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}

	/**
	 * @param string $identifier
	 * @return Migration
	 */
	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDirectory()
	{
		return $this->directory;
	}

	/**
	 * @param string $directory
	 * @return Migration
	 */
	public function setDirectory($directory)
	{
		$this->directory = $directory;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt
	 * @return Migration
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSequence()
	{
		return $this->sequence;
	}

	/**
	 * @param int $sequence
	 * @return Migration
	 */
	public function setSequence($sequence)
	{
		$this->sequence = $sequence;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNote(): string
	{
		return $this->note;
	}

	/**
	 * @param string $note
	 * @return Migration
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}
}