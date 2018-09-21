<?php

namespace MirazMac\YouFetch;

use \ArrayAccess;

/**
* StreamElement
*
* @author MirazMac <mirazmac@gmail.com>
* @version 0.1 Early Access
* @package MirazMac\YouFetch
*/
class StreamElement implements ArrayAccess
{
    protected $stream = [];

    public function __construct(array $stream)
    {
        $this->stream = $stream;
    }

    public function isAudioOnly()
    {
        return $this['video'] === false;
    }

    public function isVideoOnly()
    {
        return $this['audio'] === false;
    }

    public function is3D()
    {
        return $this['video']['3d'] === true;
    }

    public function hasBoth()
    {
        return $this['audio'] && $this['video'];
    }

    public function getLink()
    {
        return $this['link'];
    }

    public function getExtension()
    {
        return $this->stream['extension'];
    }

    public function getHeight()
    {
        if (!$this['video']['height']) {
            return 'N/A';
        }

        return $this['video']['height'];
    }

    public function getWidth()
    {
        if (!$this['video']['width']) {
            return 'N/A';
        }

        return $this['video']['width'];
    }

    public function getResulation()
    {
        return $this->getWidth() . ' x ' . $this->getHeight();
    }

    public function getAudioBitrate()
    {
        if (!$this['audio']['bitrate']) {
            return 'N/A';
        }

        return $this->formatBitrate($this['audio']['bitrate']);
    }

    public function getSize()
    {
        return $this->formatBytes($this['size']);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->stream);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->stream[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->stream[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->stream[$offset]);
        }
    }

    protected function formatBitrate($bytes)
    {
        $kb = (int)$this->formatBytes($bytes);
        return $kb . 'KBPS';
    }

    protected function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes === 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
