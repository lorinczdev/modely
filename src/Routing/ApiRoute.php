<?php

namespace Lorinczdev\Modely\Routing;

class ApiRoute
{
    public string $partOfGroup;

    public string $contentType = 'json';

    public bool $reusableAction = false;

    protected ApiRouter $router;

    public function __construct(
        public string $method,
        public string $uri,
        public string $action,
        public ?string $model = null,
    ) {
    }

    public function partOf(string $name): self
    {
        $this->partOfGroup = $name;

        return $this;
    }

    public function asForm(): self
    {
        $this->contentType = 'form';

        return $this;
    }

    public function asMultipart(): self
    {
        $this->contentType = 'multipart';

        return $this;
    }

    public function asDownload(): self
    {
        $this->contentType = 'download';

        return $this;
    }

    public function asAction(): self
    {
        $this->reusableAction = true;

        return $this;
    }

    /**
     * Set the router instance on the route.
     */
    public function setRouter(ApiRouter $router): self
    {
        $this->router = $router;

        return $this;
    }
}
