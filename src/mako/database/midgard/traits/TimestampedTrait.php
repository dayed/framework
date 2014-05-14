<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use \DateTime;

/**
 * Timestamped trait.
 *
 * @author  Frederic G. Østby
 */

trait TimestampedTrait
{
	/**
	 * Returns the column that holds the "created at" timestamp.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getCreatedAtColumn()
	{
		return isset($this->createdAtColumn) ? $this->createdAtColumn : 'created_at';
	}

	/**
	 * Returns the column that holds the "updated at" timestamp.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getUpdatedAtColumn()
	{
		return isset($this->updatedAtColumn) ? $this->updatedAtColumn : 'updated_at';
	}

	/**
	 * Returns the date time columns.
	 * 
	 * @access  public
	 * @return  array
	 */

	protected function getDateTimeColumns()
	{
		return array_merge(parent::getDateTimeColumns(), [$this->getCreatedAtColumn(), $this->getUpdatedAtColumn()]);
	}

	/**
	 * Allows you to update the "updated at" timestamp without modifying any data.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function touch()
	{
		if($this->exists)
		{
			$this->columns[$this->getUpdatedAtColumn()] = null;

			return $this->save();
		}

		return false;
	}

	/**
	 * Saves the record to the database.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function save()
	{
		// Set timestamps

		$dateTime = new DateTime;

		if(!$this->exists)
		{
			$this->columns[$this->getCreatedAtColumn()] = $dateTime;
		}

		if(!$this->exists || $this->isModified())
		{
			$this->columns[$this->getUpdatedAtColumn()] = $dateTime;
		}

		// Save record

		$saved = parent::save();

		// Touch related records

		if($saved === true && !empty($this->touch))
		{
			foreach($this->touch as $touch)
			{
				$relation = $this->$touch();

				$relation->update([$relation->getModel()->getUpdatedAtColumn() => $dateTime]);
			}
		}

		// Return save status

		return $saved;
	}
}