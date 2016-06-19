<?php
/**
 *
 * @version 0.0.1: ${FILE_NAME} 4/06/2016T06:23
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/
namespace TruckerTracker;

require_once __DIR__ . '/../../TestTrait.php';
require_once __DIR__ . '/../../TestCase.php';

abstract class ConfigControllerTestCase extends TestCase
{

    use TestTrait;

    /**
     * @before
     */
    public function setUp()
    {

        parent::setUp();


    }


}