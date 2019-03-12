<?php

namespace VideoEditor;

class VideoEditor
{
    
    /**
     * @var string
     */
    protected $ffmpegPath;
    
    /**
     * @var boolean
     */
    protected $debug;
    
    /**
     * VideoEditor constructor.
     *
     * @param string $ffmpegPath
     */
    public function __construct($ffmpegPath)
    {
        $this->ffmpegPath = $ffmpegPath;
        $this->debug = false;
    }
    
    /**
     * @return Model\VideoInterface
     */
    public function createVideo($filePath = null)
    {
        $video = new \VideoEditor\Model\Video($this);
        
        if ($filePath) {
            $video->setFilePath($filePath);
            
        }
        
        
        return $video;
    }
    
    /**
     * @param $videos
     *
     * @return Model\VideoInterface
     */
    public function concatenateVideos($videos)
    {
        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(rand(0, 999999999)) . ".mp4";
        
        $videosFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(rand(0, 999999999)) . ".txt";
        
        $f = fopen($videosFilePath, "w");
        foreach ($videos as $video) {
            fwrite($f, sprintf("file '%s'\n", $video->getFilePath()));
        }
        fclose($f);
        
        $command = sprintf("%s %s -f concat -safe 0 -i %s -c copy \"%s\"",
            $this->ffmpegPath,
            $this->isDebug() ? "" : "-loglevel panic",
            $videosFilePath,
            $outputFile
        );
        
        exec($command);
        
        $outputVideo = $this->createVideo($outputFile);
        
        return $outputVideo;
    }
    
    /**
     * @param Model\VideoInterface $video
     * @param                      $imagePath
     * @param array                $options
     *
     * @return Model\VideoInterface
     */
    public function addVideoFrame(\VideoEditor\Model\VideoInterface $video, $imagePath, $options = [])
    {
        $options = array_merge([
            'duration' => 1,
        ], $options);
        
        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(rand(0, 999999999)) . ".mp4";
        
        $command = sprintf("%s %s -loop 1 -i \"%s\" -c:v libx264 -t %s -pix_fmt yuv420p -vf \"scale=trunc(iw/2)*2:trunc(ih/2)*2\" \"%s\"",
            $this->ffmpegPath,
            $this->isDebug() ? "" : "-loglevel panic",
            $imagePath,
            $options['duration'],
            $outputFile
        );
        
        exec($command);
        
        $lastPieceVideo = $this->createVideo($outputFile);
        
        if (file_exists($video->getFilePath()) && filesize($video->getFilePath())) {
            $contatenatedVideo = $this->concatenateVideos([
                $video,
                $lastPieceVideo,
            ]);
            $lastPieceVideo->delete();
            return $contatenatedVideo;
        } else {
            return $lastPieceVideo;
        }
    }
    
    /**
     * @param Model\VideoInterface $video
     * @param                      $text
     * @param array                $options
     *
     * @return Model\VideoInterface
     */
    public function addVideoText(\VideoEditor\Model\VideoInterface $video, $text, $options = [])
    {
        
        $options = array_merge([
            'start'     => "0",
            'duration'  => "1",
            'font_path' => "",
        ], $options);
        
        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(rand(0, 999999999)) . ".mp4";
        
        $command = sprintf("%s %s -i %s -vf \"drawtext=enable='between(t,%s,%s)':fontfile=%s: text='%s'\" -acodec copy %s",
            $this->ffmpegPath,
            $this->isDebug() ? "" : "-loglevel panic",
            $video->getFilePath(),
            $options['start'],
            $options['duration'],
            $options['font_path'],
            $text,
            $outputFile
        );
        
        exec($command);
        
        $video = $this->createVideo($outputFile);
        
        return $video;
    }
    
    /**
     * @return string
     */
    public function getFfmpegPath()
    {
        return $this->ffmpegPath;
    }
    
    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }
    
    /**
     * @param bool $debug
     *
     * @return VideoEditor
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }
    
    
}