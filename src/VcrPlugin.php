<?php

namespace Http\Client\Plugin\Vcr;

use Http\Client\Plugin\Plugin;
use Http\Client\Plugin\Vcr\Exception\CannotBeReplayed;
use Http\Client\Plugin\Vcr\Exception\NotFound;
use Http\Promise\FulfilledPromise;
use Http\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class VcrPlugin implements Plugin
{
    const HEADER_VCR = 'X-VCR';
    const HEADER_VCR_REPLAY = 'replay';
    const HEADER_VCR_RECORDED = 'recorded';

    /**
     * @var Vcr
     */
    private $vcr;

    public function __construct(Vcr $vcr)
    {
        $this->vcr = $vcr;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        try {
            return $this->replay($request);
        } catch (CannotBeReplayed $e) {
            $this->record($request);
        }

        return $next($request)->then($this->onFulfilled($request), $this->onRejected($request));
    }

    private function replay(RequestInterface $request)
    {
        if (!$this->vcr->isTurnedOn() || !$this->vcr->hasTape()) {
            throw new CannotBeReplayed();
        }

        $tape = $this->vcr->getTape();

        try {
            $track = $tape->findTrackByRequest($request);
        } catch (NotFound $e) {
            throw new CannotBeReplayed();
        }

        if ($track->hasException()) {
            return new RejectedPromise($track->getException());
        }

        if ($track->hasResponse()) {
            $response = $track->getResponse()->withAddedHeader(self::HEADER_VCR, self::HEADER_VCR_REPLAY);

            return new FulfilledPromise($response);
        }

        throw new CannotBeReplayed();
    }

    private function record(RequestInterface $request)
    {
        if (!$this->vcr->isTurnedOn() || !$this->vcr->hasTape()) {
            return;
        }

        $tape = $this->vcr->getTape();
        $tape->addTrack(Track::create($request));
    }

    private function onFulfilled(RequestInterface $request)
    {
        $vcr = $this->vcr;

        return function (ResponseInterface $response) use ($vcr, $request) {
            if ($vcr->isTurnedOn() && $vcr->isRecording()) {
                $tape = $vcr->getTape();

                try {
                    $track = $tape->findTrackByRequest($request);
                } catch (NotFound $e) {
                    // The track should have been added already when the request was handled initially,
                    // but who knows what weird stuff you are doing :)
                    $track = Track::create($request);
                    $tape->addTrack($track);
                }

                $track->setResponse($response);

                $response = $response->withAddedHeader(self::HEADER_VCR, self::HEADER_VCR_RECORDED);
            }

            return $response;
        };
    }

    private function onRejected(RequestInterface $request)
    {
        $vcr = $this->vcr;

        return function (\Exception $e) use ($vcr, $request) {
            if ($vcr->isTurnedOn() && $vcr->isRecording()) {
                $tape = $vcr->getTape();

                try {
                    $track = $tape->findTrackByRequest($request);
                } catch (NotFound $notFound) {
                    // The track should have been added already when the request was handled initially,
                    // but who knows what weird stuff you are doing :)
                    $track = Track::create($request);
                    $tape->addTrack($track);
                }

                $track->setException($e);
            }

            return $e;
        };
    }
}
