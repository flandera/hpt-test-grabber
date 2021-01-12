<?php

declare(strict_types=1);

namespace App;

class Output implements IOutput
{

    /**
     * For setting encoding of chars for console or other purposes
     *
     * @var bool
     */
    private $outputToConsole = true;

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
        if ($this->outputToConsole === true) {
            $encodedResults = $this->encodeToAsciiChars($this->results);
        } else {
            $encodedResults = $this->results;
        }

        $string = json_encode($encodedResults);
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


    /**
     * Converts chars to ascii
     *
     * @param array $results
     */
    private function encodeToAsciiChars(array $results): array
    {
        $currentCharset = 'ASCII//TRANSLIT';
        array_walk_recursive(
            $results,
            function (&$value) use ($currentCharset) {
                $value = iconv('UTF-8//TRANSLIT', $currentCharset, $value);
            }
        );

        return $results;

    }//end encodeToAsciiChars()

    /**
     * @param bool $outputToConsole
     * @return void
     */
    public function setOutputToConsole(bool $outputToConsole): void
    {
        $this->outputToConsole = $outputToConsole;
    }


}//end class
