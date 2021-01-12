<?php

declare(strict_types=1);

namespace App;

use DOMXPath;

class SiteMapGrabber implements IGrabber
{
    const SITEMAP_URL = 'https://www.czc.cz/sitemap.xml';

    /**
     * @var DOMXPath|null
     */
    private $xPath;


    /**
     * Get price for given code
     *
     * @param  string $productId
     * @return float|null
     */
    public function getPrice(string $productId): float
    {
        $price = 0.0;
        $pages = $this->getShopPages();
        foreach ($pages as $page) {
            if ($this->checkPageForCode($page, $productId) === true) {
                $this->xPath = ($this->xPath ?? $this->getDom($page));
                $price       = $this->xPath->query(
                    "//div[contains(@class,'total-price')]//span[contains(@class, 'price-vatin')]"
                );
                if ($price !== false && $this->sanitizePrice($price) !== 0.0) {
                    $price = $this->sanitizePrice($price);
                    return $price;
                }
            }
        }

        return $price;

    }//end getPrice()


    /**
     * Returns name of product
     *
     * @return string
     */
    public function getProductName(): string
    {
        $productName = 'Title not found';
        $productNode = $this->xPath->document->getElementsByTagName('h1');
        if ($productNode !== false) {
            $productName = $this->sanitizeWhiteSpaces($productNode->item(0)->nodeValue);
        }

        return $productName;
    }//end getProductName()


    /**
     * Gets rating of product
     *
     * @return string
     */
    public function getRating(): string
    {
        $rating     = 'Rating not found';
        $ratingNode = $this->xPath->query("//span[contains(@class,'rating__label')]");
        if ($ratingNode !== false) {
            $rating = $ratingNode->item(0)->nodeValue;
        }

        return $rating;

    }//end getRating()


    /**
     * Fetches page content
     *
     * @param  string $page
     * @return string
     */
    private function getContent(string $page): string
    {
        header('Content-Type:application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $page);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;

    }//end getContent()


    /**
     * Removes whitespaces and Kč units
     *
     * @param  \DOMNodeList $priceNode
     * @return float
     */
    private function sanitizePrice(\DOMNodeList $priceNode): float
    {
        $price = (string) $priceNode->item(0)->nodeValue;
        $price = preg_replace('/Kč/', '', $price);
        $price = $this->sanitizeSpecialWhiteSpaces($price);
        if (is_numeric($price) === true) {
            $price = (float) $price;
        } else {
            $price = 0.0;
        }

        return $price;

    }//end sanitizePrice()


    /**
     * Returns array of shop pages
     *
     * @return array
     */
    private function getShopPages(): array
    {
        $pages = [];
        $file  = simplexml_load_file(self::SITEMAP_URL);
        foreach ($file->sitemap as $urlList) {
            $siteMapPart = file_get_contents((string) $urlList->loc);
            if ($siteMapPart !== false) {
                $sitePagesPart = simplexml_load_string($siteMapPart);
                foreach ($sitePagesPart->url as $url) {
                    $pages[] = (string) $url->loc;
                }
            }
        }

        return $pages;

    }//end getShopPages()


    /**
     * Checks if code is present on link or page
     *
     * @param  string $page
     * @param  string $productId
     * @return boolean
     */
    private function checkPageForCode(string $page, string $productId): bool
    {
        $result      = false;
        $this->xPath = null;
        if (strpos($page, strtolower($productId)) === false) {
            $xpath        = $this->getDom($page);
            $internalCode = $xpath->query(
                "//div[contains(@class,'pd-next-in-category__item pd-next-in-category__item--our-code')]"
            );
            if ($internalCode !== false) {
                $foreignCodeNode = $internalCode->item(0)->previousSibling->previousSibling;
                if ($foreignCodeNode !== null) {
                    $result      = (bool) strpos($foreignCodeNode->textContent, $productId);
                    $this->xPath = $xpath;
                }
            }
        } else {
            $result = true;
        }

        return $result;

    }//end checkPageForCode()


    /**
     * Gets DOM from link to a page
     *
     * @param  string $page
     * @return DOMXPath|null
     */
    private function getDom(string $page): ?DOMXPath
    {
        $xpath       = null;
        $dom         = new \DOMDocument();
        $pageContent = $this->getContent($page);
        try {
            libxml_use_internal_errors(true);
            $dom->loadHTML(html_entity_decode($pageContent));
            libxml_clear_errors();
            $xpath = new DOMXpath($dom);
        } catch (\Exception $e) {
            fwrite(STDOUT, 'Cannot load HTML content');
        }

        return $xpath;

    }//end getDom()


    /**
     * Removes specific whitespaces
     *
     * @param  string $text
     * @return string
     */
    private function sanitizeSpecialWhiteSpaces(string $text): string
    {
        $result = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
        return ($result ?? $text);

    }//end sanitizeSpecialWhiteSpaces()


    /**
     * Removes double whitespaces
     *
     * @param  string $text
     * @return string
     */
    private function sanitizeWhiteSpaces(string $text): string
    {
        $result  = preg_replace('/[\x00-\x1F\xFF]/', '', $text);
        $result2 = preg_replace('/\s{2,}/', '', $result);
        return ($result2 ?? $text);

    }//end sanitizeWhiteSpaces()


}//end class
