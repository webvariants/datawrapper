<?php

namespace Datawrapper\ORM\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'chart' table.
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
class ChartTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.ChartTableMap';

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
        $this->setName('chart');
        $this->setPhpName('Chart');
        $this->setClassname('Datawrapper\\ORM\\Chart');
        $this->setPackage('');
        $this->setUseIdGenerator(false);
        // columns
        $this->addPrimaryKey('id', 'Id', 'VARCHAR', true, 5, null);
        $this->addColumn('title', 'Title', 'VARCHAR', true, 255, null);
        $this->addColumn('theme', 'Theme', 'VARCHAR', true, 255, null);
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', true, null, null);
        $this->addColumn('last_modified_at', 'LastModifiedAt', 'TIMESTAMP', true, null, null);
        $this->addColumn('type', 'Type', 'VARCHAR', true, 200, null);
        $this->addColumn('metadata', 'Metadata', 'VARCHAR', true, 4096, null);
        $this->addColumn('deleted', 'Deleted', 'BOOLEAN', false, 1, false);
        $this->addColumn('deleted_at', 'DeletedAt', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('author_id', 'AuthorId', 'INTEGER', 'user', 'id', false, null, null);
        $this->addColumn('show_in_gallery', 'ShowInGallery', 'BOOLEAN', false, 1, false);
        $this->addColumn('language', 'Language', 'VARCHAR', false, 5, '');
        $this->addColumn('guest_session', 'GuestSession', 'VARCHAR', false, 255, null);
        $this->addColumn('last_edit_step', 'LastEditStep', 'INTEGER', false, null, 0);
        $this->addColumn('published_at', 'PublishedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('public_url', 'PublicUrl', 'VARCHAR', false, 255, null);
        $this->addColumn('public_version', 'PublicVersion', 'INTEGER', false, null, 0);
        $this->addForeignKey('organization_id', 'OrganizationId', 'VARCHAR', 'organization', 'id', false, 128, null);
        $this->addForeignKey('forked_from', 'ForkedFrom', 'VARCHAR', 'chart', 'id', false, 5, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('User', 'Datawrapper\\ORM\\User', RelationMap::MANY_TO_ONE, array('author_id' => 'id', ), null, null);
        $this->addRelation('Organization', 'Datawrapper\\ORM\\Organization', RelationMap::MANY_TO_ONE, array('organization_id' => 'id', ), null, null);
        $this->addRelation('ChartRelatedByForkedFrom', 'Datawrapper\\ORM\\Chart', RelationMap::MANY_TO_ONE, array('forked_from' => 'id', ), null, null);
        $this->addRelation('ChartRelatedById', 'Datawrapper\\ORM\\Chart', RelationMap::ONE_TO_MANY, array('id' => 'forked_from', ), null, null, 'ChartsRelatedById');
        $this->addRelation('Job', 'Datawrapper\\ORM\\Job', RelationMap::ONE_TO_MANY, array('id' => 'chart_id', ), null, null, 'Jobs');
    } // buildRelations()

} // ChartTableMap
