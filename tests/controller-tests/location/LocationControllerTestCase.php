<?php

/**
 *
 * @version 0.0.1: ${FILE_NAME} 5/06/2016T14:19
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/
namespace TruckerTracker;

use Artisan;

require_once __DIR__ . '/../../TestTrait.php';

abstract class LocationControllerTestCase extends TestCase
{
    use TestTrait;
    protected $user;


    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = $this->user();
    }

    protected function artisanSeedDb()
    {
        Artisan::call('db:seed', ['--class' => 'LocationControllerTestCaseDbSeeder']);
    }


}