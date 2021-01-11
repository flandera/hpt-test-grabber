<?php

declare(strict_types=1);

namespace App;

class Output implements IOutput
{

    /**
     * @var array
     */
    private $results;


    /**
     * Get encoded content
     *
     * @return string
     */
    public function getJson(): string
    {
        $string = json_encode($this->results);
        return $string;
    }//end getJson()


    /**
     * Sets array of results to encode
     *
     * @param array $results
     */
    public function setResults(array $results): void
    {
        $this->results = $results;
    }//end setResults()
}//end class
