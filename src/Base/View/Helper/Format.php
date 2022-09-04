<?php
namespace Base\View\Helper;

class Format extends \Laminas\View\Helper\AbstractHelper
{
    const FORMAT_DATE_TIME = 'date_time';
    const FORMAT_DATE = 'date';
    const FORMAT_TRUNCATE = 'truncate';
    const FORMAT_TEXT_TRUNCATE = 'text_truncate';
    const FORMAT_CURRENCY = 'currency';
    const FORMAT_CURRENCY_WITH_SYMBOL = 'currency_with_symbol';
    const FORMAT_BYTE_FILE_SIZE = 'file_size';
    const FORMAT_TIME_LEFT = 'time_left';
    const FORMAT_DOWNLOAD_URL = 'download_url';
    const FORMAT_IP_GEOLOCATION = 'ip_geolocation';
    const FORMAT_IMAGE_MINIATURE = 'image_miniature';
    
    public function format($format, $value, $params = [])
    {
        $return = null;
        
        $config = \Base\Config\Config::getInstance();
        
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
                    $return = '<span class="d-inline-block text-truncate" data-bs-html="true" style="max-width: 150px;" data-bs-toggle="tooltip" title="' . htmlspecialchars($value) . '">' . $value . '</span>';
                }
                break;
            case self::FORMAT_TEXT_TRUNCATE:
                if (!empty($value)) {
                    if (mb_strlen($value, 'UTF-8') > 200) {
                        $return = mb_strcut($value, 0, 200, 'UTF-8') . '...';
                    } else {
                        $return = $value;
                    }
                    
                }
                break;
            case self::FORMAT_CURRENCY:
                if (!empty($value)) {
                    $return = number_format($value, 2, '.', ' ');
                }
                break;
            case self::FORMAT_CURRENCY_WITH_SYMBOL:
                if (!empty($value)) {
                    $return = number_format($value, 2, '.', ' ') . ' ' . $config->getVariable('currency_symbol');
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
            case self::FORMAT_DOWNLOAD_URL:
                $return = '<a href="' . $value . '" title="Pobierz"><i class="fas fa-download"></i></a> ' . $value;
                break;
            case self::FORMAT_IP_GEOLOCATION:
                if (!empty($value)) {
                    $return = $value . ' <a href="https://ipgeolocation.io/ip-location/' . $value . '" target="_blank"><i class="fas fa-external-link-alt"></i></a>';
                }
                break;
            case self::FORMAT_IMAGE_MINIATURE:
                if (!empty($value)) {
                    $return = '<img src="/home/image/' . $value . '" alt="No image" width="200" />';
                }
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

