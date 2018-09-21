<?php

namespace MirazMac\YouFetch;

use MirazMac\YouFetch\Exceptions\SignatureException;
use \Requests_Cookie_Jar;
use \Requests_Session;

/**
* YouTube Signature Decryption Library
*
* @author MirazMac <mirazmac@gmail.com>
* @version 0.1 Early Access
* @package MirazMac\YouFetch
*/
class Signature
{
    /**
     * The player ID for the video
     *
     * @var string
     */
    protected $playerID;

    /**
     * The player URI of the video
     *
     * @var string
     */
    protected $playerUri;

    /**
     * The player sts value of the video
     *
     * @var string
     */
    protected $playerSts;

    /**
     * The ciphered signature
     *
     * @var string
     */
    protected $cipheredSignature;

    /**
     * The parsed signature decryption key
     *
     * @var string
     */
    protected $decryptionKey;

    /**
     * Create a new instance
     *
     * @param string $cipheredSignature The ciphered signature
     * @param string $playerUri         The player URI of the video
     * @param string $playerSts         The player sts value of the video
     */
    public function __construct($cipheredSignature, $playerUri, $playerSts)
    {
        $playerID = $this->extractPlayerID($playerUri);
        if (!$playerID) {
            throw new InvalidArgumentException("Invalid player URI provided!");
        }

        $this->cipheredSignature = trim($cipheredSignature);
        $this->playerID = $playerID;
        $this->playerUri = $playerUri;
        $this->playerSts = $playerSts;

        Cache::setStoragePath(__DIR__ . '/Signatures');
    }

    /**
     * Decipher the signature
     *
     * @throws SignatureException If decryption key format is not valid
     * @return string Possibly the deciphered signature
     */
    public function decrypt()
    {
        $this->parseJavaScript();

        $signature = $this->cipheredSignature;
        $methods = explode(' ', $this->decryptionKey);

        // Very elementary Watson :/
        if (empty($methods)) {
            throw new SignatureException("Invalid decryption key format! The algorithm may have changed!");
        }

        foreach ($methods as $method) {
            $dict = $method;

            if ($method !== 'r') {
                $dict = mb_substr($method, 0, 1);
            }

            switch ($dict) {
                // !ti nmad ,gnirts eht esrever tsuJ
                case 'r':
                    $signature = strrev($signature);
                    break;
                // Just a lil' string splicing
                case 's':
                    $start = (int) mb_substr($method, 1);
                    $signature = mb_substr($signature, $start);
                    break;
                // We need to swap the words in this case
                case 'w':
                    $position = (int) mb_substr($method, 1);
                    $signature = $this->swap($signature, $position);
                    break;
            }
        }

        return $signature;
    }

    /**
     * Extract the player ID from the player URI
     *
     * @param  string $playerUri The player Js URI
     * @return string|boolean
     */
    protected function extractPlayerID($playerUri)
    {
        preg_match('#player-(.*?)/#s', $playerUri, $matches);

        if (empty($matches[1]) || !is_string($matches[1])) {
            return false;
        }

        return $matches[1];
    }

    /**
     * Parses the player JavaScript and extracts the decipher key
     *
     * @throws SignatureException If failed to fetch the player JS file
     * @return boolean
     */
    protected function parseJavaScript()
    {
        // Try fetching from the cache first
        $cachedData = Cache::get($this->playerID);
        if ($cachedData) {
            $this->decryptionKey = $cachedData;
            return true;
        }

        $javascript = Http::getSession()->get($this->playerUri)->body;
        if (empty($javascript)) {
            throw new SignatureException("Failed to fetch player javascript file!");
        }

        /**
         * Extract the signature from JavaScript Source
         *
         * Collected from various sources, that I can't recall
         */
        $parsedScript     =   $this->getBetween($javascript, 'a=a.split("");', ';return a.join("")');
        $parsedScript     =   str_replace('a,', '', $parsedScript);
        $parsedScript     =   str_replace("\n", '', $parsedScript);
        $parsedScript2    =   $this->getBetween($javascript, 'var ' . mb_substr($parsedScript, 0, 2).'={', '};');
        $parsedScript2    =   str_replace('a,b', 'a', $parsedScript2);
        $parsedScript     =   str_replace(mb_substr($parsedScript, 0, 2).'.', '', $parsedScript);
        $parsedScript     =   str_replace('(', '', $parsedScript);
        $parsedScript     =   str_replace(')', '', $parsedScript);
        $parsedScript_ex  =   explode(";", $parsedScript);
        $parsedScript2_ex =   explode("\n", $parsedScript2);

        $tempArr = [];
        for ($i = 0; $i < count($parsedScript2_ex); $i++) {
            $tmp = isset($parsedScript2_ex[$i]) ? explode(':', $parsedScript2_ex[$i]) : [];
            $n   = isset($tmp[0]) ? $tmp[0] : '';
            $m   = isset($tmp[1]) ? $tmp[1] : '';
            $tempArr[$n] = $m;
        }

        $xhtml = $this->playerSts . " ";
        for ($y = 0; $y < count($parsedScript_ex); $y++) {
            $a = isset($parsedScript_ex[$y]) ? mb_substr($parsedScript_ex[$y], 0, 2) : '';
            $b = isset($parsedScript_ex[$y]) ? mb_substr($parsedScript_ex[$y], 2, 4) : '';
            $xhtml .= $this->stringify($a, $b, $tempArr);
            $xhtml .= ' ';
        }

        $xhtml .= "';";
        $xhtml = str_replace(" ';", "", $xhtml);

        $this->decryptionKey = $xhtml;

        // Store the decryption key for future use
        Cache::save($this->playerID, $this->decryptionKey);
        return true;
    }

    /**
     * Convert the javascript function to simple parse-able string
     *
     * @param  string $value
     * @param  string $num
     * @param  string $source
     * @return string
     */
    protected function stringify($value, $num, $source)
    {
        $result = '';

        if (isset($source[$value]) && mb_strpos($source[$value], 'reverse')) {
            $result = 'r';
        } elseif (isset($source[$value]) && mb_strpos($source[$value], 'a.splice')) {
            $result = 's' . $num;
        } else {
            $result = 'w' . $num;
        }

        return $result;
    }

    /**
     * Swap a word in string to a certain position
     *
     * @param  string $a
     * @param  integer $b
     * @return string
     */
    protected function swap($a, $b)
    {
        // Stop wasting time Watson!
        if (!isset($a[0])) {
            return $a;
        }

        $c = $a[0];
        $a[0] = $a[$b % mb_strlen($a)];
        $a[$b] = $c;
        return $a;
    }

    /**
     * Get contents between to strings identifier
     *
     * @param  string $content The base string
     * @param  string $start   Start Identifier
     * @param  string $end     End Identifier
     * @return string
     */
    protected function getBetween($content, $start, $end)
    {
        $r = explode($start, $content);

        if (isset($r[1])) {
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }
}
