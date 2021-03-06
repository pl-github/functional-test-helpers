<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

use IteratorAggregate;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function array_map;

final class MockRequestBuilderCollection implements IteratorAggregate
{
    private MockRequestBuilderFactory $requestFactory;
    private MockRequestResolver $requestResolver;
    private MockResponseFactory $responseFactory;
    /** @var MockRequestBuilder[] */
    private array $requestBuilders = [];

    public function __construct(MockResponseFactory $responseFactory)
    {
        $this->requestFactory = new MockRequestBuilderFactory();
        $this->requestResolver = new MockRequestResolver();
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param mixed[] $options
     */
    public function __invoke(string $method, string $url, array $options): ResponseInterface
    {
        $realRequest = ($this->requestFactory)($method, $url, $options);

        $requestBuilder = ($this->requestResolver)($this, $realRequest);
        $requestBuilder->called($realRequest);

        if ($requestBuilder->hasException()) {
            throw $requestBuilder->getException();
        }

        return $this->responseFactory->fromRequestBuilder($requestBuilder);
    }

    public function addMockRequestBuilder(MockRequestBuilder $mockRequestBuilder): void
    {
        $this->requestBuilders[] = $mockRequestBuilder;
    }

    public function getCallStack(): CallStack
    {
        $callStacks = array_map(
            static fn ($requestBuilder) => $requestBuilder->getCallStack(),
            $this->requestBuilders
        );

        return CallStack::fromCallStacks(...$callStacks);
    }

    /**
     * @return iterable|MockRequestBuilder[]
     */
    public function getIterator(): iterable
    {
        yield from $this->requestBuilders;
    }
}
