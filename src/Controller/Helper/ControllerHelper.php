<?php

namespace App\Controller\Helper;

use App\Exceptions\ExceptionMapper;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ControllerHelper extends AbstractController
{
    public static function cleanResults($data)
    {
        if (is_string($data)) {
            if (mb_detect_encoding($data, 'UTF-8', true) === false) {
                $data = mb_convert_encoding($data, 'UTF-8', 'iso-8859-1');
            }
            return $data;
        }

        if (is_array($data)) {
            $ret = [];
            foreach ($data as $i => $d) {
                $ret[$i] = self::cleanResults($d);
            }

            return $ret;
        }

        if (is_object($data)) {
            foreach ($data as $i => $d) {
                $data->$i = self::cleanResults($d);
            }

            return $data;
        }

        return $data;
    }

    public static function returnError(Exception $exception): array
    {
        return [
            'errorCode' => ExceptionMapper::mapExceptionToErrorCode($exception),
            'errorMessage' => $exception->getMessage()
        ];
    }
}