<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\NamingStrategy;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Will use the request attributes (hostname, path, headers & body) as filename.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class PathNamingStrategy implements NamingStrategyInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options available options:
     *                       - hash_headers:      the list of header names to hash,
     *                       - hash_body_methods: Methods for which the body will be hashed (Default: PUT, POST, PATCH)
     */
    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function name(RequestInterface $request): string
    {
        $parts = [$request->getUri()->getHost()];

        $method = strtoupper($request->getMethod());

        $parts[] = $method;
        $parts[] = str_replace('/', '_', trim($request->getUri()->getPath(), '/'));
        $parts[] = $this->getHeaderHash($request);

        if ($query = $request->getUri()->getQuery()) {
            $parts[] = $this->hash($query);
        }

        if (\in_array($method, $this->options['hash_body_methods'], true)) {
            $parts[] = $this->hash((string) $request->getBody());
        }

        return implode('_', array_filter($parts));
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'hash_headers' => [],
            'hash_body_methods' => ['PUT', 'POST', 'PATCH'],
        ]);

        $resolver->setAllowedTypes('hash_headers', 'string[]');
        $resolver->setAllowedTypes('hash_body_methods', 'string[]');

        $normalizer = function (Options $options, $value) {
            return \is_array($value) ? array_map('strtoupper', $value) : $value;
        };
        $resolver->setNormalizer('hash_headers', $normalizer);
        $resolver->setNormalizer('hash_body_methods', $normalizer);
    }

    private function hash(string $value): string
    {
        return substr(sha1($value), 0, 5);
    }

    private function getHeaderHash(RequestInterface $request): ?string
    {
        $headers = [];

        foreach ($this->options['hash_headers'] as $name) {
            if ($request->hasHeader($name)) {
                $headers[] = "$name:".implode(',', $request->getHeader($name));
            }
        }

        return empty($headers) ? null : $this->hash(implode(';', $headers));
    }
}
