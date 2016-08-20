<?php
use TruckerTracker\Driver;
use TruckerTracker\Location;
use TruckerTracker\Message;
use TruckerTracker\User;
use TruckerTracker\Vehicle;
use TruckerTracker\Organisation;
use TruckerTracker\TestDataTrait;

/**
 *
 * @version 0.0.1: ${FILE_NAME} 19/08/2016T20:33
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/

if (!trait_exists(TestDataTrait::class))
    include __DIR__.'/../../tests/TestDataTrait.php';

trait DbSeederTrait
{
    use TestDataTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fixture = $this->setDates($this->getFixture());
        foreach($fixture as $collection => $data){
            DB::collection($collection)->truncate();
            if (!empty($data)){
                $class = '\TruckerTracker\\'.substr(ucwords($collection),0,-1);
                foreach ($data as $document){
                    $class::create($document);
                }


//                switch ($collection){
//                    case 'organisation':
//                        Organisation::create($data);
//                        break;
//                    case 'driver':
//                        Driver::create($data);
//                        break;
//                    case 'location':
//                        Location::create($data);
//                        break;
//                    case 'user':
//                        User::create($data);
//                        break;
//                    case 'vehicle':
//                        Vehicle::create($data);
//                        break;
//                }

            }
//                DB::collection($collection)->create($data);
        }
    }

    abstract protected function getFixture();


    /**
     * @param $fixture
     * @return mixed
     */
    protected function setDates($fixture)
    {
        foreach ($fixture as $key => $value) {
            if (is_array($value)) {
                $fixture[$key] = $this->setDates($value);
            } else if (in_array($key, ['queued_at', 'sent_at', 'received_at', 'delivered_at', 'datetime'])) {
                $fixture[$key] = ($value instanceof \DateTime)
                    ? $value
                    : (new DateTime($value));
            }
        }
        return $fixture;
    }
}