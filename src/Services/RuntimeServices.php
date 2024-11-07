<?php

declare(strict_types=1);

namespace SchemaTransformer\Services;

use SchemaTransformer\Interfaces\AbstractDataConverter;
use SchemaTransformer\Interfaces\AbstractDataReader;
use SchemaTransformer\Interfaces\AbstractDataWriter;
use SchemaTransformer\Services\Service;
use SchemaTransformer\Interfaces\AbstractService;
use SchemaTransformer\Transforms\DataSanitizers\SanitizeReachmeeJobPostingLink;
use SchemaTransformer\Transforms\ReachmeeJobPostingTransform;
use SchemaTransformer\Transforms\StratsysTransform;
use SchemaTransformer\Transforms\VismaJobPostingTransform;

class RuntimeServices
{
    private AbstractService $jobPostingService;
    private AbstractService $stratsysService;
    private AbstractService $vismaService;

    public function __construct(
        AbstractDataReader $reader,
        AbstractDataWriter $writer,
        AbstractDataConverter $converter
    ) {
        $reachmeeJobPostingSanitizers = [
            new SanitizeReachmeeJobPostingLink()
        ];

        $this->jobPostingService = new Service(
            $reader,
            $writer,
            new ReachmeeJobPostingTransform($reachmeeJobPostingSanitizers),
            $converter
        );
        $this->stratsysService   = new Service(
            $reader,
            $writer,
            new StratsysTransform(),
            $converter
        );
        $this->vismaService   = new Service(
            $reader,
            $writer,
            new VismaJobPostingTransform([], '16fb545c-0894-4ae8-82bd-c991d98caaf8'),
            $converter
        );
    }
    public function getJobPostingService(): AbstractService
    {
        return $this->jobPostingService;
    }
    public function getStratsysService(): AbstractService
    {
        return $this->stratsysService;
    }

    public function getVismaService(): AbstractService
    {
        return $this->vismaService;
    }
}
