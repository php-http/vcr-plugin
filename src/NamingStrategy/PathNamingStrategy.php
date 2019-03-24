<?php

declare(strict_types=1);

namespace Http\Client\Plugin\Vcr\NamingStrategy;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Will use the request path as filename.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class PathNamingStrategy implements NamingStrategyInterface
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function name(RequestInterface $request): string
    {
        $parts = [$this->options['name_prefix']];

        if ($this->options['hostname_prefix']) {
            $parts[] = $request->getUri()->getHost();
        }
        $method = strtoupper($request->getMethod());

        $parts[] = $method;
        $parts[] = str_replace(\DIRECTORY_SEPARATOR, '_', trim($request->getUri()->getPath(), '/'));

        if ($query = $request->getUri()->getQuery()) {
            $parts[] = $this->hash($query);
        }

        if ($this->options['use_headers']) {
            $headers = '';
            foreach ($request->getHeaders() as $header => $values) {
                $headers .= "$header:".implode(',', $values);
            }

            $parts[] = $this->hash($headers);
        }

        if (\in_array($method, $this->options['hash_body_methods'], true)) {
            $parts[] = $this->hash((string) $request->getBody());
        }

        return implode('_', array_filter($parts));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'hostname_prefix' => false,
            'name_prefix' => '',
            'use_headers' => false,
            'hash_body_methods' => ['PUT', 'POST', 'PATCH'],
        ]);

        $resolver->setAllowedTypes('hostname_prefix', 'bool');
        $resolver->setAllowedTypes('name_prefix', ['null', 'string']);
        $resolver->setAllowedTypes('use_headers', 'bool');
        $resolver->setAllowedTypes('hash_body_methods', 'string[]');

        $resolver->setNormalizer('hash_body_methods', function (Options $options, $value) {
            return \is_array($value) ? array_map('strtoupper', $value) : $value;
        });
    }

    private function hash(string $value): string
    {
        return substr(sha1($value), 0, 5);
    }
}
