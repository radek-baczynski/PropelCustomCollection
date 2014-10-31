<?php
/**
 * ObjectCollection.php.
 * @package
 * @author  Bartłomiej Kuleszewicz <bartlomiej.kuleszewicz@znanylekarz.pl>
 * @date    23.23.2013 12:23
 */


namespace Docplanner\PropelBehavior\ExtendedCollection\Collection;

class ObjectCollection extends \PropelObjectCollection
{
	const SORT_DESC = -1;
	const SORT_ASC  = 1;

	/**
	 * Get an associative array of objects form collection
	 * The first parameter specifies the column to be used for the key,
	 * And the seconf for the value.
	 *
	 * <code>
	 *   $res = $coll->toKeyObject('Id');
	 * </code>
	 * <code>
	 *   $res = $coll->toKeyObject(array('RelatedModel', 'Name'));
	 * </code>
	 *
	 * @param string|array $keyColumn The name of the column, or a list of columns to call.
	 *
	 * @return array
	 */
	public function toKeyObject($keyColumn = 'PrimaryKey')
	{
		$ret = array();

		if (!is_array($keyColumn))
		{
			$keyColumn = array($keyColumn);
		}

		foreach ($this as $obj)
		{
			$ret[$this->getValueForColumns($obj, $keyColumn)] = $obj;
		}

		return $ret;
	}

	public function toFlatArray($column)
	{
		$ret = [];

		if (!is_array($column))
		{
			$column = array($column);
		}

		foreach ($this as $object)
		{
			$ret[] = $this->getValueForColumns($object, $column);
		}

		return $ret;
	}

	public function groupByColumn($keyColumn)
	{
		$ret = [];

		$getterMethod = 'get' . $keyColumn;
		foreach ($this as $obj)
		{
			$ret[$obj->$getterMethod()][] = $obj;
		}

		return $ret;
	}

	public function groupByCallback($callback)
	{
		$ret = [];

		foreach ($this as $obj)
		{
			$ret[$callback($obj)][] = $obj;
		}

		return $ret;
	}

	/**
	 * Filter collection by callback function, same way as array_filter function
	 *
	 * @param $callback
	 * @return ObjectCollection
	 */
	public function filterByCallback($callback)
	{
		$ret = new self();
		$ret->setModel($this->getModel());
		$formatter = $this->getFormatter();
		if ($formatter)
		{
			$ret->setFormatter($formatter);
		}
		$ret->setData(array_filter($this->getData(), $callback));

		return $ret;
	}

	/**
	 * array_walk for collection
	 *
	 * @param callable $callback
	 * @return bool
	 */
	public function walk($callback)
	{
		$data = $this->getData();
		array_walk($data, $callback);

		return $data;
	}

	/**
	 * array_map for collection
	 *
	 * @param callable $callback
	 * @return bool
	 */
	public function map($callback)
	{
		return array_map($callback, $this->getData());
	}

	/**
	 * @param $fieldName string method name, column name or virtual column name
	 * @param $value
	 * @return ObjectCollection filtered objects
	 */
	public function filterByField($fieldName, $value)
	{
		return $this->filterByCallback(function (\BaseObject $element) use ($fieldName, $value)
		{
			if (method_exists($element, $fieldName))
			{
				$val = $element->$fieldName();
			}
			elseif (method_exists($element, $method = 'get' . $fieldName))
			{
				$val = $element->$method();
			}
			elseif ($element->hasVirtualColumn($fieldName))
			{
				$val = $element->getVirtualColumn($fieldName);
			}
			else
			{
				return false;
			}

			if (is_array($value))
			{
				return in_array($val, $value);
			}
			else
			{
				return $val == $value;
			}
		});
	}

	/**
	 * Sort objects in collection
	 *
	 * @param     $objectMethodName string collection member method used for sorting
	 * @param int $direction
	 */
	public function sortBy($objectMethodName, $direction = self::SORT_DESC)
	{
		$this->uasort(function ($a, $b) use ($objectMethodName, $direction)
		{
			return $direction * strcasecmp($a->$objectMethodName(), $b->$objectMethodName());
		});
	}

	/**
	 * @param string 		$keyColumn
	 * @param bool   		$usePrefix
	 * @param array|null   	$columnParams
	 *
	 * @return array
	 */
	public function getArrayCopy($keyColumn = null, $usePrefix = false, $columnParams = null)
	{
		$tmpResult = parent::getArrayCopy($keyColumn, $usePrefix);

		if (null === $columnParams)
		{
			return $tmpResult;
		}

		$result = [];
		$keyGetterMethod = 'get' . $keyColumn;

		foreach ($tmpResult as $item)
		{
			$key = call_user_func_array(array($item, $keyGetterMethod), $columnParams);
			$result[$key] = $item;
		}

		return $result;
	}

	/**
	 * Extracts data from collection by callback
	 *
	 * @param $callback
	 * @return ObjectCollection|
	 */
	public function extract($callback)
	{
		$ret = new self();

		foreach($this->getData() as $d)
		{
			$ret->append($callback($d));
		}

		return $ret;
	}

	/**
	 * Extracts data from collection by field name
	 *
	 * @param $field
	 * @return ObjectCollection
	 */
	public function extractField($field)
	{
		return $this->extract(function($e) use ($field) {
			return $e->$field();
		});
	}

	public function __toString()
	{
		return 'ObjectCollection('.$this->count().')';
	}
}