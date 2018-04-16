<?php

namespace VideoEditor\Model;

/**
 * Created by PhpStorm.
 * User: michelecapicchioni
 * Date: 13/04/18
 * Time: 23:15
 */
class Video implements VideoInterface
{

    /**
     * @var \VideoEditor\VideoEditor
     */
    protected $videoEditor;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * Video constructor.
     * @param \VideoEditor\VideoEditor $videoEditor
     */
    public function __construct(\VideoEditor\VideoEditor $videoEditor)
    {
        $this->filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(rand(0, 999999999)) . ".mp4";
        file_put_contents($this->filePath, "");
        $this->videoEditor = $videoEditor;
    }

    /**
     * @inheritDoc
     */
    public function getDuration()
    {
        $command = sprintf("%s -i \"%s\" 2>&1 | grep \"Duration\"", $this->videoEditor->getFfmpegPath(), $this->filePath);

        exec($command, $output);

        $output = implode("", $output);
        if (preg_match("/Duration:(.*?),/is", $output, $duration)) {
            $duration = $duration[1];
            $duration = trim($duration);
            $durationFields = explode(":", $duration);
            $duration = 0;
            $moltiplicator = 1;
            while (count($durationFields)) {
                $duration += floatval(array_pop($durationFields)) * $moltiplicator;
                $moltiplicator *= 60;
            }

            return $duration;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getFormattedDuration()
    {
        $seconds = $this->getDuration();
        $time = sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);
        return $time;
    }

    /**
     * @param $seconds
     * @return string
     */
    public function getFrame($seconds)
    {
        $time = sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);

        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(rand(0, 99999999999)) . ".jpg";

        $command = sprintf("%s %s -i \"%s\" -c:v libx264 -pix_fmt yuvj422p -ss %s -vframes 1 \"%s\"",
            $this->videoEditor->getFfmpegPath(),
            $this->videoEditor->isDebug() ? "" : "-loglevel panic",
            $this->filePath,
            $time,
            $outputFile
        );

        exec($command);

        return $outputFile;
    }

    /**
     * @inheritDoc
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        unlink($this->filePath);
    }

    /**
     * @inheritDoc
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }


}