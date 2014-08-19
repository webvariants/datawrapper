<?php

namespace Datawrapper\ORM\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'organization' table.
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
class OrganizationTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.OrganizationTableMap';

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
        $this->setName('organization');
        $this->setPhpName('Organization');
        $this->setClassname('Datawrapper\\ORM\\Organization');
        $this->setPackage('');
        $this->setUseIdGenerator(false);
        // columns
        $this->addPrimaryKey('id', 'Id', 'VARCHAR', true, 128, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 512, null);
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', true, null, null);
        $this->addColumn('deleted', 'Deleted', 'BOOLEAN', false, 1, false);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Chart', 'Datawrapper\\ORM\\Chart', RelationMap::ONE_TO_MANY, array('id' => 'organization_id', ), null, null, 'Charts');
        $this->addRelation('UserOrganization', 'Datawrapper\\ORM\\UserOrganization', RelationMap::ONE_TO_MANY, array('id' => 'organization_id', ), null, null, 'UserOrganizations');
        $this->addRelation('PluginOrganization', 'Datawrapper\\ORM\\PluginOrganization', RelationMap::ONE_TO_MANY, array('id' => 'organization_id', ), null, null, 'PluginOrganizations');
        $this->addRelation('OrganizationProduct', 'Datawrapper\\ORM\\OrganizationProduct', RelationMap::ONE_TO_MANY, array('id' => 'organization_id', ), null, null, 'OrganizationProducts');
        $this->addRelation('User', 'Datawrapper\\ORM\\User', RelationMap::MANY_TO_MANY, array(), null, null, 'Users');
        $this->addRelation('Plugin', 'Datawrapper\\ORM\\Plugin', RelationMap::MANY_TO_MANY, array(), null, null, 'Plugins');
        $this->addRelation('Product', 'Datawrapper\\ORM\\Product', RelationMap::MANY_TO_MANY, array(), null, null, 'Products');
    } // buildRelations()

} // OrganizationTableMap
