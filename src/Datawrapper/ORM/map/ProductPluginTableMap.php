<?php

namespace Datawrapper\ORM\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'product_plugin' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator..map
 */
class ProductPluginTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.ProductPluginTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('product_plugin');
        $this->setPhpName('ProductPlugin');
        $this->setClassname('Datawrapper\\ORM\\ProductPlugin');
        $this->setPackage('');
        $this->setUseIdGenerator(false);
        $this->setIsCrossRef(true);
        // columns
        $this->addForeignPrimaryKey('product_id', 'ProductId', 'VARCHAR' , 'product', 'id', true, 128, null);
        $this->addForeignPrimaryKey('plugin_id', 'PluginId', 'VARCHAR' , 'plugin', 'id', true, 128, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Product', 'Datawrapper\\ORM\\Product', RelationMap::MANY_TO_ONE, array('product_id' => 'id', ), null, null);
        $this->addRelation('Plugin', 'Datawrapper\\ORM\\Plugin', RelationMap::MANY_TO_ONE, array('plugin_id' => 'id', ), null, null);
    } // buildRelations()

} // ProductPluginTableMap