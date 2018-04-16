<?php
/**
 * Created by PhpStorm.
 * User: michelecapicchioni
 * Date: 16/04/18
 * Time: 18:16
 */

namespace VideoEditor\Model;


interface VideoInterface
{
    /**
     * @return float
     */
    public function getDuration();

    /**
     * @return float
     */
    public function getFormattedDuration();

    /**
     * @return string
     */
    public function getFilePath();

    /**
     * @param $seconds
     * @return string
     */
    public function getFrame($seconds);

    /**
     * @param $filePath
     * @return $this
     */
    public function setFilePath($filePath);

    /**
     * @return void
     */
    public function delete();
}