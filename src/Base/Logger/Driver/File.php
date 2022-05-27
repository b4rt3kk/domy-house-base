<?php

namespace Base\Logger\Driver;

class File extends \Base\Logger\Driver\AbstractDriver
{
    protected $logsDir;
    
    protected $fileName;
    
    protected $maxFileSize = 1024 * 1000;
    
    public function getLogsDir()
    {
        return $this->logsDir;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    public function setLogsDir($logsDir): void
    {
        $this->logsDir = $logsDir;
    }

    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
    }

    public function setMaxFileSize($maxFileSize): void
    {
        $this->maxFileSize = $maxFileSize;
    }

    public function logMessage($message, $messageType, $additionalData = [])
    {
        $logsDir = rtrim($this->getLogsDir(), DIRECTORY_SEPARATOR);
        $fileName = $this->getFileName();
        
        $maxFileSize = $this->getMaxFileSize();
        
        $fileDir = $logsDir . DIRECTORY_SEPARATOR . $fileName;
        
        if (!is_writable($logsDir)) {
            throw new \Exception(sprintf("Katalog %s musi posiadać uprawnienia do zapisu", $logsDir));
        }
        
        if (empty($fileName)) {
            throw new \Exception("Nazwa pliku nie może być pusta");
        }
        
        if (!empty($maxFileSize)) {
            // jeśli obecny plik jest zbyt duży należy go zarchwizować i utworzyć nowy
            $fileSize = filesize($fileDir);
            
            if ($fileSize > $maxFileSize) {
                if (!$this->moveFileToArchieve($fileName)) {
                    throw new \Exception(sprintf("Nie udało się zarchiwizować pliku %s", $fileName));
                }
            }
        }

        if (!file_put_contents($fileDir, $this->createLogRow($message, $messageType, $additionalData), FILE_APPEND)) {
            throw new \Exception(sprintf("Nie można zapisać pliku %s", $fileDir));
        }
    }
    
    protected function createLogRow($message, $messageType, $additionalData = [])
    {
        $remoteAddress = new \Laminas\Http\PhpEnvironment\RemoteAddress();
        $ipAddress = $remoteAddress->getIpAddress();
        
        $text  = date('Y-m-d H:i:s') . ' ';
        $text .= $ipAddress . ' ';
        
        switch ($messageType) {
            case \Base\Logger\Logger::MESSAGE_INFO:
                $text .= '[INFO]';
                break;
            case \Base\Logger\Logger::MESSAGE_SUCCESS:
                $text .= '[SUCCESS]';
                break;
            case \Base\Logger\Logger::MESSAGE_WARNING:
                $text .= '[WARNING]';
                break;
            case \Base\Logger\Logger::MESSAGE_ERROR:
                $text .= '[ERROR]';
                break;
        }
        
        $text .= ': ';
        $text .= $message;
        $text .= "\r\n";
        
        return $text;
    }
    
    protected function moveFileToArchieve($fileName)
    {
        return rename($fileName, $fileName . '1');
    }
}
