<?php
/**
 * ObjectFormatter.php
 * @package
 * @author  Bartłomiej Kuleszewicz <bartlomiej.kuleszewicz@znanylekarz.pl>
 * @date    23.23.2013 12:45
 */
class ExtendedCollectionBehavior extends \Behavior
{
    protected $defaultFormatter = '\Docplanner\ExtendedCollection\Formatter\ObjectFormatter';
    protected $defaultCollectionClass = '\Docplanner\ExtendedCollection\Collection\ObjectCollection';

    public function queryAttributes()
    {
        $formatter = $this->getParameter('formatter');
        $formatter = $formatter ? : $this->defaultFormatter;

        $attributes = "protected \$defaultFormatterClass = '{$formatter}';
";

        return $attributes;
    }

    /**
     * @param \string $script
     */
    public function queryFilter(&$script)
    {
        $formatter = $this->getParameter('formatter');
        $formatter = $formatter ? : $this->defaultFormatter;

        $script = preg_replace('/@return\s+PropelObjectCollection\b/i', '@return '.$formatter.'|PropelObjectCollection', $script);
    }

    public function objectFilter(&$script)
    {
        $collectionClass = $this->getParameter('collection_class');
        $collectionClass = $collectionClass ? : $this->defaultCollectionClass;

        $script = preg_replace('/@return\s+PropelObjectCollection\b/i', '@return ' . $collectionClass . '', $script);
        $script = preg_replace('/@var\s+PropelObjectCollection\b/i', '@var ' . $collectionClass . '', $script);
        $script = preg_replace('/new\s+PropelObjectCollection\b/i', 'new ' . $collectionClass . '', $script);
    }
}