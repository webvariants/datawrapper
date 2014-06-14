<?php

namespace Datawrapper\ORM;

use Datawrapper\ORM\om\BaseJob;


/**
 * Skeleton subclass for representing a row from the 'job' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.
 */
class Job extends BaseJob {
    public function getParameter() {
        return json_decode(parent::getParameter(), true);
    }
}