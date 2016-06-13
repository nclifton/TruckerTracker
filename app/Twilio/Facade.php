<?php
/**
 *
 * @version 0.0.1: Facade.php 12/06/2016T02:24
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker\Twilio;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{

    public static function getFacadeAccessor(){
        return 'twilio';
    }
    
}