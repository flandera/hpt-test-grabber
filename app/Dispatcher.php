<?php

declare(strict_types=1);

namespace App;

class Dispatcher
{
    const FILE_NAME           = 'vstup.txt';
    const DECIMALS_NUMBER     = 2;
    const DECIMALS_SEPARATOR  = '.';
    const THOUSANDS_SEPARATOR = '';

    /**
     * @var IGrabber
     */
    private $grabber;

    /**
     * @var IOutput
     */
    private $output;


    /**
     * @param IGrabber $grabber
     * @param IOutput  $output
     */
    public function __construct(IGrabber $grabber, IOutput $output)
    {
        $this->grabber = $grabber;
        $this->output  = $output;
    }//end __construct()


    /**
     * Runs the Grabber
     *
     * @return void
     */
    public function run(): void
    {
        $results = [];
        $file    = fopen(self::FILE_NAME, 'r');
        if ($file !== false) {
            while (($line = fgets($file)) !== false) {
                $code  = $this->getCode($line);
                $price = $this->grabber->getPrice($code);
                if ($price !== 0.0) {
                    $productName = $this->grabber->getProductName();
                    $rating      = $this->grabber->getRating();
                    $results[]   = [
                        $code => [
                            'price'       => number_format(
                                $price,
                                self::DECIMALS_NUMBER,
                                self::DECIMALS_SEPARATOR,
                                self::THOUSANDS_SEPARATOR
                            ),
                            'productName' => $productName,
                            'rating'      => $rating,
                        ],
                    ];
                } else {
                    $results[] = [
                        $code => ['price' => 'Product with this code was not found'],
                    ];
                }
            }//end while

            fclose($file);
        } else {
            $this->output->setResults(['ERROR' => 'Cannot read the input file!']);
        }//end if

        $this->output->setResults($results);
        fwrite(STDOUT, $this->output->getJson());
    }//end run()


    /**
     * Gets code from line
     *
     * @param string $line
     * @return string
     */
    private function getCode(string $line): string
    {
        $line = str_replace(["\n", "\r"], '', $line);
        return $line;
    }//end getCode()
}//end class
