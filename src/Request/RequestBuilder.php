<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Request;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function basename;
use function Safe\json_encode;
use function Safe\sprintf;
use function str_replace;
use function strpos;
use function sys_get_temp_dir;

final class RequestBuilder
{
    /** @var callable */
    private $findUser;
    /** @var callable */
    private $createToken;
    private string $method;
    private string $uri;
    /** @var mixed[] */
    private array $parameters = [];
    /** @var mixed[] */
    private array $files = [];
    /** @var mixed[] */
    private array $server = [];
    private ?string $content = null;
    private bool $changeHistory = true;

    private function __construct(
        callable $findUser,
        callable $createToken,
        string $method,
        string $uri
    ) {
        $this->findUser = $findUser;
        $this->createToken = $createToken;
        $this->method = $method;
        $this->uri = $uri;
    }

    public static function create(
        callable $findUser,
        callable $createToken,
        string $method,
        string $uri
    ): self {
        return new self($findUser, $createToken, $method, $uri);
    }

    public function uriAppend(string $suffix): self
    {
        $this->uri .= $suffix;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function uriParam(string $key, $value): self
    {
        $token = sprintf('{%s}', $key);
        if (strpos($this->uri, $token) === false) {
            throw InvalidRequest::invalidUriParam($token);
        }

        $this->uri = str_replace($token, (string) $value, $this->uri);

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function queryParam(string $key, $value): self
    {
        $this->uri .= sprintf('%s%s=%s', strpos($this->uri, '?') !== false ? '&' : '?', $key, $value);

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function parameter(string $key, $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function contentType(string $contentType): self
    {
        $this->server('CONTENT_TYPE', $contentType);

        return $this;
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param mixed[] $data
     */
    public function json(?array $data = null): self
    {
        $this->contentType('application/json');

        if ($data !== null) {
            $this->content(json_encode($data));
        }

        return $this;
    }

    public function xml(?string $xml = null): self
    {
        $this->contentType('text/xml');

        if ($xml !== null) {
            $this->content($xml);
        }

        return $this;
    }

    public function accept(string $accept): self
    {
        $this->server('HTTP_ACCEPT', $accept);

        return $this;
    }

    public function acceptAll(): self
    {
        $this->accept('*/*');

        return $this;
    }

    public function acceptImages(): self
    {
        $this->accept('image/*');

        return $this;
    }

    public function acceptJson(): self
    {
        $this->accept('application/json');

        return $this;
    }

    public function acceptHtml(): self
    {
        $this->accept('text/html');

        return $this;
    }

    public function acceptPdf(): self
    {
        $this->accept('application/pdf');

        return $this;
    }

    public function acceptXml(): self
    {
        $this->accept('text/xml');

        return $this;
    }

    /**
     * @param string[] $roles
     * @param string[] $additionalParams
     */
    public function authToken(string $username, array $roles = ['ROLE_USER'], array $additionalParams = []): self
    {
        $token = ($this->createToken)($username, $roles, $additionalParams);

        $this->server('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));

        return $this;
    }

    /**
     * @param mixed $userId
     */
    public function authLogin($userId, KernelBrowser $client): self
    {
        $user = ($this->findUser)($userId);

        $client->loginUser($user);

        return $this;
    }

    public function userAgent(string $userAgent): self
    {
        $this->server('HTTP_USER_AGENT', $userAgent);

        return $this;
    }

    public function server(string $key, string $value): self
    {
        $this->server[$key] = $value;

        return $this;
    }

    public function file(string $key, UploadedFile $file): self
    {
        $this->server('CONTENT_TYPE', 'multipart/form-data');

        $this->files[$key] = $file;

        return $this;
    }

    public function fileByPath(string $key, string $path): self
    {
        $filesystem = new Filesystem();
        $temporaryFilename = $filesystem->tempnam(sys_get_temp_dir(), 'upload_file_');
        $filesystem->copy($path, $temporaryFilename, true);

        $this->file($key, new UploadedFile($temporaryFilename, basename($path)));

        return $this;
    }

    public function fileByContent(string $key, string $content): self
    {
        $filesystem = new Filesystem();
        $temporaryFilename = $filesystem->tempnam(sys_get_temp_dir(), 'upload_file_');
        $filesystem->dumpFile($temporaryFilename, $content);

        $this->file($key, new UploadedFile($temporaryFilename, sprintf('%s.txt', $key)));

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return mixed[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return mixed[]
     */
    public function getServer(): array
    {
        return $this->server;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getChangeHistory(): bool
    {
        return $this->changeHistory;
    }
}
