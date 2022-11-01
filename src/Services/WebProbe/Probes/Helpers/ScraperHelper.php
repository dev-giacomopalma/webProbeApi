<?php

namespace App\Services\WebProbe\Probes\Helpers;

use HeadlessChromium\BrowserFactory;
use Exception;
use HeadlessChromium\Page;
use App\Services\WebProbe\Probes\Dtos\HTMLPageDto;
use App\Services\WebProbe\Probes\Exceptions\EvaluationException;
use App\Services\WebProbe\Probes\Exceptions\PageLoadException;
use App\Services\WebProbe\Probes\Exceptions\ScrapeElementNotFound;
use App\Services\WebProbe\Probes\Exceptions\UnrecognisedActionException;
use App\Services\WebProbe\Probes\Exceptions\UnrecognisedAttributeException;

class ScraperHelper
{

    private const HTML_ELEMENT_TYPE_SPAN = 'span';

    private const ID_ATTRIBUTE_NAME = 'id';
    private const NAME_ATTRIBUTE_NAME = 'name';

    private const VALID_ATTRIBUTE_NAMES = [
        self::ID_ATTRIBUTE_NAME,
        self::NAME_ATTRIBUTE_NAME
    ];

    /**
     * @param string $url
     * @param string $actions
     * @param string $browserIdentifier
     * @return HTMLPageDto
     * @throws PageLoadException
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\EvaluationFailed
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     */
    public static function loadPage(
        string $url,
        string $actions = null,
        string $browserIdentifier = 'chromium'
    ): HTMLPageDto {
        try {
            $browserFactory = new BrowserFactory($browserIdentifier);
            $browser = $browserFactory->createBrowser(
                [
                    'startupTimeout' => 120,
                    'sendSyncDefaultTimeout' => 60000,
                    'debug' => false,
                    'noSandbox' => true,
                    'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15',
                ]
            );
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation(Page::NETWORK_IDLE, 60000);
        } catch (Exception $exception) {
            throw new PageLoadException($exception->getMessage());
        }
        if (null !== $actions) {
            $actions = json_decode($actions);
            if (!empty($actions)) {
                foreach ($actions as $key => $action) {
                    try {
                        self::evaluatePage($page, $action);
                    } catch (Exception $exception) {
                        throw new PageLoadException(
                            sprintf(
                                'Elements evaluation was not possible. %s',
                                $exception->getMessage()
                            ),
                            $exception->getCode()
                        );
                    }
                }
            }
        }
        $head = $page->evaluate("document.head.innerHTML")->getReturnValue();
        $body = $page->evaluate("document.body.innerHTML")->getReturnValue();
        if (null !== $head && null !== $body) {
            return new HTMLPageDto($head, $body);
        }

        throw new PageLoadException(sprintf('Page not loaded. Url: %s', $url));

    }

    public static function readSpanByUniqueStart(string $page, string $start): string
    {
        $blocks = explode($start, $page);
        if (count($blocks) > 1) {
            $pointerBlock = $blocks[1];
            return self::readInsideHtmlTagLeft(
                self::readBeforeSpanClose($pointerBlock)
            );
        }

        return '';

    }

    private static function readBeforeSpanClose(string $block): string
    {
        return self::readBeforeElementClose($block, self::HTML_ELEMENT_TYPE_SPAN);
    }

    private static function readBeforeElementClose(string $block, string $element)
    {
        $blocks = explode("</{$element}>", $block);
        if (count($blocks) > 1) {
            return (string) $blocks[0];
        }

        return '';
    }

    private static function readInsideHtmlTagLeft(string $block)
    {
        $blocks = explode('>', $block);
        if (count($blocks) > 1) {
            return (string) $blocks[1];
        }

        return '';
    }

    protected static function removeCurrenciesSign(string $block, $removeHtml = true): string
    {
        $block = str_replace(array('€', '$'), '', $block);

        if ($removeHtml) {
            $block = str_replace(array('&euro;', '&dollar;'), '', $block);
        }

        return $block;
    }

    /**
     * @param string $delimiter
     * @param string $body
     * @param bool $fullList
     * @param bool $strict
     * @return array
     * @throws ScrapeElementNotFound
     */
    public static function readAfter(string $delimiter, string $body, bool $fullList = false, bool $strict = false): array
    {
        $blocks = explode($delimiter, $body);
        if (count($blocks) > 1) {
            if (false === $fullList) {
                return [$blocks[1]];
            }

            unset($blocks[0]);
            return array_values($blocks);
        }

        if ($strict) {
            throw new ScrapeElementNotFound(sprintf('%s : Delimiter not found %s', __FUNCTION__, $delimiter));
        }

        return [];
    }

    /**
     * @param string $delimiter
     * @param string $body
     * @param bool $fullList
     * @param bool $strict
     * @return array
     * @throws ScrapeElementNotFound
     */
    public static function readBefore(string $delimiter, string $body, bool $fullList = false, bool $strict = false): array
    {
        $blocks = explode($delimiter, $body);
        if (count($blocks) > 1) {
            if (false === $fullList) {
                return [$blocks[0]];
            }

            unset($blocks[count($blocks) -1]);
            return array_values($blocks);

        }

        if ($strict) {
            throw new ScrapeElementNotFound(sprintf('%s : Delimiter not found  %s',__FUNCTION__, $delimiter));
        }

        return [];
    }

    /**
     * Read string contained between left and right delimiter
     * if $strict == true, return an exception instead of an empty string
     * in case delimiters are not found in body
     *
     * @param string $leftDelimiter
     * @param string $rightDelimiter
     * @param string $body
     * @param bool $fullList
     * @param bool $strict
     * @return array
     * @throws ScrapeElementNotFound
     */
    public static function readBetween(
        string $leftDelimiter,
        string $rightDelimiter,
        string $body,
        bool $fullList = false,
        bool $strict = false
    ): array {
        $elements = self::readAfter($leftDelimiter, $body, $fullList, $strict);
        $vals = [];
        $insideVals = [];
        foreach ($elements as $element) {
            $insideVals[] = self::readBefore($rightDelimiter, $element, $fullList, $strict);
        }

        foreach ($insideVals as $insideVal) {
            if (isset($insideVal[0])) {
                $vals[] = $insideVal[0];
            }
        }

        return $vals;
    }

    /**
     * @param Page $page
     * @param $action
     * @throws UnrecognisedActionException
     * @throws UnrecognisedAttributeException
     * @throws EvaluationException
     */
    private static function evaluatePage(Page $page, $action): void
    {
        switch ($action->attribute) {
            case 'id':
                $identifier = '#'.$action->identifier;
                break;
            case 'name':
                $identifier = "[name='".$action->identifier."']";
                break;
            case 'domxquery':
                $identifier = $action->identifier;
                break;
            default:
                $identifier = "[".$action->attribute."='".$action->identifier."']";
                break;
        }
        $repeat = $action->repeat ?? 1;
        for ($i = 1; $i <= $repeat; $i++) {
            switch ($action->action) {
                case 'set':
                    $expression = sprintf(
                        '(() => {document.querySelector("%s").value = "%s";})()',
                        $identifier,
                        $action->value
                    );
                    break;
                case 'asyncClick':
                    $expression = sprintf(
                        '(() => {document.querySelector("%s").click();})()',
                        $identifier,
                    );
                    break;
                case 'click':
                    $expression = sprintf(
                        '(() => {document.querySelector("%s").click();})()',
                        $identifier,
                        );
                    break;
                case 'submit':
                    $expression = sprintf(
                        '(() => {document.querySelector("%s").submit();})()',
                        $identifier,
                        );
                    break;
                default:
                    throw new UnrecognisedActionException(
                        sprintf('Action not recognized: %s', $action->action)
                    );
            }
            try {
                $evaluate = $page->evaluate($expression);
            } catch (Exception $exception) {
                throw new EvaluationException(
                    $exception->getMessage(),
                    $exception->getCode()
                );
            }

            if ($action->action === 'click') {
                $evaluate->waitForPageReload(Page::NETWORK_IDLE);
            } else if ($action->action === 'asyncClick') {
                $evaluate->waitForResponse(30000);
            }
        }
    }
}