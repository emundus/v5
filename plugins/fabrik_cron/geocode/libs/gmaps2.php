<?php

/**
 *
 * Borrowed from ...
 * http://www.phpclasses.org/browse/file/38470.html
 *
 */

Class GeoCode
{
    private $address = "";
    private $url = "";

    public function getLatLng($addr,$returnType="array")
    {
        $this->address = $addr;

        $this-> makeUrl();

        $final = $this->parseGeoData();

        if($returnType == "json")
        {
            return $this->makeJson($final);
        }
        else
        {
            return $final;
        }
    }


    private function makeJson($data)
    {
        return json_encode($data);
    }

    private function makeUrl()
    {
        $this->address = str_replace(" ", "+",$this->address);
        $this->url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$this->address."&sensor=false";
    }

    private function parseGeoData()
    {
        $data = file_get_contents($this->url);
        $result = json_decode($data);

        if($result->status == "OK")
        {
            if($result->results[0]->geometry->location)
            {
                $addressFromGoogle = $result->results[0]->formatted_address;
                $lat = $result->results[0]->geometry->location->lat;
                $lng = $result->results[0]->geometry->location->lng;

                $resultFromGl['status'] = $result->status;
                $resultFromGl['address'] = $addressFromGoogle;
                $resultFromGl['lat'] = $lat;
                $resultFromGl['lng'] = $lng;
            }
            else
            {
                $resultFromGl['status'] = "Address not found";
            }
        }
        else
        {
            $resultFromGl['status'] = $result->status;
        }
        return $resultFromGl;
    }

}

?>