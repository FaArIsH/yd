<?php

namespace MirazMac\YouFetch;

use \ArrayAccess;
use MirazMac\YouFetch\Exceptions\YouTubeException;
use \Requests_Session;
use \Requests_Cookie_Jar;

/**
* YouTube Video Downloader Library
*
* @author MirazMac <mirazmac@gmail.com>
* @version 0.1 Early Access
* @package MirazMac\YouFetch
*/
class YouFetch
{
    /**
     * RegEx pattern to match player assets URI
     *
     * @var string
     */
    const PLAYER_PATTERN = '/"assets":.+?"js":\s*("[^"]+")/';
    /**
     * RegEx pattern to match player sts value
     *
     * @var string
     */
    const STS_PATTERN = '/"sts"\s*:\s*(\d+)/i';
    /**
     * RegEx pattern to match adaptive formats from webpage
     *
     * @var string
     */
    const ADAPTIVE_FMTS_PATTERN = '/\"adaptive\_fmts\"\:\s*\"([^\"]+)/i';
    /**
     * RegEx pattern to match URL Encoded streams from webpage
     *
     * @var string
     */
    const URL_ENCODED_FMTS_PATTERN = '/\"url\_encoded\_fmt\_stream\_map\"\:\s*\"([^\"]+)/i';
    /**
     * RegEx pattern to match stream format from the codec
     *
     * @var string
     */
    const TYPE_PATTERN = '/^([a-z0-9\-\_\/]+)(\;\s*codecs\="(?P<codecs>[^"]+)")?/i';
    /**
     * Endpoint to the webpage
     *
     * @var string
     */
    const WEB_PAGE_URI = 'https://youtube.com/watch?v=%s';
    /**
     * Endpoint to the embed page
     *
     * @var string
     */
    const EMBED_URI = 'https://youtube.com/embed/%s';

    /**
     * Current Video ID
     *
     * @var string
     */
    protected $videoID;
    /**
     * Player URI of the current video
     *
     * @var string
     */
    protected $playerUri;
    /**
     * Player ID of the current video
     *
     * @var string
     */
    protected $playerID;
    /**
     * Player sts value of the current video
     *
     * @var string
     */
    protected $playerSts;
    /**
     * Final parsed streams
     *
     * @var array
     */
    protected $parsedItems = [];

    /**
     * Internal Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Parsed Video Information
     *
     * @var array
     */
    protected $videoInfo = [];

    public function __construct($videoID, array $options = [])
    {
        $this->videoID = trim($videoID);
        $defaultOptions = [
            'parseWebPageForStreams' => false,
            'enableRedirector' => false,
            'appendAfterTitle' => ''
        ];
        $this->options = array_merge($defaultOptions, $options);

        $this->bootParser();
    }

    public function fetchAll()
    {
        return $this->parsedItems;
    }

    public function fetchAudioOnly()
    {
        $streams = $this->fetchAll();
        foreach ($streams as $itag => $stream) {
            if (!$stream->isAudioOnly()) {
                unset($streams[$itag]);
            }
        }
        return $streams;
    }

    public function fetchVideoOnly()
    {
        $streams = $this->fetchAll();
        foreach ($streams as $itag => $stream) {
            if (!$stream->isVideoOnly()) {
                unset($streams[$itag]);
            }
        }
        return $streams;
    }

    public function fetchFullVideos()
    {
        $streams = $this->fetchAll();
        foreach ($streams as $itag => $stream) {
            if (!$stream->hasBoth()) {
                unset($streams[$itag]);
            }
        }
        return $streams;
    }

    public function fetchVideoInfo()
    {
        return $this->videoInfo;
    }

    protected function bootParser()
    {
        // Fetch the player data first
        $this->fetchPlayerData();

        // Holds media links as array in future
        $mediaLinks = [];
        $rawMediaLinks = null;
        $videoDetails = $this->fetchMediaFromEndPoint();

        if (isset($videoDetails['adaptive_fmts'])) {
            $rawMediaLinks .= $videoDetails['adaptive_fmts'];
        }

        if (isset($videoDetails['url_encoded_fmt_stream_map'])) {
            $rawMediaLinks .= ",{$videoDetails['url_encoded_fmt_stream_map']}";
        }

        // If we don't have any streams OR Force web page parsing is enabled
        // We'd try to grab the stream links from webpage
        if (empty($rawMediaLinks) || $this->options['parseWebPageForStreams']) {
            $rawMediaLinks = $this->fetchStreamsFromWebPage();
        }

        if (!is_string($rawMediaLinks) || mb_strpos($rawMediaLinks, ',') === false) {
            throw new YouTubeException("No media streams found for the video.");
        }

        // URL friendly file name
        $downloadAs = $this->sanitizeFileName(
            "{$videoDetails['title']} {$this->options['appendAfterTitle']}"
        );

        // Load itags
        $iTags = Itags::load();
        // Load default media format
        $defaultMedia = Itags::getDefaultMedia();
        // Final output init
        $finalOutput = [];

        // Arraify(!) the data
        foreach (explode(',', $rawMediaLinks) as $link) {
            parse_str($link, $link);
            $mediaLinks[] = $link;
        }

        // Lets loop watson!
        foreach ($mediaLinks as $mlink) {
            // You don't have an itag? OR
            // You have an itag thats actually represnts a live video? OR
            // You don't have the stream URL!
            // hehehe, fuck off then -_-
            if (!isset($mlink['itag']) || $mlink['itag'] === '_rtmp' || !isset($mlink['url'])) {
                continue;
            }

            // Adjust URL Queries
            parse_str(parse_url($mlink['url'], PHP_URL_QUERY), $mediaUrlQuery);
            // Update with URL Friendly file name we created earlier
            $mediaUrlQuery['title'] = $downloadAs;
            // Phew, nobody wants to die young
            $mediaUrlQuery['keepalive'] = 'yes';
            // Useless, probably, still trying is worth it :|
            $mediaUrlQuery['ratebypass'] = 'yes';

            // Append signature if exists
            if (isset($mlink['sig'])) {
                $mediaUrlQuery['signature'] = $mlink['sig'];
            }

            // Decipher the ciphered signature if present
            if (isset($mlink['s'])) {
                $signature = new Signature($mlink['s'], $this->playerUri, $this->playerSts);
                $mediaUrlQuery['signature'] = $signature->decrypt();
            }

            // Update media link with adjusted parameters
            if (is_array($ex = explode('?', $mlink['url']))) {
                $mlink['url'] = "{$ex[0]}?" . http_build_query($mediaUrlQuery);
            }

            // Without any signature parameter, the link is pretty much useless
            if (mb_strpos($mlink['url'], '&signature=') === false) {
                continue;
            }

            if ($this->options['enableRedirector']) {
                $mlink['url'] = preg_replace(
                    '/^(.*)\.googlevideo\.com/',
                    'https://redirector.googlevideo.com',
                    $mlink['url']
                );
            }

            $mediaLink = $defaultMedia;
            // Update itag value
            $mediaLink['itag'] = intval($mlink['itag']);

            // Update media link
            $mediaLink['link'] = $mlink['url'];

             // Detect media link size
            if (isset($mlink['clen'])) {
                $mediaLink['size'] = $this->getNumber($mlink['clen']);
            }

            // Get details of media link with itag
            // Video Info init
            $iTagV = [];

            // Check for ITag Data
            if (isset($iTags[$mlink['itag']])) {
                $iTagInf = $iTags[$mlink['itag']];
                // Update media extension
                $mediaLink['extension'] = $iTagInf['extension'];
                // Update iTag video details
                if (isset($iTagInf['video'])) {
                    $iTagV = $iTagInf['video'];
                }
                // Check for is DASH media
                if (isset($iTagInf['dash']) && in_array($iTagInf['dash'], ['video', 'audio'])) {
                    // Detect media type
                    $mediaLink['type'] = $iTagInf['dash'];
                    // DASH media
                    $mediaLink['dash'] = true;

                    // Process Dash media
                    switch ($iTagInf['dash']) {
                        case 'video':
                            // Audio stream is not availabe
                            $mediaLink['audio'] = false;
                            break;
                        case 'audio':
                            // Video stream is not availabe
                            $mediaLink['video'] = false;
                            // Audio bitrate & quality
                            $bitrate = null;
                            if (isset($mlink['bitrate'])) {
                                $bitrate = $this->getNumber($mlink['bitrate']);
                            } elseif (isset($iTagInf['audio']) && isset($iTagInf['audio']['bitrate'])) {
                                $bitrate = $iTagInf['audio']['bitrate'];
                            }

                            // Update bitrate
                            if ($bitrate) {
                                $mediaLink['audio']['bitrate'] = $this->getNumber($bitrate);
                            }
                            // Audio frequency
                            if (isset($iTagInf['audio']) && isset($iTagInf['audio']['frequency'])) {
                                // Frequency
                                $mediaLink['audio']['frequency'] = $this->getNumber($iTagInf['audio']['frequency']);
                            }
                            break;
                    }
                    // Wooh! Done with processing dash medias
                } else {
                    // So, not a dash media right?
                    $mediaLink['type'] = 'video';
                }
            }
            /** Done with checking itags **/

            // Now lets Update media video stream details
            if ($mediaLink['video'] !== false) {
                // Check for is 3D video
                if (isset($iTagV['3d']) && $iTagV['3d']) {
                    $mediaLink['video']['3d'] = true;
                }
                // Width x Height
                if (isset($mlink['size'])) {
                    list($width, $height) = explode('x', $mlink['size']);
                    $mediaLink['video']['width'] = intval($width);
                    $mediaLink['video']['height'] = intval($height);
                } elseif (isset($iTagV['height'])) {
                    // Get dimensions from iTag info
                    $mediaLink['video']['height'] = $iTagV['height'];
                    // Get width of video
                    if (isset($iTagV['width'])) {
                        $mediaLink['video']['width'] = $iTagV['width'];
                    } else {
                        $mediaLink['video']['width'] = ceil(($iTagV['height'] / 9) * 16);
                    }

                    // Video bitrate
                    $vBitrate = null;
                    if (isset($mlink['bitrate'])) {
                        $vBitrate = $this->getNumber($mlink['bitrate']);
                    } elseif (isset($iTagV['bitrate'])) {
                        $vBitrate = $iTagV['bitrate'];
                    }

                    if ($vBitrate) {
                        $mediaLink['video']['bitrate'] = $this->getNumber($vBitrate);
                    }

                    // Video FrameRate
                    $framerate = null;
                    if (isset($mlink['fps'])) {
                        $framerate = $this->getNumber($mlink['fps']);
                    } elseif (isset($iTagV['framerate'])) {
                        $framerate = $iTagV['framerate'];
                    }

                    $mediaLink['video']['framerate'] = $framerate;
                }
            }
            /** Done with processing video stream details **/

            // Time to deal with media extension and codecs
            if (isset($mlink['type']) && is_string($mlink['type']) &&
                preg_match(static::TYPE_PATTERN, $mlink['type'], $matches)) {
                // Check media type
                if ($mediaLink['type'] === null) {
                    $mediaLink['type'] = mb_stripos($matches['type'], 'audio') !== false ? 'audio' : 'video';
                }

                // Check media file extension
                if ($mediaLink['extension'] === null) {
                    if (mb_stripos($matches['type'], 'mp4')) {
                        $mediaLink['extension'] = $mediaLink['type'] == 'video' ? 'mp4' : 'm4a';
                    } elseif (mb_stripos($matches['type'], 'webm')) {
                        $mediaLink['extension'] = 'webm';
                    } elseif (mb_stripos($matches['type'], 'flv')) {
                        $mediaLink['extension'] = 'flv';
                    } elseif (mb_stripos($matches['type'], '3gp')) {
                        $mediaLink['extension'] = '3gp';
                    }
                }

                // Update codec details
                if (isset($matches['codecs'])) {
                    $codecs = explode(',', $matches['codecs']);
                    // Check for is DASH media
                    if (!$mediaLink['dash'] && count($codecs) == 1) {
                        $mediaLink['dash'] = true;
                    }
                    // Media stream codecs
                    if ($mediaLink['type'] == 'video') {
                        // Update video codec
                        if (is_array($mediaLink['video']) && isset($codecs[0])) {
                            $vCodec = explode('.', trim($codecs[0]));
                            $mediaLink['video']['codec'] = $vCodec[0];
                        }
                        // Update audio codec
                        if (is_array($mediaLink['audio']) && isset($codecs[1])) {
                            $aCodec = explode('.', trim($codecs[1]));
                            $mediaLink['audio']['codec'] = $aCodec[0];
                        }
                    } else {
                        // Update audio codec
                        if (is_array($mediaLink['audio']) && isset($codecs[0])) {
                            $vCodec = explode('.', trim($codecs[0]));
                            $mediaLink['audio']['codec'] = $vCodec[0];
                        }
                    }
                }
            }

            /** Done dealing with media codecs **/

            // Fuck! Finally!
            $finalOutput[$mediaLink['itag']] = $mediaLink;
        }


        // Oops! Not yet? You kiddin' me? -_-
        foreach ($finalOutput as $itag => $data) {
            // Remove media block if extension not available
            if ($data['extension'] === null) {
                unset($finalOutput[$itag]);
                continue;
            }

            // Add sizes if not added previously
            if (!$data['size']) {
                $mediaSize = 0;
                $mediaSizeObj = Http::getSession()->head($data['link']);
                if (isset($mediaSizeObj->headers['content-length'])) {
                    $mediaSize = $mediaSizeObj->headers['content-length'];
                }
                $finalOutput[$itag]['size'] = $mediaSize;
            }
        }

        foreach ($finalOutput as $itag => $data) {
            $finalOutput[$itag] = new StreamElement($data);
        }


        $this->parsedItems = array_reverse($finalOutput);

        // We dig for this?
        return true;
    }

    protected function fetchMediaFromEndPoint()
    {
        // We assume it's an Unknown error, So negative minded!
        $videoDetails = 'Unknown';

        // Different variations for the youtube getinfo page
        $variations = ['embedded', 'detailpage', 'vevo'];

        // Loop through each of 'em untill we find our soulmate <3
        foreach ($variations as $elKey) {
            $query = http_build_query([
                'c'         => 'web',
                'el'        => $elKey,
                'hl'        => 'en_US',
                'cver'      => 'html5',
                'eurl'      => "https://youtube.googleapis.com/v/{$this->videoID}",
                'html5'     => '1',
                'iframe'    => '1',
                'authuser'  => '1',
                'sts'       => $this->playerSts,
                'video_id'  => $this->videoID
            ]);
            $videoData = Http::getSession()->get("https://www.youtube.com/get_video_info?{$query}")->body;

            if (!is_string($videoData)) {
                continue;
            }
            parse_str($videoData, $videoData);
            if (is_array($videoData) && isset($videoData['token'])) {
                $videoDetails = $videoData;
                break;
            } elseif (is_array($videoData) && isset($videoData['status']) && $videoData['status'] === 'fail') {
                $videoDetails = isset($videoData['reason']) ? $videoData['reason'] : 'Unknown';
            }
        }

        // I think its not working anymore, we need to break up :|
        if (!isset($videoDetails['title'])) {
            throw new YouTubeException("Failed to fetch YouTube video details. Reason: {$videoDetails}");
        }

        // This one is a rental video Watson! -_-
        if (isset($videoDetails['ypc_video_rental_bar_text']) && !isset($videoDetails['author'])) {
            throw new YouTubeException("Rental videos are not supported.");
        }

        $defaultDetails = Itags::getDefaultInfo();

        foreach ($defaultDetails as $key => $value) {
            if (isset($videoDetails[$key])) {
                $defaultDetails[$key] = $videoDetails[$key];
            }
        }

        $this->videoInfo = new VideoElement($defaultDetails);

        return $videoDetails;
    }

    protected function fetchPlayerData()
    {
        $endPointUri = sprintf(static::EMBED_URI, $this->videoID);
        $response = Http::getSession()->get($endPointUri);

        // Here we go with the source code
        $source = $response->body;

        // Extract the player information
        preg_match(static::PLAYER_PATTERN, $source, $matches);
        if (empty($matches[1]) || !is_string($playerPath = json_decode($matches[1]))) {
            throw new YouTubeException("Failed to find player information for the video.");
        }

        preg_match('%player-(.*?)/%', $playerPath, $matches);
        if (empty($matches[1]) || !is_string($matches[1])) {
            throw new YouTubeException("Failed to find player ID from the embed page!");
        }

        $playerID  = $matches[1];
        $playerUri = null;
        $protocol  = mb_substr($playerPath, 0, 2);

        if ($protocol === '//') {
            // If the path is double slashed then we just need to append the protocol name
            $playerUri .= "https:{$playerPath}";
        } else {
            // Otherwise it must be a native path
            $playerUri .= "https://youtube.com{$playerPath}";
        }

        // Look for sts value
        preg_match(static::STS_PATTERN, $source, $matches);
        if (empty($matches[1])) {
            throw new YouTubeException("Failed to find player sts value for the video!");
        }

        $this->playerUri = $playerUri;
        $this->playerID  = $playerID;
        $this->playerSts = (int) $matches[1];
        return true;
    }

    protected function fetchStreamsFromWebPage()
    {
        $webPageUri = sprintf(static::WEB_PAGE_URI, $this->videoID);
        $webPageRequest = Http::getSession()->get($webPageUri);

        if (!$webPageRequest->success) {
            return false;
        }

        $webPage = $webPageRequest->body;
        $rawMediaLinks = null;

        if (preg_match(static::ADAPTIVE_FMTS_PATTERN, $webPage, $matches) &&
            is_string($jsonOut = json_decode("\"{$matches[1]}\""))) {
            $rawMediaLinks .= $jsonOut;
        }

        if (preg_match(static::URL_ENCODED_FMTS_PATTERN, $webPage, $matches) &&
            mb_strpos($matches[1], 's=') === false &&
            is_string($jsonOut = json_decode("\"{$matches[1]}\""))) {
            $rawMediaLinks .= ',' . $jsonOut;
        }

        if (!is_string($rawMediaLinks) || mb_strpos($rawMediaLinks, ',') === false) {
            throw new YouTubeException("Failed to find streams from the webpage.");
        }

        return $rawMediaLinks;
    }

    protected function getNumber($data)
    {
        return floatval(number_format(floatval($data), 0, '.', ''));
    }

    protected function sanitizeFileName($fileName)
    {
        $specialChars = "\x00\x21\x22\x24\x25\x2a\x2f\x3a\x3c\x3e\x3f\x5c\x7c";
        $fileName = str_replace(str_split($specialChars), '_', $fileName);
        return $fileName;
    }
}
