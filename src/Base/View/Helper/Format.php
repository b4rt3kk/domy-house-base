<?php
namespace Base\View\Helper;

class Format extends \Laminas\View\Helper\AbstractHelper
{
    const FORMAT_DATE_TIME = 'date_time';
    const FORMAT_DATE = 'date';
    const FORMAT_TRUNCATE = 'truncate';
    const FORMAT_CURRENCY = 'currency';
    
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
            default:
                $return = $value;
        }
        
        return $return;
    }
}

