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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }
}