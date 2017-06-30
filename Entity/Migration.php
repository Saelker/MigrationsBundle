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
	private $folder;

	/**
	 * @var \DateTime
	 */
	private $createdAt;


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
	 * Set identifier
	 *
	 * @param string $identifier
	 *
	 * @return Migration
	 */
	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;

		return $this;
	}

	/**
	 * Get identifier
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}

	/**
	 * Set folder
	 *
	 * @param string $folder
	 *
	 * @return Migration
	 */
	public function setFolder($folder)
	{
		$this->folder = $folder;

		return $this;
	}

	/**
	 * Get folder
	 *
	 * @return string
	 */
	public function getFolder()
	{
		return $this->folder;
	}

	/**
	 * Set createdAt
	 *
	 * @param \DateTime $createdAt
	 *
	 * @return Migration
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Get createdAt
	 *
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}
}

