<?php
/**
 * ObjectFormatter.php
 * @package
 * @author  Bartłomiej Kuleszewicz <bartlomiej.kuleszewicz@znanylekarz.pl>
 * @date    23.23.2013 12:22
 */


namespace Docplanner\PropelBehavior\ExtendedCollection\Formatter;


use Docplanner\PropelBehavior\ExtendedCollection\Collection\ObjectCollection;

class ObjectFormatter extends \PropelObjectFormatter
{
	protected $collectionName = ObjectCollection::class;
}