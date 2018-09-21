<?php

namespace MirazMac\YouFetch;

use \ArrayAccess;

/**
* VideoElement
*
* @author MirazMac <mirazmac@gmail.com>
* @version 0.1 Early Access
* @package MirazMac\YouFetch
*/
class VideoElement implements ArrayAccess
{
    protected $videoInfo = [];

    public function __construct(array $videoInfo)
    {
        $this->videoInfo = $videoInfo;
    }

    public function getTitle()
    {
        return $this['title'];
    }

    public function getAuthor()
    {
        return $this['author'];
    }

    public function getChannelID()
    {
        return $this['uid'];
    }

    public function getID()
    {
        return $this['video_id'];
    }

    public function getThumbnail($size = 'mqdefault')
    {
        $size = filter_var($size, FILTER_SANITIZE_STRING);
        $id = $this->getID();
        return "https://i.ytimg.com/vi/{$id}/{$size}.jpg";
    }

    public function getLength()
    {
        return $this['length_seconds'];
    }

    public function getViews()
    {
        return $this['view_count'];
    }

    public function getRating()
    {
        return $this['avg_rating'];
    }

    public function getKeywords()
    {
        return $this['keywords'];
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->videoInfo);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->videoInfo[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->videoInfo[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->videoInfo[$offset]);
        }
    }
}
