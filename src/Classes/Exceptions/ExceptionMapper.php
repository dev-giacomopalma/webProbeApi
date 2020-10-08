<?php

namespace App\Classes\Exceptions;

use Exception;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\CommunicationException\CannotReadResponse;
use HeadlessChromium\Exception\CommunicationException\InvalidResponse;
use HeadlessChromium\Exception\CommunicationException\ResponseHasError;
use HeadlessChromium\Exception\EvaluationFailed;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use twittingeek\webProbe\Probes\Exceptions\PageLoadException;

class ExceptionMapper
{

    private const GENERIC_ERROR_CODE = 'ERROR';
    private const FIELD_EVALUATION_EXCEPTION_ERROR_CODE = 'FIELD_EVALUATION_FAILED';
    private const UNSUPPORTED_EVALUATION_RULE_TYPE_ERROR_CODE = 'UNSUPPORTED_EVALUATION_RULE_TYPE';
    private const UNSUPPORTED_RESULT_TYPE_ERROR_CODE = 'UNSUPPORTED_RESULT_TYPE';
    private const ACCESS_DENIED_ERROR_CODE = 'ACCESS_DENIED';
    private const PAGE_LOAD_ERROR_CODE = 'PAGE_LOAD_ERROR';
    private const COMMUNICATION_ERROR_CODE = 'COMMUNICATION_ERROR';
    private const CANNOT_READ_RESPONSE ='CANNOT_READ_RESPONSE';
    private const INVALID_RESPONSE = 'INVALID_RESPONSE';
    private const RESPONSE_HAS_ERROR = 'RESPONSE_HAS_ERROR';
    private const EVALUATION_ERROR = 'EVALUATION_ERROR';
    private const NAVIGATION_EXPIRED = 'NAVIGATION_EXPIRED';
    private const NO_RESPONSE_ERROR = 'NO_RESPONSE_ERROR';
    private const OPERATION_TIMEOUT = 'OPERATION_TIMEOUT';


    public static function mapExceptionToErrorCode(Exception $exception): string
    {

        if ($exception instanceof FieldEvaluationException) {
            return self::FIELD_EVALUATION_EXCEPTION_ERROR_CODE;
        }

        if ($exception instanceof UnsupportedEvaluationRuleTypeException) {
            return self::UNSUPPORTED_EVALUATION_RULE_TYPE_ERROR_CODE;
        }

        if ($exception instanceof UnsupportedResultTypeException) {
            return self::UNSUPPORTED_RESULT_TYPE_ERROR_CODE;
        }

        if ($exception instanceof AccessDeniedException) {
            return self::ACCESS_DENIED_ERROR_CODE;
        }

        if ($exception instanceof PageLoadException) {
            return self::PAGE_LOAD_ERROR_CODE;
        }

        if ($exception instanceof CommunicationException) {
            return self::COMMUNICATION_ERROR_CODE;
        }

        if ($exception instanceof CannotReadResponse) {
            return self::CANNOT_READ_RESPONSE;
        }

        if ($exception instanceof InvalidResponse) {
            return self::INVALID_RESPONSE;
        }

        if ($exception instanceof ResponseHasError) {
            return self::RESPONSE_HAS_ERROR;
        }

        if ($exception instanceof EvaluationFailed) {
            return self::EVALUATION_ERROR;
        }

        if ($exception instanceof NavigationExpired) {
            return self::NAVIGATION_EXPIRED;
        }

        if ($exception instanceof NoResponseAvailable) {
            return self::NO_RESPONSE_ERROR;
        }

        if ($exception instanceof OperationTimedOut) {
            return self::OPERATION_TIMEOUT;
        }

        return self::GENERIC_ERROR_CODE;
    }

}
