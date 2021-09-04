<?php
namespace Base\View\Helper;

class Format extends \Laminas\View\Helper\AbstractHelper
{
    const FORMAT_DATE_TIME = 'date_time';
    const FORMAT_DATE = 'date';
    
    public function format($format, $value, $params = [])
    {
        $return = null;
        
        switch ($format) {
            case self::FORMAT_DATE_TIME:
                $return = date('Y-m-d H:i:s', strtotime($value));
                break;
            case self::FORMAT_DATE:
                $return = date('Y-m-d', strtotime($value));
                break;
            default:
                $return = $value;
        }
        
        return $return;
    }
}

