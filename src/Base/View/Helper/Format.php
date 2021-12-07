<?php
namespace Base\View\Helper;

class Format extends \Laminas\View\Helper\AbstractHelper
{
    const FORMAT_DATE_TIME = 'date_time';
    const FORMAT_DATE = 'date';
    const FORMAT_TRUNCATE = 'truncate';
    const FORMAT_CURRENCY = 'currency';
    const FORMAT_BYTE_FILE_SIZE = 'file_size';
    const FORMAT_TIME_LEFT = 'time_left';
    
    public function format($format, $value, $params = [])
    {
        $return = null;
        
        switch ($format) {
            case self::FORMAT_DATE_TIME:
                if (!empty($value)) {
                    $return = date('Y-m-d H:i:s', strtotime($value));
                }
                break;
            case self::FORMAT_DATE:
                if (!empty($value)) {
                    $return = date('Y-m-d', strtotime($value));
                }
                break;
            case self::FORMAT_TRUNCATE:
                if (!empty($value)) {
                    $return = '<span class="d-inline-block text-truncate" data-bs-html="true" style="max-width: 150px;" data-bs-toggle="tooltip" title="' . $value . '">' . $value . '</span>';
                }
                break;
            case self::FORMAT_CURRENCY:
                if (!empty($value)) {
                    $return = number_format($value, 2, '.', ' ');
                }
                break;
            case self::FORMAT_BYTE_FILE_SIZE:
                // wartość wprowadzona do formatowania jest w bajtach
                if (!empty($value)) {
                    $return = $this->getBytesFormatted($value);
                }
                break;
            case self::FORMAT_TIME_LEFT:
                $return = $this->getTimeLeftFormatted($value);
                break;
            default:
                $return = $value;
        }
        
        return $return;
    }
    
    protected function getBytesFormatted($bytes)
    {
        $suffix = [
            'bytes' => ' B',
            'kbytes' => ' KB',
            'mbytes' => ' MB',
        ];
        
        $return = $bytes;
        $kbytes = $bytes / 1000;
        $mbytes = $kbytes / 1000;
        
        switch (true) {
            case $mbytes > 1:
                $return = number_format($mbytes, 2) . $suffix['mbytes'];
                break;
            case $kbytes > 1:
                $return = number_format($kbytes, 2) . $suffix['kbytes'];
                break;
            default:
                $return = $bytes . $suffix['bytes'];
        }
        
        return $return;
    }
    
    protected function getTimeLeftFormatted($seconds)
    {
        $hours = floor($seconds / (60 * 60));
        
        if ($hours > 0) {
            $seconds = $seconds - $hours * (60 * 60);
        }
        
        $minutes = floor($seconds / 60);
        
        if ($minutes > 0) {
            $seconds = $seconds - $minutes * 60;
        }
        
        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT);
    }
}

