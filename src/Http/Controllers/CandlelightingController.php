<?php

namespace Lasallecms\Candlelighting\Http\Controllers;

    /**
     *
     * Candle lighting package for the LaSalle Content Management System, based on the Laravel 5 Framework
     * Copyright (C) 2015 - 2016  The South LaSalle Trading Corporation
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     *
     *
     * @package    Candle lighting package for the LaSalle Content Management System
     * @link       http://LaSalleCMS.com
     * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
     * @license    http://www.gnu.org/licenses/gpl-3.0.html
     * @author     The South LaSalle Trading Corporation
     * @email      info@southlasalle.com
     *
     */

// Laravel classes
use Lasallecms\Candlelighting\Http\Controllers\Controller;
use Lasallecms\Helpers\HTML\HTMLHelper;

// Laravel facades
use Illuminate\Support\Facades\Config;

// Third party classes
use GuzzleHttp;

/**
 * Class CandlelightingController
 * @package Lasallecms\Candlelighting\Http\Controllers
 */
class CandlelightingController extends Controller
{
    /**
     * @var json encoded response from HebCal.com
     */
    protected $hebcalJSON;

    /**
     * @var json decoded response body from HebCal.com
     */
    protected $body;

    /**
     * @var Shabbat info from the HebCal.com response
     */
    protected $shabbatInfo = [];


    /**
     * CandlelightingController constructor.
     */
    public function __construct() {

        // execute base controller's construct method first in order to run the middleware
        parent::__construct();

        // get the JSON encoded response from HebCal.com
        $this->hebcalJSON = $this->callHebCalAPI();

        // get the status code
        $this->getStatusCode();

        // get the JSON decoded response from HebCal.com, for the response's body only
        $this->body = $this->getJSONdecodedBody();

        // create the Shabbat info array
        $this->getShabbatInfo();
    }


    /**
     * Display a summary page in the admin back-end
     *
     * @return response
     */
    public function displayAdmin() {

        echo "<pre>";
        print_r($this->shabbatInfo);
        return;

    }

    /**
     * Brief inline frontend display
     *
     * @return response
     */
    public function displayFrontend1() {

        return view('candlelighting::displayfrontend1', [
            'shabbatInfo' => $this->shabbatInfo,
            'HTMLHelper'  => HTMLHelper::class,
        ]);
    }

    /**
     * Get the JSON encoded data from HebCal
     *
     * @return mixed
     */
    private function callHebCalAPI() {
        // synchronous request
        $client = new GuzzleHttp\Client();

        $havdalah = Config::get('lasallecmscandlelighting.candlelighting_havdalah_minutes_after_sundown');

        return $client->get('http://www.hebcal.com/shabbat/?cfg=json&geo=city&city=Toronto&m='.$havdalah,[]);
    }

    /**
     * Get the HebCal's response body, converted to an array
     */
    private function getJSONdecodedBody() {
        $body = $this->getBody();
        return json_decode($body, true);
    }


    /**
     * What is the status code?
     *
     * @return mixed
     */
    private function getStatusCode() {
        $this->shabbatInfo['status_code'] = $this->hebcalJSON->getStatusCode();
        return $this->shabbatInfo['status_code'];
    }

    /**
     * @param  string   $type   What type of header?
     * @return mixed
     */
    private function getHeaderLine($type='content-type') {
        return $this->hebcalJSON->getHeaderLine($type);
    }

    /**
     * What is the body of the API call
     *
     * @return mixed
     */
    private function getBody() {
        return $this->hebcalJSON->getBody();
    }


    /**
     * Grab the Shabbat information from the body
     *
     * @return array
     */
    private function getShabbatInfo() {

        // The HebCal response body groups Shabbat info. Let's find these groups (sub-arrays), and populate the
        // Shabbat info array with info from these groups.

        for ($x = 0; $x <= count($this->body['items']); $x++) {


            if (isset($this->body['items'][$x]['category'])) {

                if ($this->body['items'][$x]['category'] == "candles") {

                    $title = $this->body['items'][$x]['title'];

                    $this->shabbatInfo['candle_lighting_title'] = $title;
                    $this->shabbatInfo['date']                  = date('M j' ,strtotime(substr($this->body['items'][$x]['date'],0,10)) );
                    $this->shabbatInfo['candle_lighting_time']  = substr($title, 16, strlen($title));
                }

                if ($this->body['items'][$x]['category'] == "parashat") {
                    $this->shabbatInfo['parashat'] = $this->body['items'][$x]['title'];
                    $this->shabbatInfo['parashat_link'] = $this->body['items'][$x]['link'];
                }

                if ($this->body['items'][$x]['category'] == "havdalah") {
                    $this->shabbatInfo['havdalah_candle_lighting_time'] = substr($this->body['items'][$x]['title'],18,7);
                }

            }
        }
    }
}




