<?php

declare(strict_types=1);

namespace Ugm\News\Exceptions;

use RuntimeException;

/**
 * Reports a WordPress integration failure without exposing upstream details.
 */
final class WordPressApiException extends RuntimeException
{
    /**
     * Creates a safe exception with an optional upstream HTTP status.
     */
    public function __construct(
        public readonly ?int $upstreamStatus = null,
        public readonly ?string $upstreamCode = null,
    ) {
        parent::__construct('No se pudo consultar la API de noticias.');
    }
}
